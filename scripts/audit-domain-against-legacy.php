<?php

declare(strict_types=1);

use App\Models\PracticeTest;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Arr;

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$testSlug = $argv[1] ?? '';
$quizFile = $argv[2] ?? '';

if ($testSlug === '' || $quizFile === '' || ! is_file($quizFile)) {
    fwrite(STDERR, "Cách dùng: php scripts/audit-domain-against-legacy.php <practice-test-slug> <quiz1.js>\n");
    exit(2);
}

$source = file_get_contents($quizFile);
preg_match('/var quizInfo = "([^"]+)"/', $source ?: '', $match);
$quiz = isset($match[1]) ? json_decode(base64_decode($match[1], true) ?: '', true) : null;
$slides = Arr::get($quiz, 'd.sl.g.0.S');

if (! is_array($slides)) {
    fwrite(STDERR, "Không đọc được danh sách câu hỏi từ {$quizFile}.\n");
    exit(2);
}

$test = PracticeTest::query()
    ->where('slug', $testSlug)
    ->with(['questions.options', 'questions.assets'])
    ->firstOrFail();
$questions = $test->questions->sortBy('position')->values();
$differences = [];

if ($questions->count() !== count($slides)) {
    $differences[] = sprintf('Số câu khác nhau: nguồn=%d, MOS=%d', count($slides), $questions->count());
}

foreach ($slides as $index => $slide) {
    $question = $questions->get($index);
    $number = $index + 1;

    if (! $question) {
        $differences[] = "Câu {$number}: thiếu trong MOS";

        continue;
    }

    $sourceType = (string) ($slide['tp'] ?? '');
    if ($question->type !== $sourceType) {
        $differences[] = "Câu {$number}: loại nguồn={$sourceType}, MOS={$question->type}";
    }

    $sourceTitle = normalizedText(textNode($slide['D'] ?? ''));
    if (normalizedText($question->title) !== $sourceTitle) {
        $differences[] = "Câu {$number}: nội dung câu hỏi không khớp";
    }

    $sourceOptions = sourceOptions($slide);
    $domainOptions = $question->options->sortBy('position')->values();
    if ($domainOptions->count() !== count($sourceOptions)) {
        $differences[] = sprintf(
            'Câu %d: số option nguồn=%d, MOS=%d',
            $number,
            count($sourceOptions),
            $domainOptions->count(),
        );

        continue;
    }

    foreach ($sourceOptions as $optionIndex => $expected) {
        $actual = $domainOptions[$optionIndex];
        if ((int) $actual->position !== (int) $expected['position']) {
            $differences[] = "Câu {$number}, option ".($optionIndex + 1).': sai position';
        }
        $actualContent = normalizedText($actual->content);
        $expectedContent = normalizedText($expected['content']);
        if ($sourceType === 'MultipleChoiceText') {
            $actualContent = normalizedText(str_replace('[...]', '', $actualContent));
            $expectedContent = normalizedText(str_replace('[...]', '', $expectedContent));
        }
        if ($actualContent !== $expectedContent) {
            $differences[] = sprintf(
                'Câu %d, option %d: nội dung nguồn="%s", MOS="%s"',
                $number,
                $optionIndex + 1,
                $expectedContent,
                $actualContent,
            );
        }
        if ((bool) $actual->is_correct !== (bool) $expected['is_correct']) {
            $differences[] = "Câu {$number}, option ".($optionIndex + 1).': sai đáp án đúng';
        }
        if (! equivalent($actual->metadata ?? [], $expected['metadata'])) {
            $differences[] = "Câu {$number}, option ".($optionIndex + 1).': sai metadata';
        }
    }

    if ($sourceType === 'Hotspot') {
        $reference = (string) (Arr::get($slide, 'C.i') ?: Arr::get($slide, 'sP.s', ''));
        $sourceStem = pathinfo(basename(str_replace('storage://images/', '', $reference)), PATHINFO_FILENAME);
        $asset = $question->assets->first();
        $assetStem = $asset ? pathinfo((string) $asset->original_name, PATHINFO_FILENAME) : '';
        if (! $asset || $sourceStem !== $assetStem) {
            $differences[] = "Câu {$number}: asset không khớp nguồn";
        } elseif (! assetExists((string) $asset->path)) {
            $differences[] = "Câu {$number}: thiếu file asset nội bộ {$asset->path}";
        }
    }
}

echo json_encode([
    'practice_test' => $test->slug,
    'source_questions' => count($slides),
    'domain_questions' => $questions->count(),
    'differences' => count($differences),
    'details' => $differences,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES).PHP_EOL;

exit($differences === [] ? 0 : 1);

function sourceOptions(array $slide): array
{
    $type = (string) ($slide['tp'] ?? '');
    $raw = $slide['C'] ?? [];
    $options = [];

    if ($type === 'Matching') {
        foreach (($raw['m'] ?? $raw['p'] ?? []) as $position => $pair) {
            $nodes = $pair['p'] ?? [];
            $left = textNode($nodes[0] ?? $pair['p'] ?? '');
            $right = textNode($nodes[1] ?? $pair['r'] ?? '');
            $options[] = option($left, ['left' => $left, 'right' => $right], true, $position);
        }
    } elseif ($type === 'MultipleChoiceText') {
        $answers = Arr::get($raw, 'rt.r', []);
        $html = (string) Arr::get($raw, 'rt.a', '');
        if ($answers !== [] && preg_match_all('/<p[^>]*>(.*?)<\/p>/is', $html, $matches)) {
            $position = 0;
            foreach ($matches[1] as $paragraph) {
                if (! preg_match('/<span\s+id="([^"]+)"[^>]*>/i', $paragraph, $spanMatch)) {
                    continue;
                }
                $answer = collect($answers)->firstWhere('id', $spanMatch[1]) ?? ($answers[$position] ?? null);
                if (! $answer) {
                    continue;
                }
                $withBlank = preg_replace('/<span[^>]*><\/span>/i', '[...]', $paragraph);
                $content = trim((string) preg_replace('/\s+/', ' ', strip_tags((string) $withBlank)));
                $options[] = option($content, [
                    'available_options' => Arr::get($answer, 'data.v', []),
                    'correct_index' => Arr::get($answer, 'data.i'),
                ], true, $position++);
            }
        }
        if ($options === []) {
            $labels = array_values(array_filter(Arr::get($raw, 'rt.d', []), 'is_string'));
            foreach ($labels as $position => $label) {
                $values = Arr::get($answers, "{$position}.data.v", []);
                $correctIndex = Arr::get($answers, "{$position}.data.i");
                if ($correctIndex !== null || $values !== []) {
                    $options[] = option(strip_tags($label), [
                        'available_options' => $values,
                        'correct_index' => $correctIndex,
                    ], true, $position);
                }
            }
        }
    } elseif ($type === 'Hotspot') {
        foreach (($raw['a'] ?? []) as $position => $area) {
            $options[] = option(
                $area['l'] ?? 'Vùng '.($position + 1),
                ['rect' => $area['r'] ?? null],
                (bool) ($area['c'] ?? false),
                $position,
            );
        }
    } elseif ($type === 'Sequence') {
        foreach (($raw['sq'] ?? $raw['chs'] ?? []) as $position => $step) {
            $options[] = option(textNode($step), ['correct_position' => $position], true, $position);
        }
    } else {
        foreach (($raw['chs'] ?? []) as $position => $choice) {
            $options[] = option(
                textNode($choice['t'] ?? $choice),
                [],
                (bool) ($choice['c'] ?? false),
                $position,
            );
        }
    }

    return $options;
}

function option(string $content, array $metadata, bool $isCorrect, int $position): array
{
    return [
        'content' => $content,
        'metadata' => $metadata,
        'is_correct' => $isCorrect,
        'position' => $position,
    ];
}

function textNode(mixed $node): string
{
    if (is_string($node)) {
        return trim(strip_tags($node));
    }
    if (! is_array($node)) {
        return '';
    }
    foreach (['d', 't', 'a', 'h'] as $key) {
        $value = $node[$key] ?? null;
        if (is_array($value) && array_is_list($value)) {
            $value = $value[0] ?? null;
        }
        $text = textNode($value);
        if ($text !== '') {
            return $text;
        }
    }

    return '';
}

function normalizedText(?string $value): string
{
    $decoded = html_entity_decode(strip_tags((string) $value), ENT_QUOTES | ENT_HTML5, 'UTF-8');

    return trim((string) preg_replace('/\s+/u', ' ', $decoded));
}

function equivalent(mixed $actual, mixed $expected): bool
{
    if (is_numeric($actual) && is_numeric($expected)) {
        return abs((float) $actual - (float) $expected) < 0.000001;
    }
    if (is_array($actual) && is_array($expected)) {
        if (! array_is_list($actual) && ! array_is_list($expected)) {
            ksort($actual);
            ksort($expected);
        }
        if (array_keys($actual) !== array_keys($expected)) {
            return false;
        }
        foreach ($actual as $key => $value) {
            if (! equivalent($value, $expected[$key])) {
                return false;
            }
        }

        return true;
    }

    return $actual === $expected;
}

function assetExists(string $publicPath): bool
{
    if (! str_starts_with($publicPath, '/storage/')) {
        return false;
    }

    return is_file(__DIR__.'/../storage/app/public/'.substr($publicPath, strlen('/storage/')));
}
