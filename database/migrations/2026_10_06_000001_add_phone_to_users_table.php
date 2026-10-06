<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Số điện thoại / Zalo của tài khoản (học sinh nhỏ thì là SĐT phụ huynh).
 * Dùng để liên hệ hỗ trợ và để xác minh chính chủ khi quên tài khoản.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'phone')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('phone', 20)->nullable()->after('email')->index();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'phone')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropIndex(['phone']);
                $table->dropColumn('phone');
            });
        }
    }
};
