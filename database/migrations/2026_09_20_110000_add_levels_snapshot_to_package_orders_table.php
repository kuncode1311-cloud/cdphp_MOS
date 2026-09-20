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
            if (! Schema::hasColumn('package_orders', 'levels_snapshot')) {
                $table->string('levels_snapshot', 255)->nullable()->after('max_students')->comment('Ảnh chụp danh sách khối lớp lúc đặt mua');
            }
            if (! Schema::hasColumn('package_orders', 'order_type')) {
                $table->string('order_type', 30)->default('subscription')->after('levels_snapshot')->comment('Loại đơn: subscription (chuẩn), quota_add (mua thêm sĩ số)');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('package_orders', function (Blueprint $table) {
            if (Schema::hasColumn('package_orders', 'order_type')) {
                $table->dropColumn('order_type');
            }
            if (Schema::hasColumn('package_orders', 'levels_snapshot')) {
                $table->dropColumn('levels_snapshot');
            }
        });
    }
};
