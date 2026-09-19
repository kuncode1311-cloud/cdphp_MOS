<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tạo bảng lưu tin nhắn hỗ trợ / tư vấn từ khách hàng & giáo viên
     */
    public function up(): void
    {
        Schema::create('support_messages', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120)->comment('Họ và tên người gửi');
            $table->string('phone', 30)->nullable()->comment('Số điện thoại liên hệ');
            $table->string('email', 120)->nullable()->comment('Email liên hệ');
            $table->text('message')->comment('Nội dung câu hỏi / tư vấn');
            $table->string('status', 30)->default('pending')->comment('Trạng thái: pending, replied, closed');
            $table->string('ip_address', 45)->nullable()->comment('Địa chỉ IP của người gửi');
            $table->timestamps();
        });
    }

    /**
     * Rollback bảng support_messages
     */
    public function down(): void
    {
        Schema::dropIfExists('support_messages');
    }
};
