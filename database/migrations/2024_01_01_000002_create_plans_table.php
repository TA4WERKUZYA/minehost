<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('game_type'); // java, bedrock
            $table->string('core_type'); // vanilla, forge, fabric, pocketmine
            
            $table->decimal('price_monthly', 10, 2);
            $table->decimal('price_quarterly', 10, 2);
            $table->decimal('price_half_year', 10, 2);
            $table->decimal('price_yearly', 10, 2);
            
            $table->integer('memory'); // MB
            $table->integer('cpu_cores');
            $table->integer('disk_space'); // MB
            $table->integer('backup_slots');
            $table->integer('player_slots');
            
            $table->boolean('ftp_access')->default(true);
            $table->boolean('database_access')->default(true);
            $table->boolean('mod_support')->default(false);
            $table->boolean('plugin_support')->default(false);
            
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('plans');
    }
};
