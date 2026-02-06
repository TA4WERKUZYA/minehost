<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Core extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'game_type',
        'version',
        'file_name',
        'file_path',
        'file_size',
        'download_url',
        'description',
        'is_default',
        'is_active',
        'is_stable',
        'compatible_versions',
        'settings'
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'is_stable' => 'boolean',
        'compatible_versions' => 'array',
        'settings' => 'array'
    ];

    // Отношения
    public function servers()
    {
        return $this->hasMany(Server::class);
    }

    // Scope для активных ядер
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope для стабильных ядер
    public function scopeStable($query)
    {
        return $query->where('is_stable', true);
    }

    // Scope для Java ядер
    public function scopeJava($query)
    {
        return $query->where('game_type', 'java');
    }

    // Scope для Bedrock ядер
    public function scopeBedrock($query)
    {
        return $query->where('game_type', 'bedrock');
    }

    // Scope для определенного типа ядра
    public function scopeByName($query, $name)
    {
        return $query->where('name', $name);
    }

    // Получить человекочитаемый размер файла
    public function getFileSizeFormattedAttribute()
    {
        if ($this->file_size >= 1073741824) {
            return number_format($this->file_size / 1073741824, 2) . ' GB';
        } elseif ($this->file_size >= 1048576) {
            return number_format($this->file_size / 1048576, 2) . ' MB';
        } elseif ($this->file_size >= 1024) {
            return number_format($this->file_size / 1024, 2) . ' KB';
        } else {
            return $this->file_size . ' bytes';
        }
    }

    // Получить иконку для типа ядра
    public function getIconAttribute()
    {
        $icons = [
            'paper' => 'fas fa-file-alt',
            'spigot' => 'fas fa-bolt',
            'vanilla' => 'fas fa-gem',
            'fabric' => 'fas fa-puzzle-piece',
            'bedrock' => 'fas fa-mobile-alt'
        ];

        return $icons[$this->name] ?? 'fas fa-cube';
    }

    // Получить цвет для типа ядра
    public function getColorAttribute()
    {
        $colors = [
            'paper' => 'blue',
            'spigot' => 'yellow',
            'vanilla' => 'green',
            'fabric' => 'purple',
            'bedrock' => 'orange'
        ];

        return $colors[$this->name] ?? 'gray';
    }

    // Проверить, используется ли ядро
    public function isInUse()
    {
        return $this->servers()->count() > 0;
    }

    // Сделать ядро дефолтным для своего типа
    public function makeDefault()
    {
        // Сбрасываем дефолтный статус у всех ядер того же типа
        self::where('game_type', $this->game_type)
            ->where('id', '!=', $this->id)
            ->update(['is_default' => false]);
        
        // Устанавливаем дефолтный статус этому ядру
        $this->update(['is_default' => true]);
        
        return $this;
    }
}