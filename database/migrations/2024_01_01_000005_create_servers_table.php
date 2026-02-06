<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('servers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('plan_id')->constrained()->onDelete('cascade');
            $table->foreignId('node_id')->constrained()->onDelete('cascade');
            
            $table->string('name');
            $table->string('uuid')->unique();
            $table->string('ip_address');
            $table->integer('port');
            $table->string('game_type')->default('java'); // java, bedrock
            $table->string('core_type')->default('vanilla'); // vanilla, forge, fabric, pocketmine, etc
            $table->string('core_version');
            $table->string('java_version')->nullable();
            
            $table->integer('memory')->default(1024); // MB
            $table->integer('cpu_limit')->default(100); // %
            $table->integer('disk_space')->default(10240); // MB
            
            $table->string('status')->default('stopped'); // stopped, starting, running, stopping, error
            $table->integer('pid')->nullable();
            $table->string('screen_name')->nullable();
            
            $table->string('location');
            $table->string('image_path')->default('/default-server.png');
            
            $table->timestamp('expires_at');
            $table->timestamp('suspended_at')->nullable();
            $table->timestamp('backup_date')->nullable();
            
            $table->json('settings')->nullable();
            $table->json('mods')->nullable();
            $table->json('plugins')->nullable();
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('servers');
    }
};
