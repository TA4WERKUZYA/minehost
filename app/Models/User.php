<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'balance',
        'is_banned',
        'ban_reason',          // Добавляем
        'banned_at',
        'banned_until',
        'discord_id',          // Добавляем
        'telegram_id',         // Добавляем
        'country',             // Добавляем
        'timezone',            // Добавляем
        'last_login_at',       // Добавляем
        'email_verified_at',
        'is_admin',
        'settings',
];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_banned' => 'boolean',
        'banned_at' => 'datetime',
        'banned_until' => 'datetime',
        'last_login_at' => 'datetime',
        'balance' => 'decimal:2',
    ];

    // Отношения - должны быть УНИКАЛЬНЫ (без дублирования)

    // Серверы пользователя
    public function servers()
    {
        return $this->hasMany(Server::class);
    }

    // Заказы пользователя
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    // Транзакции пользователя (если модель Transaction существует)
    public function transactions()
    {
        if (class_exists(Transaction::class)) {
            return $this->hasMany(Transaction::class)->orderBy('created_at', 'desc');
        }
        return null;
    }

    // Методы для проверки прав
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isModerator()
    {
        return $this->role === 'moderator';
    }

    public function isBanned()
    {
        return $this->is_banned || ($this->banned_until && $this->banned_until->isFuture());
    }

    public function hasVerifiedEmail()
    {
        return !is_null($this->email_verified_at);
    }
}