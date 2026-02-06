// App\Http\Controllers\PaymentController.php
use YooKassa\Client;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Transaction;

class PaymentController extends Controller
{
    private function getClient(): Client
    {
        $client = new Client();
        // Ключи лучше хранить в .env файле как YOOKASSA_SHOP_ID и YOOKASSA_SECRET_KEY
        $client->setAuth(config('services.yookassa.shop_id'), config('services.yookassa.secret_key'));
        return $client;
    }

    public function create(Request $request)
    {
        $request->validate(['amount' => 'required|numeric|min:10']); // Минимум 10 руб.

        $user = auth()->user();
        $client = $this->getClient();

        // Создаем платеж в ЮKassa[citation:4]
        $payment = $client->createPayment([
            'amount' => [
                'value' => number_format($request->amount, 2, '.', ''),
                'currency' => 'RUB',
            ],
            'confirmation' => [
                'type' => 'redirect',
                'return_url' => route('payment.success'), // Страница успеха на вашем сайте
            ],
            'description' => 'Пополнение баланса на сайте Minecraft Hosting',
            'metadata' => [
                'user_id' => $user->id,
                'type' => 'balance_refill'
            ],
            // Фискальный чек (обязателен)[citation:5]
            'receipt' => [
                'customer' => [
                    'email' => $user->email,
                ],
                'items' => [
                    [
                        'description' => 'Пополнение игрового баланса',
                        'amount' => [
                            'value' => number_format($request->amount, 2, '.', ''),
                            'currency' => 'RUB'
                        ],
                        'vat_code' => 1, // Ставка НДС (1 = 20%, но уточните у бухгалтера)
                        'quantity' => '1',
                        'payment_mode' => 'full_payment',
                        'payment_subject' => 'service'
                    ]
                ]
            ]
        ], uniqid('', true)); // Уникальный idempotence key

        // Сохраняем транзакцию в БД в статусе 'pending'
        $transaction = Transaction::create([
            'user_id' => $user->id,
            'payment_id' => $payment->getId(),
            'amount' => $request->amount,
            'description' => $payment->getDescription(),
            'status' => 'pending',
            'metadata' => ['yookassa_response' => $payment->jsonSerialize()]
        ]);

        // Перенаправляем пользователя на страницу оплаты ЮKassa
        return redirect()->away($payment->getConfirmation()->getConfirmationUrl());
    }
}
