<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('practice_tests', function (Blueprint $table) {
            $table->json('resource_manifest')->nullable()->after('access_code');
        });

        // Câu hỏi của bài luyện; raw_payload giữ cấu trúc dùng cho màn hình làm bài và chấm điểm.
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('practice_test_id')->constrained()->cascadeOnDelete();
            $table->string('external_id')->nullable();
            $table->string('type', 60)->default('MultipleChoice');
            $table->text('title')->nullable();
            $table->longText('raw_payload');
            $table->unsignedSmallInteger('position')->default(0);
            $table->unsignedSmallInteger('points')->default(1);
            $table->boolean('is_published')->default(true);
            $table->timestamps();
            $table->unique(['practice_test_id', 'position']);
            $table->index(['practice_test_id', 'is_published']);
        });

        // Các lựa chọn của câu hỏi; is_correct đánh dấu đáp án đúng.
        Schema::create('question_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->text('content')->nullable();
            $table->string('image_path')->nullable();
            $table->boolean('is_correct')->default(false);
            $table->unsignedSmallInteger('position')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['question_id', 'position']);
        });

        // Thông tin ảnh, âm thanh hoặc tài liệu; bảng lưu đường dẫn chứ không chứa nội dung file.
        Schema::create('question_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->string('kind', 30)->default('image');
            $table->string('path');
            $table->string('original_name')->nullable();
            $table->string('mime_type')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('question_assets');
        Schema::dropIfExists('question_options');
        Schema::dropIfExists('questions');
        Schema::table('practice_tests', fn (Blueprint $table) => $table->dropColumn('resource_manifest'));
    }
};
