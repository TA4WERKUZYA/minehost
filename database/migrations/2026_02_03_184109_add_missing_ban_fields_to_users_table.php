<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Проверяем существование каждого столбца перед добавлением
            if (!Schema::hasColumn('users', 'banned_by')) {
                $table->foreignId('banned_by')->nullable()->after('banned_until')
                      ->constrained('users')->nullOnDelete();
            }
            
            if (!Schema::hasColumn('users', 'ban_reason')) {
                $table->text('ban_reason')->nullable()->after('is_banned');
            }
            
            if (!Schema::hasColumn('users', 'banned_at')) {
                $table->timestamp('banned_at')->nullable()->after('ban_reason');
            }
            
            if (!Schema::hasColumn('users', 'banned_until')) {
                $table->timestamp('banned_until')->nullable()->after('banned_at');
            }
            
            if (!Schema::hasColumn('users', 'is_banned')) {
                $table->boolean('is_banned')->default(false)->after('role');
            }
            
            if (!Schema::hasColumn('users', 'last_login_at')) {
                $table->timestamp('last_login_at')->nullable()->after('remember_token');
            }
            
            if (!Schema::hasColumn('users', 'last_login_ip')) {
                $table->string('last_login_ip', 45)->nullable()->after('last_login_at');
            }
            
            if (!Schema::hasColumn('users', 'notes')) {
                $table->text('notes')->nullable()->after('last_login_ip');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['banned_by']);
            $table->dropColumn([
                'banned_by',
                'ban_reason',
                'banned_at',
                'banned_until',
                'is_banned',
                'last_login_at',
                'last_login_ip',
                'notes'
            ]);
        });
    }
};