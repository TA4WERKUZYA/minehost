<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Причина блокировки
            $table->text('ban_reason')->nullable()->after('is_banned');
            
            // Discord ID
            $table->string('discord_id', 100)->nullable()->after('ban_reason');
            
            // Telegram ID
            $table->string('telegram_id', 100)->nullable()->after('discord_id');
            
            // Страна
            $table->string('country', 100)->nullable()->after('telegram_id');
            
            // Часовой пояс
            $table->string('timezone', 50)->nullable()->after('country');
            
            // Последний вход
            $table->timestamp('last_login_at')->nullable()->after('timezone');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'ban_reason',
                'discord_id',
                'telegram_id',
                'country',
                'timezone',
                'last_login_at'
            ]);
        });
    }
};