<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Node extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'ip_address',
        'hostname',
        'location',
        'total_memory',
        'used_memory',
        'total_disk',
        'used_disk',
        'total_cpu',
        'used_cpu',
        'daemon_token',
        'daemon_port',
        'backup_path',
        'servers_path',
        'cores_path',
        'is_active',
        'accept_new_servers',
        'settings'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'accept_new_servers' => 'boolean',
        'settings' => 'array'
    ];

    public function servers()
    {
        return $this->hasMany(Server::class);
    }

    public function getAvailableMemoryAttribute()
    {
        return $this->total_memory - $this->used_memory;
    }

    public function getAvailableDiskAttribute()
    {
        return $this->total_disk - $this->used_disk;
    }

    public function getAvailableCpuAttribute()
    {
        return $this->total_cpu - $this->used_cpu;
    }

    public function isAvailable()
    {
        return $this->is_active && $this->accept_new_servers;
    }
}
