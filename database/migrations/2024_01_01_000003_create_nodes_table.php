<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('nodes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('ip_address');
            $table->string('hostname');
            $table->string('location');
            
            $table->integer('total_memory'); // MB
            $table->integer('used_memory')->default(0);
            $table->integer('total_disk'); // MB
            $table->integer('used_disk')->default(0);
            $table->integer('total_cpu'); // cores
            $table->integer('used_cpu')->default(0);
            
            $table->string('daemon_token');
            $table->integer('daemon_port')->default(8080);
            $table->string('backup_path')->default('/backups');
            $table->string('servers_path')->default('/servers');
            $table->string('cores_path')->default('/cores');
            
            $table->boolean('is_active')->default(true);
            $table->boolean('accept_new_servers')->default(true);
            
            $table->json('settings')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('nodes');
    }
};
