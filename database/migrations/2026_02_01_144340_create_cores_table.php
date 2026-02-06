<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('cores', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Paper, Spigot, Vanilla, Fabric
            $table->string('game_type'); // java, bedrock
            $table->string('version'); // 1.20.4, 1.19.4 и т.д.
            $table->string('file_name'); // paper-1.20.4.jar
            $table->string('file_path'); // /opt/minecraft-cores/java/paper/1.20.4/paper-1.20.4.jar
            $table->integer('file_size')->nullable();
            $table->string('download_url')->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable();
            $table->timestamps();
            
            $table->unique(['name', 'version', 'game_type']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('cores');
    }
};
