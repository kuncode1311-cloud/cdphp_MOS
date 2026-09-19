<?php

namespace App\Console\Commands;

use App\Models\Question;
use App\Models\QuestionAsset;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class InternalizeQuestionAssetReferences extends Command
{
    protected $signature = 'questions:internalize-asset-references';

    protected $description = 'Đổi mọi đường dẫn media runtime sang question_assets nội bộ';

    public function handle(): int
    {
        $backupDirectory = storage_path('app/backups');
        File::ensureDirectoryExists($backupDirectory);
        $backupPath = $backupDirectory.'/question-assets-before-internalize-'.now()->format('Ymd-His').'.json';
        File::put($backupPath, json_encode([
            'questions' => Question::query()->whereNotNull('configuration')->get(['id', 'configuration']),
            'options' => DB::table('question_options')->whereNotNull('image_path')->get(),
            'assets' => QuestionAsset::query()->get(),
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        $missing = [];
        DB::transaction(function () use (&$missing): void {
            QuestionAsset::query()->where('path', 'like', '/legacy/%')->each(function (QuestionAsset $asset) use (&$missing): void {
                $legacyPath = $asset->path;
                $filename = basename($legacyPath);
                $internalPath = '/storage/question-assets/missing/q'.$asset->question_id.'-'.$asset->id.'-'.$filename;
                $metadata = $asset->metadata ?? [];
                $metadata['missing'] = true;
                $metadata['legacy_path'] = $legacyPath;
                $asset->update(['path' => $internalPath, 'metadata' => $metadata]);
                $missing[] = $asset->id;
            });

            Question::query()->with(['assets', 'options'])->chunkById(100, function ($questions): void {
                foreach ($questions as $question) {
                    $assetsByName = $question->assets->keyBy(
                        fn ($asset) => $asset->original_name ?: basename($asset->path),
                    );

                    foreach ($question->options as $option) {
                        if (! $option->image_path) {
                            continue;
                        }
                        $asset = $assetsByName->get(basename($option->image_path));
                        if ($asset && $option->image_path !== $asset->path) {
                            $option->update(['image_path' => $asset->path]);
                        }
                    }

                    $configuration = $question->configuration ?? [];
                    if (! empty($configuration['image_path'])) {
                        $asset = $assetsByName->get(basename($configuration['image_path']));
                        if ($asset && $configuration['image_path'] !== $asset->path) {
                            $configuration['image_path'] = $asset->path;
                            $question->updateQuietly(['configuration' => $configuration]);
                        }
                    }
                }
            });
        });

        $this->info('Backup: '.$backupPath);
        $this->info('Đã đồng bộ đường dẫn nội bộ; asset thiếu: '.count($missing).'.');

        return self::SUCCESS;
    }
}
