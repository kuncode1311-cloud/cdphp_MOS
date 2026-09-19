<?php

namespace App\Console\Commands;

use App\Models\Question;
use App\Services\LegacyQuestionNormalizer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class NormalizeLegacyQuestions extends Command
{
    protected $signature = 'questions:normalize-legacy {--force : Ghi đè options đã có}';

    protected $description = 'Chuyển dữ liệu import cũ sang cấu trúc nội bộ của MOS';

    public function handle(LegacyQuestionNormalizer $normalizer): int
    {
        if (! Schema::hasColumn('questions', 'raw_payload')) {
            $this->error('Cột raw_payload không còn tồn tại; lệnh backfill lịch sử đã hết nhiệm vụ.');

            return self::FAILURE;
        }

        $count = 0;
        Question::query()->whereNotNull('raw_payload')->orderBy('id')->chunkById(100, function ($questions) use ($normalizer, &$count) {
            foreach ($questions as $question) {
                if (! $this->option('force') && $question->options()->exists() && $question->configuration !== null) {
                    continue;
                }
                $normalizer->normalize($question);
                $count++;
            }
        });
        $this->info("Đã chuẩn hóa {$count} câu hỏi.");

        return self::SUCCESS;
    }
}
