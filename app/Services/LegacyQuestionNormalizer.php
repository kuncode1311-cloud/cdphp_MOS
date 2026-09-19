<?php

namespace App\Services;

use App\Models\Question;
use Illuminate\Support\Arr;

/** Chuyển JSON import cũ thành dữ liệu miền của MOS. Chỉ dùng trong import/backfill. */
class LegacyQuestionNormalizer
{
    public function normalize(Question $question, ?array $payload = null): void
    {
        $raw = $payload ?? $question->raw_payload ?? [];
        if (is_string($raw)) {
            $raw = json_decode($raw, true) ?: [];
        }
        $question->loadMissing('assets');
        $type = $question->type;
        $configuration = [];
        $options = [];
        $content = $this->text(Arr::get($raw, 'D')) ?: $question->title;

        if ($type === 'Matching') {
            foreach (Arr::get($raw, 'C.m', Arr::get($raw, 'C.p', [])) as $position => $pair) {
                $nodes = $pair['p'] ?? [];
                $options[] = ['content' => $this->text($nodes[0] ?? $pair['p'] ?? ''), 'metadata' => ['left' => $this->text($nodes[0] ?? $pair['p'] ?? ''), 'right' => $this->text($nodes[1] ?? $pair['r'] ?? '')], 'is_correct' => true, 'position' => $position];
            }
        } elseif ($type === 'MultipleChoiceText') {
            $answers = Arr::get($raw, 'C.rt.r', []);
            $html = Arr::get($raw, 'C.rt.a', '');
            if (!empty($answers) && preg_match_all('/<p[^>]*>(.*?)<\/p>/is', $html, $matches)) {
                $pIdx = 0;
                foreach ($matches[1] as $p) {
                    $pClean = trim(strip_tags($p, '<span>'));
                    if ($pClean === '' || $pClean === '​') continue;
                    if (preg_match('/<span\s+id="([^"]+)"[^>]*>/i', $p, $spanMatch)) {
                        $spanId = $spanMatch[1];
                        $ansDef = collect($answers)->firstWhere('id', $spanId) ?? ($answers[$pIdx] ?? null);
                        if ($ansDef) {
                            $textWithBlank = preg_replace('/<span[^>]*><\/span>/i', '[...]', $p);
                            $cleanedText = trim(preg_replace('/\s+/', ' ', strip_tags($textWithBlank)));
                            $options[] = [
                                'content' => $cleanedText,
                                'metadata' => [
                                    'available_options' => Arr::get($ansDef, 'data.v', []),
                                    'correct_index' => Arr::get($ansDef, 'data.i'),
                                ],
                                'is_correct' => true,
                                'position' => $pIdx,
                            ];
                            $pIdx++;
                        }
                    }
                }
            }
            if (empty($options)) {
                $labels = array_values(array_filter(Arr::get($raw, 'C.rt.d', []), 'is_string'));
                foreach ($labels as $position => $label) {
                    $values = Arr::get($answers, "$position.data.v", []);
                    $answerIndex = Arr::get($answers, "$position.data.i");
                    if ($answerIndex !== null || !empty($values)) {
                        $options[] = ['content' => trim(strip_tags($label)), 'metadata' => ['available_options' => $values, 'correct_index' => $answerIndex], 'is_correct' => true, 'position' => $position];
                    }
                }
            }
        } elseif ($type === 'Hotspot') {
            $configuration['image_path'] = $this->assetPath($question, Arr::get($raw, 'C.i') ?: Arr::get($raw, 'sP.s'));
            foreach (Arr::get($raw, 'C.a', []) as $position => $area) {
                $options[] = ['content' => $area['l'] ?? 'Vùng '.($position + 1), 'metadata' => ['rect' => $area['r'] ?? null], 'is_correct' => (bool) ($area['c'] ?? false), 'position' => $position];
            }
        } elseif ($type === 'Sequence') {
            foreach (Arr::get($raw, 'C.sq', Arr::get($raw, 'C.chs', [])) as $position => $step) {
                $options[] = ['content' => $this->text($step), 'metadata' => ['correct_position' => $position], 'is_correct' => true, 'position' => $position];
            }
        } else {
            foreach (Arr::get($raw, 'C.chs', []) as $position => $choice) {
                $options[] = ['content' => $this->text($choice['t'] ?? $choice), 'image_path' => $this->assetPath($question, $choice['ia']['i'] ?? $choice['img'] ?? null), 'metadata' => [], 'is_correct' => (bool) ($choice['c'] ?? false), 'position' => $position];
            }
        }

        $question->updateQuietly(['title' => $content, 'configuration' => $configuration]);
        $question->options()->delete();
        foreach ($options as $option) {
            if ($option['content'] !== '' || ! empty($option['image_path'])) {
                $question->options()->create($option);
            }
        }
    }

    private function text(mixed $node): string
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
            $text = $this->text($value);
            if ($text !== '') {
                return $text;
            }
        }

        return '';
    }

    private function assetPath(Question $question, mixed $reference): ?string
    {
        if (! is_string($reference) || $reference === '') {
            return null;
        }
        $filename = basename(str_replace('storage://images/', '', $reference));

        return $question->assets->first(fn ($asset) => basename($asset->path) === $filename)?->path;
    }
}
