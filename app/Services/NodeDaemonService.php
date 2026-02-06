<?php

namespace App\Services;

use App\Models\Node;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NodeDaemonService
{
    public function sendCommand(Node $node, string $serverUuid, string $command)
    {
        $url = "http://{$node->ip_address}:{$node->daemon_port}/api/server/{$serverUuid}/command";
        
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $node->daemon_token,
            'Content-Type' => 'application/json'
        ])->post($url, [
            'command' => $command
        ]);

        if ($response->failed()) {
            throw new \Exception('Failed to send command: ' . $response->body());
        }

        return $response->json();
    }

    public function getConsoleOutput(Node $node, string $serverUuid, int $lines = 100)
    {
        $url = "http://{$node->ip_address}:{$node->daemon_port}/api/server/{$serverUuid}/console?lines={$lines}";
        
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $node->daemon_token
        ])->get($url);

        if ($response->failed()) {
            throw new \Exception('Failed to get console output: ' . $response->body());
        }

        return $response->json()['output'] ?? '';
    }

    public function startServer(Node $node, string $serverUuid)
    {
        $url = "http://{$node->ip_address}:{$node->daemon_port}/api/server/{$serverUuid}/start";
        
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $node->daemon_token
        ])->post($url);

        if ($response->failed()) {
            throw new \Exception('Failed to start server: ' . $response->body());
        }

        return $response->json();
    }

    public function stopServer(Node $node, string $serverUuid)
    {
        $url = "http://{$node->ip_address}:{$node->daemon_port}/api/server/{$serverUuid}/stop";
        
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $node->daemon_token
        ])->post($url);

        if ($response->failed()) {
            throw new \Exception('Failed to stop server: ' . $response->body());
        }

        return $response->json();
    }

    public function restartServer(Node $node, string $serverUuid)
    {
        $url = "http://{$node->ip_address}:{$node->daemon_port}/api/server/{$serverUuid}/restart";
        
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $node->daemon_token
        ])->post($url);

        if ($response->failed()) {
            throw new \Exception('Failed to restart server: ' . $response->body());
        }

        return $response->json();
    }

    public function getAvailableCores(Node $node)
    {
        $url = "http://{$node->ip_address}:{$node->daemon_port}/api/cores";
        
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $node->daemon_token
        ])->get($url);

        if ($response->failed()) {
            Log::error('Failed to get cores: ' . $response->body());
            return [];
        }

        return $response->json()['cores'] ?? [];
    }

    public function getOnlinePlayers(Node $node, string $serverUuid)
    {
        $url = "http://{$node->ip_address}:{$node->daemon_port}/api/server/{$serverUuid}/players";
        
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $node->daemon_token
        ])->get($url);

        if ($response->failed()) {
            throw new \Exception('Failed to get players: ' . $response->body());
        }

        return $response->json()['players'] ?? [];
    }

    public function createBackup(Node $node, string $serverUuid, string $backupName)
    {
        $url = "http://{$node->ip_address}:{$node->daemon_port}/api/server/{$serverUuid}/backup";
        
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $node->daemon_token,
            'Content-Type' => 'application/json'
        ])->post($url, [
            'name' => $backupName
        ]);

        if ($response->failed()) {
            throw new \Exception('Failed to create backup: ' . $response->body());
        }

        return $response->json();
    }

    public function restoreBackup(Node $node, string $serverUuid, string $backupPath)
    {
        $url = "http://{$node->ip_address}:{$node->daemon_port}/api/server/{$serverUuid}/restore";
        
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $node->daemon_token,
            'Content-Type' => 'application/json'
        ])->post($url, [
            'backup_path' => $backupPath
        ]);

        if ($response->failed()) {
            throw new \Exception('Failed to restore backup: ' . $response->body());
        }

        return $response->json();
    }

    public function updateServerConfig(Node $node, string $serverUuid, array $config)
    {
        $url = "http://{$node->ip_address}:{$node->daemon_port}/api/server/{$serverUuid}/config";
        
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $node->daemon_token,
            'Content-Type' => 'application/json'
        ])->put($url, $config);

        if ($response->failed()) {
            throw new \Exception('Failed to update config: ' . $response->body());
        }

        return $response->json();
    }
}
