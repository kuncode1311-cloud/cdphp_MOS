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
        Schema::table('package_orders', function (Blueprint $table) {
            if (! Schema::hasColumn('package_orders', 'parent_id')) {
                $table->foreignId('parent_id')
                    ->nullable()
                    ->after('package_id')
                    ->constrained('package_orders')
                    ->nullOnDelete()
                    ->comment('Khóa ngoại tự liên kết (Self-referencing FK) trỏ về đơn hàng gốc');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('package_orders', function (Blueprint $table) {
            if (Schema::hasColumn('package_orders', 'parent_id')) {
                $table->dropForeign(['parent_id']);
                $table->dropColumn('parent_id');
            }
        });
    }
};
