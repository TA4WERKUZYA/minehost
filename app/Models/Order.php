<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'plan_id',
        'uuid',
        'amount',
        'period',
        'status',
        'payment_method',
        'payment_id',
        'paid_at',
        'server_config'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'server_config' => 'array'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function isPaid()
    {
        return $this->status === 'paid' && !is_null($this->paid_at);
    }

    public function markAsPaid($paymentMethod, $paymentId)
    {
        $this->update([
            'status' => 'paid',
            'payment_method' => $paymentMethod,
            'payment_id' => $paymentId,
            'paid_at' => now()
        ]);
    }
}
