<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('cores', function (Blueprint $table) {
            // Добавляем description после version
            if (!Schema::hasColumn('cores', 'description')) {
                $table->text('description')->nullable()->after('version');
            }
            
            // Добавляем is_stable после is_default
            if (!Schema::hasColumn('cores', 'is_stable')) {
                $table->boolean('is_stable')->default(true)->after('is_default');
            }
            
            // Добавляем compatible_versions после is_active
            if (!Schema::hasColumn('cores', 'compatible_versions')) {
                $table->json('compatible_versions')->nullable()->after('is_active');
            }
        });
    }

    public function down()
    {
        Schema::table('cores', function (Blueprint $table) {
            $table->dropColumn(['description', 'is_stable', 'compatible_versions']);
        });
    }
};
