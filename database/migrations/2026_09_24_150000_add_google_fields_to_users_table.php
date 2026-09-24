<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bổ sung cột google_id và avatar cho bảng users để phục vụ đăng nhập qua Google OAuth 2.0.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('google_id')->nullable()->index()->after('email')->comment('Mã định danh tài khoản Google OAuth 2.0');
            $table->string('avatar')->nullable()->after('name')->comment('Ảnh đại diện người dùng hoặc URL avatar Google');
        });
    }

    /**
     * Thu hồi thay đổi khi rollback.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['google_id', 'avatar']);
        });
    }
};
