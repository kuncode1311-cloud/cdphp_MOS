<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Thời điểm hết hạn của mã thanh toán online (PayOS). Quá giờ mà chưa trả tiền thì đơn tự hủy.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('package_orders', 'payos_expires_at')) {
            Schema::table('package_orders', function (Blueprint $table) {
                $table->timestamp('payos_expires_at')->nullable()->after('payment_method');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('package_orders', 'payos_expires_at')) {
            Schema::table('package_orders', function (Blueprint $table) {
                $table->dropColumn('payos_expires_at');
            });
        }
    }
};
