<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Lưu tài khoản đã đăng nhập khi gửi tin để nhận diện loại người gửi chính xác,
     * và dọn các SĐT là chữ mặc định do khung chat cũ tự điền.
     */
    public function up(): void
    {
        Schema::table('support_messages', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
        });

        DB::table('support_messages')->where('phone', 'Khách truy cập trang Đăng nhập')->update(['phone' => null]);
    }

    public function down(): void
    {
        Schema::table('support_messages', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
        });
    }
};