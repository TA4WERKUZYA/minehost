<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('servers', function (Blueprint $table) {
            // Удаляем ненужные колонки
            $table->dropColumn([
                'core_type',
                'core_version',
                'java_version',
                'cpu_limit',
                'pid',
                'screen_name',
                'image_path',
                'suspended_at',
                'backup_date',
                'mods',
                'plugins'
            ]);
            
            // Добавляем нужные колонки
            $table->string('game_type')->default('java')->change();
            $table->integer('player_slots')->default(20)->after('disk_space');
        });
    }

    public function down()
    {
        Schema::table('servers', function (Blueprint $table) {
            // Вернуть колонки при откате
            $table->string('core_type')->default('vanilla');
            $table->string('core_version')->nullable();
            $table->string('java_version')->nullable();
            $table->integer('cpu_limit')->default(100);
            $table->integer('pid')->nullable();
            $table->string('screen_name')->nullable();
            $table->string('image_path')->default('/default-server.png');
            $table->timestamp('suspended_at')->nullable();
            $table->timestamp('backup_date')->nullable();
            $table->longText('mods')->nullable();
            $table->longText('plugins')->nullable();
            
            $table->dropColumn('player_slots');
        });
    }
};