<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['deposit', 'withdrawal', 'payment', 'refund', 'bonus', 'manual']);
            $table->decimal('amount', 10, 2);
            $table->string('description');
            $table->decimal('old_balance', 10, 2)->default(0);
            $table->decimal('new_balance', 10, 2)->default(0);
            $table->foreignId('admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('ip_address')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            // Индексы для быстрого поиска
            $table->index(['user_id', 'created_at']);
            $table->index(['type', 'created_at']);
            $table->index(['admin_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};