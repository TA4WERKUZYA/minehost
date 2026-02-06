<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Проверяем и добавляем только недостающие колонки
        if (!Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('role')->default('user')->after('email');
            });
        }
        
        if (!Schema::hasColumn('users', 'balance')) {
            Schema::table('users', function (Blueprint $table) {
                $table->decimal('balance', 10, 2)->default(0)->after('role');
            });
        }
        
        if (!Schema::hasColumn('users', 'is_banned')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('is_banned')->default(false)->after('balance');
            });
        }
        
        if (!Schema::hasColumn('users', 'banned_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->timestamp('banned_at')->nullable()->after('is_banned');
            });
        }
        
        if (!Schema::hasColumn('users', 'banned_until')) {
            Schema::table('users', function (Blueprint $table) {
                $table->timestamp('banned_until')->nullable()->after('banned_at');
            });
        }
        
        if (!Schema::hasColumn('users', 'email_verified_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->timestamp('email_verified_at')->nullable()->after('email');
            });
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Удаляем только если существуют
            $columns = ['role', 'balance', 'is_banned', 'banned_at', 'banned_until', 'email_verified_at'];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};