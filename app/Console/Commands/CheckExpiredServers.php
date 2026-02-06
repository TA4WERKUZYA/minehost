<?php

namespace App\Console\Commands;

use App\Models\Server;
use App\Services\NodeDaemonService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckExpiredServers extends Command
{
    protected $signature = 'servers:check-expired';
    protected $description = 'Check for expired servers and suspend/archive them';
    
    protected $daemonService;
    
    public function __construct(NodeDaemonService $daemonService)
    {
        parent::__construct();
        $this->daemonService = $daemonService;
    }
    
    public function handle()
    {
        $now = now();
        
        // Servers that expired less than 5 days ago - suspend them
        $recentlyExpired = Server::where('expires_at', '<', $now)
            ->whereNull('suspended_at')
            ->where('expires_at', '>', $now->subDays(5))
            ->get();
        
        foreach ($recentlyExpired as $server) {
            $this->info("Suspending server #{$server->id}");
            
            // Stop server
            try {
                $this->daemonService->stopServer($server->node, $server->uuid);
            } catch (\Exception $e) {
                Log::error("Failed to stop expired server #{$server->id}: " . $e->getMessage());
            }
            
            // Mark as suspended
            $server->update([
                'suspended_at' => now(),
                'status' => 'stopped'
            ]);
        }
        
        // Servers that expired more than 5 days ago - archive them
        $toArchive = Server::where('expires_at', '<', $now->subDays(5))
            ->whereNotNull('suspended_at')
            ->whereNull('backup_date')
            ->get();
        
        foreach ($toArchive as $server) {
            $this->info("Archiving server #{$server->id}");
            
            // Create final backup
            try {
                $backup = $this->daemonService->createBackup(
                    $server->node,
                    $server->uuid,
                    "archive_" . date('Y-m-d')
                );
                
                // Mark as archived
                $server->update([
                    'backup_date' => now()
                ]);
                
                Log::info("Server #{$server->id} archived successfully");
                
            } catch (\Exception $e) {
                Log::error("Failed to archive server #{$server->id}: " . $e->getMessage());
            }
        }
        
        // Servers archived more than 2 days ago - delete them
        $toDelete = Server::where('backup_date', '<', $now->subDays(2))->get();
        
        foreach ($toDelete as $server) {
            $this->info("Deleting archived server #{$server->id}");
            
            // Delete server files
            // This would call daemon to delete server directory
            
            $server->delete();
        }
        
        $this->info('Expired servers check completed');
    }
}
