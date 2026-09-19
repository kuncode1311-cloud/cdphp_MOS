<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Thêm vai trò và mã học sinh vào bảng tài khoản đã có.
        Schema::table('users', function (Blueprint $t) {
            $t->string('role')->default('student')->after('password');
            $t->string('student_code')->nullable()->unique()->after('role');
        });
        // Lớp học liên kết giáo viên; nullOnDelete giữ lớp và bỏ liên kết khi tài khoản giáo viên bị xóa.
        Schema::create('classrooms', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->unsignedTinyInteger('grade');
            $t->string('school_year')->default('2026-2027');
            $t->foreignId('teacher_id')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
        });
        Schema::table('users', fn (Blueprint $t) => $t->foreignId('classroom_id')->nullable()->after('student_code')->constrained()->nullOnDelete());
        // Mỗi lần nộp bài là một bản ghi: điểm, số câu đúng và thời gian làm.
        Schema::create('test_attempts', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->foreignId('practice_test_id')->constrained()->cascadeOnDelete();
            $t->unsignedSmallInteger('score')->default(0);
            $t->unsignedSmallInteger('correct_answers')->default(0);
            $t->unsignedSmallInteger('total_questions')->default(10);
            $t->unsignedInteger('duration_seconds')->default(0);
            $t->timestamp('completed_at')->nullable();
            $t->timestamps();
            $t->index(['user_id', 'practice_test_id']);
        });
    }

    public function down(): void
    {
        // Gỡ lượt làm và liên kết lớp trước khi xóa bảng lớp để tránh vướng khóa ngoại.
        Schema::dropIfExists('test_attempts');
        Schema::table('users', fn (Blueprint $t) => $t->dropConstrainedForeignId('classroom_id'));
        Schema::dropIfExists('classrooms');
        Schema::table('users', fn (Blueprint $t) => $t->dropColumn(['role', 'student_code']));
    }
};
