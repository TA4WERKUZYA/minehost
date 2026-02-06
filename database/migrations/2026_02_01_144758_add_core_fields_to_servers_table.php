<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('servers', function (Blueprint $table) {
            // Поля для ядра
            $table->string('core_type')->nullable()->after('game_type');
            $table->string('core_version')->nullable()->after('core_type');
            $table->foreignId('core_id')->nullable()->after('core_version')->constrained('cores')->nullOnDelete();
            
            // Плеер слоты
            if (!Schema::hasColumn('servers', 'player_slots')) {
                $table->integer('player_slots')->default(20)->after('disk_space');
            }
            
            // Даты событий
            $table->timestamp('installed_at')->nullable()->after('expires_at');
            $table->timestamp('started_at')->nullable()->after('installed_at');
            $table->timestamp('stopped_at')->nullable()->after('started_at');
            $table->timestamp('last_backup')->nullable()->after('stopped_at');
            
            // Индекс для быстрого поиска
            $table->index(['core_id', 'status']);
        });
    }

    public function down()
    {
        Schema::table('servers', function (Blueprint $table) {
            $table->dropForeign(['core_id']);
            $table->dropColumn([
                'core_type',
                'core_version',
                'core_id',
                'player_slots',
                'installed_at',
                'started_at',
                'stopped_at',
                'last_backup'
            ]);
        });
    }
};
