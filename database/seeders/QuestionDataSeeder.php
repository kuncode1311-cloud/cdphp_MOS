<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class QuestionDataSeeder extends Seeder
{
    /**
     * Nạp dữ liệu 509 câu hỏi, 1936 đáp án và 36 tệp đính kèm IC3 vào CSDL
     */
    public function run(): void
    {
        if (app()->runningUnitTests()) {
            return;
        }

        $jsonPath = database_path('data/ic3_questions_seed.json');

        if (! File::exists($jsonPath)) {
            return;
        }

        if (DB::table('questions')->count() === 0) {
            $data = json_decode(File::get($jsonPath), true);
            if (! $data || empty($data['questions'])) {
                return;
            }

            Schema::disableForeignKeyConstraints();

            try {
                $questions = $data['questions'];
                foreach (array_chunk($questions, 50) as $chunk) {
                    DB::table('questions')->insert($chunk);
                }

                if (! empty($data['options'])) {
                    foreach (array_chunk($data['options'], 100) as $chunk) {
                        DB::table('question_options')->insert($chunk);
                    }
                }

                if (! empty($data['assets'])) {
                    foreach (array_chunk($data['assets'], 50) as $chunk) {
                        DB::table('question_assets')->insert($chunk);
                    }
                }

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
}

