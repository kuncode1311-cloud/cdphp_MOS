<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Chạy migration: Tạo bảng lưu vết câu hỏi làm sai & tiến trình phục thù của học sinh
     */
    public function up(): void
    {
        Schema::create('student_mistakes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('questions')->cascadeOnDelete();
            $table->foreignId('practice_test_id')->nullable()->constrained('practice_tests')->nullOnDelete();
            $table->unsignedSmallInteger('wrong_count')->default(1);
            $table->unsignedSmallInteger('correct_count')->default(0);
            $table->json('last_student_answer')->nullable();
            $table->string('status', 20)->default('unresolved'); // 'unresolved' (chưa sửa được) hoặc 'resolved' (đã sửa đúng)
            $table->timestamp('last_wrong_at')->useCurrent();
            $table->timestamp('last_resolved_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'question_id']);
            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'wrong_count']);
        });
    }

    /**
     * Đảo ngược migration
     */
    public function down(): void
    {
        Schema::dropIfExists('student_mistakes');
    }
};
