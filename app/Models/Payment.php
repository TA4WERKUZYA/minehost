<?php
// app/Models/Payment.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'amount',
        'yookassa_id',
        'status',
        'description',
        'metadata',
        'payment_data',
        'paid_at',
        'captured_at',
        'confirmation_url',
        'error_message',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'metadata' => 'array',
        'payment_data' => 'array',
        'paid_at' => 'datetime',
        'captured_at' => 'datetime',
    ];

    // Отношения
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Методы статусов
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isSucceeded(): bool
    {
        return $this->status === 'succeeded';
    }

    public function isWaitingForCapture(): bool
    {
        return $this->status === 'waiting_for_capture';
    }

    public function isCanceled(): bool
    {
        return $this->status === 'canceled';
    }

    // Обновление баланса пользователя
    public function applyToBalance(): void
    {
        if ($this->isSucceeded() && !$this->metadata['balance_applied'] ?? false) {
            $this->user->increment('balance', $this->amount);
            
            $metadata = $this->metadata ?? [];
            $metadata['balance_applied'] = true;
            $metadata['balance_applied_at'] = now()->toDateTimeString();
            
            $this->update(['metadata' => $metadata]);
            
            // Создаем транзакцию
            Transaction::create([
                'user_id' => $this->user_id,
                'type' => 'deposit',
                'amount' => $this->amount,
                'description' => 'Пополнение баланса через ЮKassa',
                'payment_id' => $this->id,
                'balance_before' => $this->user->balance - $this->amount,
                'balance_after' => $this->user->balance,
            ]);
        }
    }
}
