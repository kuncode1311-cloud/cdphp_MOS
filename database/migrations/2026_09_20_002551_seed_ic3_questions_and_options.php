<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tự động nạp 509 câu hỏi chuẩn IC3, 1936 đáp án và 36 tệp đồ họa vào cơ sở dữ liệu trên môi trường máy chủ (Railway/Production)
     */
    public function up(): void
    {
        if (app()->runningUnitTests()) {
            return;
        }

        $jsonPath = database_path('data/ic3_questions_seed.json');
        if (! File::exists($jsonPath)) {
            return;
        }


        // Nếu cơ sở dữ liệu chưa có câu hỏi nào (hoặc môi trường mới như Railway)
        if (DB::table('questions')->count() === 0) {
            $data = json_decode(File::get($jsonPath), true);
            if (! $data || empty($data['questions'])) {
                return;
            }

            Schema::disableForeignKeyConstraints();

            try {
                // 1. Nạp danh sách Câu hỏi (509 câu)
                $questions = $data['questions'];
                foreach (array_chunk($questions, 50) as $chunk) {
                    DB::table('questions')->insert($chunk);
                }

                // 2. Nạp danh sách Đáp án lựa chọn (1936 đáp án)
                if (! empty($data['options'])) {
                    foreach (array_chunk($data['options'], 100) as $chunk) {
                        DB::table('question_options')->insert($chunk);
                    }
                }

                // 3. Nạp danh mục Tệp đính kèm / Ảnh minh họa (36 assets)
                if (! empty($data['assets'])) {
                    foreach (array_chunk($data['assets'], 50) as $chunk) {
                        DB::table('question_assets')->insert($chunk);
                    }
                }

                // 4. Cập nhật số lượng câu hỏi thực tế (question_count) cho từng bài luyện
                if (! empty($data['test_counts'])) {
                    foreach ($data['test_counts'] as $testId => $count) {
                        DB::table('practice_tests')
                            ->where('id', $testId)
                            ->update([
                                'question_count' => (int) $count,
                                'is_published' => 1,
                            ]);
                    }
                }
            } finally {
                Schema::enableForeignKeyConstraints();
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Giữ nguyên dữ liệu an toàn
    }
};

