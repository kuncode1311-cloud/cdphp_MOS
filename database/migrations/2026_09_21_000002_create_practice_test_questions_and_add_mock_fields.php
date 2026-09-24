<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Chạy migration: Bổ sung trường cho Đề thi thử (Mock Exam) và Bảng liên kết câu hỏi
     */
    public function up(): void
    {
        Schema::table('practice_tests', function (Blueprint $table) {
            $table->boolean('is_mock')->default(false)->after('difficulty');
            $table->foreignId('level_id')->nullable()->after('is_mock')->constrained('levels')->nullOnDelete();
        });

        // Chuyển topic_id sang nullable cho các đề thi thử cấp khối (không thuộc riêng 1 chủ đề)
        Schema::table('practice_tests', function (Blueprint $table) {
            $table->unsignedBigInteger('topic_id')->nullable()->change();
        });

        // Bảng pivot liên kết nhiều câu hỏi vào đề thi thử (Many-to-Many)
        Schema::create('practice_test_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('practice_test_id')->constrained('practice_tests')->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('questions')->cascadeOnDelete();
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();

            $table->unique(['practice_test_id', 'question_id']);
            $table->index(['practice_test_id', 'position']);
        });
    }

    /**
     * Đảo ngược migration
     */
    public function down(): void
    {
        Schema::dropIfExists('practice_test_questions');

        Schema::table('practice_tests', function (Blueprint $table) {
            $table->dropForeign(['level_id']);
            $table->dropColumn(['is_mock', 'level_id']);
            $table->unsignedBigInteger('topic_id')->nullable(false)->change();
        });
    }
};
