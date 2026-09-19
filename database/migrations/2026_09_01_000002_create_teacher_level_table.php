<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Chạy migrate thì Laravel gọi up() để tạo hoặc bổ sung cấu trúc bảng.
     * Bảng pivot lưu danh sách các Khối lớp (Level) mà Admin cấp quyền cho Giáo viên.
     */
    public function up(): void
    {
        // Bảng nối quyền khối của giáo viên; quyền học sinh lưu riêng trong level_user.
        Schema::create('teacher_level', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('level_id')->constrained('levels')->cascadeOnDelete();
            $table->timestamps();

            // Mỗi giáo viên chỉ gán 1 lần với 1 khối lớp
            $table->unique(['teacher_id', 'level_id']);
        });
    }

    /**
     * Khi rollback, down() gỡ lại phần cấu trúc mà migration này đã tạo.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_level');
    }
};
