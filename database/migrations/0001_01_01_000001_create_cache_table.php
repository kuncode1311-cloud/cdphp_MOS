<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Chạy migrate thì Laravel gọi up() để tạo hoặc bổ sung cấu trúc bảng.
     */
    public function up(): void
    {
        // Dữ liệu lưu tạm để lần sau lấy nhanh hơn; expiration là thời điểm hết hạn.
        Schema::create('cache', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->mediumText('value');
            $table->bigInteger('expiration')->index();
        });

        // Khóa tạm giúp các tiến trình tránh cùng xử lý một việc cần chạy riêng.
        Schema::create('cache_locks', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->string('owner');
            $table->bigInteger('expiration')->index();
        });
    }

    /**
     * Khi rollback, down() gỡ lại phần cấu trúc mà migration này đã tạo.
     */
    public function down(): void
    {
        Schema::dropIfExists('cache');
        Schema::dropIfExists('cache_locks');
    }
};
