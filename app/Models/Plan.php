<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'game_type',
        'core_type',
        'price_monthly',
        'price_quarterly',
        'price_half_year',
        'price_yearly',
        'memory',
        'cpu_cores',
        'disk_space',
        'backup_slots',
        'player_slots',
        'ftp_access',
        'database_access',
        'mod_support',
        'plugin_support',
        'is_active'
    ];

    protected $casts = [
        'price_monthly' => 'decimal:2',
        'price_quarterly' => 'decimal:2',
        'price_half_year' => 'decimal:2',
        'price_yearly' => 'decimal:2',
        'ftp_access' => 'boolean',
        'database_access' => 'boolean',
        'mod_support' => 'boolean',
        'plugin_support' => 'boolean',
        'is_active' => 'boolean'
    ];

    public function servers()
    {
        return $this->hasMany(Server::class);
    }

    public function getPriceForPeriod($period)
    {
        return match($period) {
            '30' => $this->price_monthly,
            '60' => $this->price_quarterly,
            '90' => $this->price_half_year,
            '180' => $this->price_yearly,
            default => $this->price_monthly
        };
    }
}
