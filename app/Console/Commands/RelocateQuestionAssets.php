<?php

namespace App\Console\Commands;

use App\Models\QuestionAsset;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/** Sao chép media import vào kho nội bộ MOS và cập nhật đường dẫn bản ghi. */
class RelocateQuestionAssets extends Command
{
    protected $signature = 'questions:relocate-assets {--dry-run}';

    protected $description = 'Chuyển tài nguyên câu hỏi từ thư mục legacy sang storage của MOS';

    public function handle(): int
    {
        $moved = 0;
        $missing = 0;
        QuestionAsset::query()->orderBy('id')->each(function (QuestionAsset $asset) use (&$moved, &$missing) {
            if (str_starts_with($asset->path, '/storage/question-assets/')) {
                return;
            }
            $source = public_path(ltrim($asset->path, '/'));
            if (! File::isFile($source)) {
                $missing++;
                $this->warn("Không tìm thấy: {$asset->path}");

                return;
            }
            $extension = pathinfo($source, PATHINFO_EXTENSION);
            $target = 'question-assets/imported/q'.$asset->question_id.'-'.$asset->id.'.'.$extension;
            if (! $this->option('dry-run')) {
                File::ensureDirectoryExists(dirname(storage_path('app/public/'.$target)));
                File::copy($source, storage_path('app/public/'.$target));
            }
            if (! $this->option('dry-run')) {
                $asset->update(['path' => '/storage/'.$target]);
            }
            $moved++;
        });
        $this->info("Đã chuyển {$moved} file; thiếu {$missing} file.");

        return $missing ? self::FAILURE : self::SUCCESS;
    }
}
