<?php
// app/Http/Controllers/BalanceController.php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use YooKassa\Client;
use YooKassa\Common\Exceptions\ApiException;

class BalanceController extends Controller
{
    private Client $yookassaClient;

    public function __construct()
    {
        $this->yookassaClient = new Client();
        $this->yookassaClient->setAuth(
            config('services.yookassa.shop_id'),
            config('services.yookassa.secret_key')
        );
    }

    /**
     * Страница пополнения баланса
     */
    public function index()
    {
        $user = auth()->user();
        
        // Получаем последние 5 платежей
        $recentPayments = Payment::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('balance.index', [
            'user' => $user,
            'payments' => $recentPayments,
            'title' => 'Пополнение баланса'
        ]);
    }

    /**
     * Создание платежа
     */
    public function create(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:10|max:50000',
        ]);

        $user = auth()->user();
        $amount = $request->amount;

        DB::beginTransaction();
        try {
            // Создаем платеж в ЮKassa
            $payment = $this->yookassaClient->createPayment([
                'amount' => [
                    'value' => number_format($amount, 2, '.', ''),
                    'currency' => 'RUB',
                ],
                'confirmation' => [
                    'type' => 'redirect',
                    'return_url' => route('balance.success'),
                ],
                'capture' => true,
                'description' => 'Пополнение баланса AllyHost',
                'metadata' => [
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                    'type' => 'balance_refill',
                ],
                'receipt' => [
                    'customer' => [
                        'email' => $user->email,
                    ],
                    'items' => [
                        [
                            'description' => 'Пополнение игрового баланса',
                            'amount' => [
                                'value' => number_format($amount, 2, '.', ''),
                                'currency' => 'RUB',
                            ],
                            'vat_code' => 1,
                            'quantity' => '1.00',
                            'payment_mode' => 'full_payment',
                            'payment_subject' => 'service',
                        ],
                    ],
                ],
            ], uniqid('', true));

            // Сохраняем платеж в БД
            $dbPayment = Payment::create([
                'user_id' => $user->id,
                'amount' => $amount,
                'yookassa_id' => $payment->getId(),
                'status' => $payment->getStatus(),
                'description' => $payment->getDescription(),
                'metadata' => [
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                    'type' => 'balance_refill',
                    'balance_applied' => false,
                ],
                'payment_data' => $payment->jsonSerialize(),
                'confirmation_url' => $payment->getConfirmation()->getConfirmationUrl(),
            ]);

            DB::commit();

            Log::info('Balance payment created', [
                'user_id' => $user->id,
                'payment_id' => $dbPayment->id,
                'yookassa_id' => $payment->getId(),
                'amount' => $amount,
            ]);

            // Перенаправляем на страницу оплаты ЮKassa
            return redirect()->away($payment->getConfirmation()->getConfirmationUrl());

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Balance payment creation failed', [
                'user_id' => $user->id,
                'amount' => $amount,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('balance.index')
                ->with('error', 'Ошибка создания платежа: ' . $e->getMessage());
        }
    }

    /**
     * Успешная оплата
     */
    public function success(Request $request)
{
    $user = auth()->user();
    $payment = Payment::where('user_id', $user->id)
        ->whereIn('status', ['pending', 'waiting_for_capture'])
        ->latest()
        ->first();

    if (!$payment) {
        return redirect()->route('balance.index')->with('error', 'Платеж не найден');
    }

    try {
        $yookassaPayment = $this->yookassaClient->getPaymentInfo($payment->yookassa_id);
        $paymentData = $yookassaPayment->jsonSerialize();
        
        // ВАЖНО: получаем дату оплаты из массива данных
        $paidAt = null;
        if (isset($paymentData['paid_at'])) {
            $paidAt = date('Y-m-d H:i:s', strtotime($paymentData['paid_at']));
        } elseif (isset($paymentData['captured_at'])) {
            $paidAt = date('Y-m-d H:i:s', strtotime($paymentData['captured_at']));
        }
        
        $payment->update([
            'status' => $paymentData['status'],
            'payment_data' => $paymentData,
            'paid_at' => $paidAt,
            'captured_at' => $paidAt,
        ]);

        // Если платеж успешен, начисляем баланс
        if ($paymentData['status'] === 'succeeded') {
            $this->applyToBalance($payment);
            return view('balance.success', [
                'payment' => $payment,
                'title' => 'Платеж успешен'
            ]);
        } else {
            return view('balance.success', [
                'payment' => $payment,
                'title' => 'Статус платежа: ' . $paymentData['status']
            ]);
        }

    } catch (\Exception $e) {
        Log::error('Ошибка проверки статуса платежа', [
            'error' => $e->getMessage(),
            'payment_id' => $payment->yookassa_id
        ]);
        
        return view('balance.success', [
            'payment' => $payment,
            'title' => 'Ошибка проверки платежа'
        ])->with('error', 'Не удалось проверить статус платежа. Пожалуйста, проверьте баланс позже.');
    }
}

    /**
     * Отмена оплаты
     */
    public function cancel(Request $request)
    {
        return view('balance.cancel', [
            'title' => 'Платеж отменен'
        ]);
    }

    /**
     * История платежей
     */
    public function history(Request $request)
    {
        $user = auth()->user();
        $payments = Payment::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('balance.history', [
            'payments' => $payments,
            'title' => 'История платежей'
        ]);
    }

    /**
     * Вебхук для ЮKassa
     */
    public function webhook(Request $request)
{
    try {
        // Получаем тело запроса
        $requestBody = $request->getContent();
        $requestData = json_decode($requestBody, true);
        
        if (!isset($requestData['object']['id'])) {
            return response()->json(['error' => 'Invalid webhook data'], 400);
        }
        
        $yookassaId = $requestData['object']['id'];
        $event = $requestData['event'];
        
        // Находим платеж в базе
        $payment = Payment::where('yookassa_id', $yookassaId)->first();
        
        if (!$payment) {
            Log::warning('Payment not found for webhook', ['yookassa_id' => $yookassaId]);
            return response()->json(['error' => 'Payment not found'], 404);
        }
        
        // Получаем актуальную информацию о платеже
        $yookassaPayment = $this->yookassaClient->getPaymentInfo($yookassaId);
        $paymentData = $yookassaPayment->jsonSerialize();
        
        // Обновляем статус платежа
        $paidAt = null;
        if (isset($paymentData['paid_at'])) {
            $paidAt = date('Y-m-d H:i:s', strtotime($paymentData['paid_at']));
        } elseif (isset($paymentData['captured_at'])) {
            $paidAt = date('Y-m-d H:i:s', strtotime($paymentData['captured_at']));
        }
        
        $payment->update([
            'status' => $paymentData['status'],
            'payment_data' => $paymentData,
            'paid_at' => $paidAt,
            'captured_at' => $paidAt,
        ]);
        
        // Если платеж успешен, начисляем баланс
        if ($paymentData['status'] === 'succeeded') {
            $this->applyToBalance($payment);
        }
        
        Log::info('Webhook processed successfully', [
            'yookassa_id' => $yookassaId,
            'event' => $event,
            'status' => $paymentData['status']
        ]);
        
        return response()->json(['success' => true]);
        
    } catch (\Exception $e) {
        Log::error('Webhook processing error', [
            'error' => $e->getMessage(),
            'request_data' => $request->all()
        ]);
        
        return response()->json(['error' => 'Internal server error'], 500);
    }
}

    /**
     * Применение платежа к балансу
     */
    private function applyToBalance(Payment $payment): void
    {
        if ($payment->status === 'succeeded' && !($payment->metadata['balance_applied'] ?? false)) {
            $user = $payment->user;
            
            // Обновляем баланс
            $oldBalance = $user->balance;
            $user->balance += $payment->amount;
            $user->save();
            
            // Обновляем метаданные платежа
            $metadata = $payment->metadata ?? [];
            $metadata['balance_applied'] = true;
            $metadata['balance_applied_at'] = now()->toDateTimeString();
            $metadata['old_balance'] = $oldBalance;
            $metadata['new_balance'] = $user->balance;
            
            $payment->update(['metadata' => $metadata]);
            
            // Создаем транзакцию
            Transaction::create([
                'user_id' => $user->id,
                'type' => 'deposit',
                'amount' => $payment->amount,
                'description' => 'Пополнение баланса через ЮKassa',
                'payment_id' => $payment->id,
                'balance_before' => $oldBalance,
                'balance_after' => $user->balance,
            ]);
        }
    }
}
