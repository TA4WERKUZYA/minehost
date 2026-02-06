<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Backup extends Model
{
    use HasFactory;

    protected $fillable = [
        'server_id',
        'name',
        'file_path',
        'size',
        'is_automatic',
        'status',
        'completed_at'
    ];

    protected $casts = [
        'is_automatic' => 'boolean',
        'completed_at' => 'datetime'
    ];

    public function server()
    {
        return $this->belongsTo(Server::class);
    }

    public function getSizeFormattedAttribute()
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = $this->size;
        $unit = floor(log($bytes, 1024));
        return round($bytes / pow(1024, $unit), 2) . ' ' . $units[$unit];
    }
}
