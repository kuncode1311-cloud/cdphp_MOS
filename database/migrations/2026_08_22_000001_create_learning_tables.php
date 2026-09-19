<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Cấu trúc học đi từ chương trình → khối → chủ đề → bài luyện.
        Schema::create('programs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('accent')->default('#5b5ce2');
            $table->timestamps();
        });

        // Mỗi khối thuộc một chương trình; cascadeOnDelete là xóa cha thì dữ liệu con cũng bị xóa.
        Schema::create('levels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->unsignedTinyInteger('grade');
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();
            $table->unique(['program_id', 'slug']);
        });

        // Mỗi chủ đề thuộc một khối; position dùng để sắp thứ tự hiển thị.
        Schema::create('topics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('level_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('icon')->default('sparkles');
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();
            $table->unique(['level_id', 'slug']);
        });

        // Bài luyện nằm trong chủ đề; is_published là trạng thái phát hành.
        Schema::create('practice_tests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('topic_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->unsignedSmallInteger('duration_minutes')->default(20);
            $table->unsignedSmallInteger('question_count')->default(20);
            $table->unsignedSmallInteger('pass_score')->default(700);
            $table->unsignedSmallInteger('max_score')->default(1000);
            $table->enum('difficulty', ['Cơ bản', 'Trung bình', 'Nâng cao'])->default('Cơ bản');
            $table->boolean('is_published')->default(true);
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();
            $table->unique(['topic_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('practice_tests');
        Schema::dropIfExists('topics');
        Schema::dropIfExists('levels');
        Schema::dropIfExists('programs');
    }
};
