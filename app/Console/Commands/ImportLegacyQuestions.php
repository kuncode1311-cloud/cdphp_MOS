<?php

namespace App\Console\Commands;

use App\Models\PracticeTest;
use App\Services\LegacyQuestionNormalizer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ImportLegacyQuestions extends Command
{
    protected $signature = 'ic3:import-questions {--force : Ghi đè câu hỏi đã nhập}';

    protected $description = 'Nhập câu hỏi IC3 từ gói iSpring legacy vào cơ sở dữ liệu';

    // Lệnh artisan ic3:import-questions bắt đầu từ đây, đọc gói câu hỏi cũ vào database.
    public function handle(LegacyQuestionNormalizer $normalizer): int
    {
        $manifestPath = public_path('legacy/ic3/manifest.json');
        if (! is_file($manifestPath)) {
            $backupZip = storage_path('legacy_backup/legacy_ic3_source.zip');
            if (is_file($backupZip) && class_exists('\ZipArchive')) {
                $this->info("Đang tự động giải nén gói nguồn từ {$backupZip} vào public/legacy/...");
                $zip = new \ZipArchive();
                if ($zip->open($backupZip) === true) {
                    $zip->extractTo(public_path('legacy'));
                    $zip->close();
                }
            }
        }

        if (! is_file($manifestPath)) {
            $this->error('Không tìm thấy manifest của gói import IC3.');

            return self::FAILURE;
        }
        $manifest = json_decode(file_get_contents($manifestPath), true, flags: JSON_THROW_ON_ERROR);
        $imported = 0;

        foreach ($manifest as $source) {
            $testSlug = $this->domainTestSlug($source);
            $test = $testSlug ? PracticeTest::query()->where('slug', $testSlug)->first() : null;
            if (! $test) {
                $this->warn('Không tìm thấy bộ đề domain cho '.($source['slug'] ?? 'nguồn không xác định'));

                continue;
            }
            if ($test->questions()->exists() && ! $this->option('force')) {
                $this->line("Bỏ qua {$test->slug}: đã có dữ liệu.");

                continue;
            }

            $baseDirectory = dirname(ltrim((string) ($source['local_path'] ?? ''), '/'));
            $quizPath = public_path($baseDirectory.'/data/quiz1.js');
            if (! is_file($quizPath)) {
                $this->warn("Thiếu quiz1.js: {$test->slug}");

                continue;
            }

            $quiz = $this->decodeQuiz($quizPath);
            $slides = data_get($quiz, 'd.sl.g.0.S', []);

            DB::transaction(function () use ($test, $slides, $baseDirectory, $normalizer, &$imported): void {
                $test->questions()->delete();
                $test->update(['question_count' => count($slides)]);

                foreach ($slides as $position => $slide) {
                    $question = $test->questions()->create([
                        'type' => (string) ($slide['tp'] ?? 'Unknown'),
                        'title' => $this->extractQuestionTitle($slide),
                        'configuration' => [],
                        'position' => $position,
                        'points' => 1,
                        'is_published' => true,
                    ]);

                    foreach ($this->findAssetPaths($slide, $baseDirectory) as $assetData) {
                        $question->assets()->create([
                            'kind' => $assetData['kind'],
                            'path' => $assetData['path'],
                            'original_name' => basename($assetData['path']),
                            'mime_type' => null,
                        ]);
                    }
                    // Ranh giới import: chuyển ngay dữ liệu nguồn sang domain schema MOS.
                    $normalizer->normalize($question, $slide);
                    $imported++;
                }
            });

            $this->info("Đã nhập {$test->slug}: ".count($slides).' câu.');
        }

        Artisan::call('questions:relocate-assets');
        $this->output->write(Artisan::output());
        Artisan::call('questions:internalize-asset-references');
        $this->output->write(Artisan::output());

        $this->info("Hoàn tất: {$imported} câu hỏi đã được chuẩn hóa trong CSDL.");

        return self::SUCCESS;
    }

    private function domainTestSlug(array $source): ?string
    {
        $grade = (int) ($source['grade'] ?? 0);
        $slug = strtolower((string) ($source['slug'] ?? ''));
        if ($grade === 3 && str_ends_with($slug, '-cdmr')) {
            return 'k3-cd7-bai-1';
        }
        if (! preg_match('/-cd(\d+)-t(\d+)$/', $slug, $match)) {
            return null;
        }

        return "k{$grade}-cd{$match[1]}-bai-{$match[2]}";
    }

    private function decodeQuiz(string $path): array
    {
        preg_match('/var quizInfo = "([^"]+)"/', file_get_contents($path), $match);

        return json_decode(base64_decode($match[1] ?? ''), true, flags: JSON_THROW_ON_ERROR);
    }

    private function extractQuestionTitle(array $slide): string
    {
        $preferred = data_get($slide, 'D.a') ?? data_get($slide, 'D.d.0');
        if (is_string($preferred) && trim(strip_tags($preferred)) !== '') {
            return Str::limit(trim(strip_tags($preferred)), 1000, '');
        }

        return '(Câu hỏi tương tác không có tiêu đề)';
    }

    private function findAssetPaths(array $slide, string $baseDirectory): array
    {
        preg_match_all('/(?:storage:\/\/images\/[^\s"]+|img-[a-zA-Z0-9_\-]+\.(?:png|jpe?g|gif|svg|webp)|[a-zA-Z0-9_\-\/]+\.(?:mp3|wav|ogg|m4a))/i', json_encode($slide), $matches);

        $assets = [];
        $seen = [];
        foreach ($matches[0] ?? [] as $raw) {
            $path = $this->resolvePath($raw, $baseDirectory);
            if (isset($seen[$path])) {
                continue;
            }
            $seen[$path] = true;

            $kind = preg_match('/\.(mp3|wav|ogg|m4a)$/i', $path) ? 'audio' : 'image';
            $assets[] = [
                'kind' => $kind,
                'path' => $path,
            ];
        }

        return $assets;
    }

    private function resolvePath(string $rawPath, string $baseDirectory): string
    {
        $clean = trim($rawPath);
        if (Str::startsWith($clean, 'storage://images/')) {
            $clean = 'quiz1/images/'.Str::after($clean, 'storage://images/');
        } elseif (Str::startsWith($clean, 'storage://')) {
            $clean = 'quiz1/'.Str::after($clean, 'storage://');
        } elseif (Str::startsWith($clean, 'img-')) {
            $clean = 'quiz1/images/'.$clean;
        }

        if (Str::startsWith($clean, '/')) {
            return $clean;
        }

        return '/'.$baseDirectory.'/data/'.ltrim($clean, '/');
    }
}
