<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Lớp gọi mô hình AI dùng chung cho Trợ lý AI trong khung chat.
 *
 * Ưu tiên 9Router (cấu hình QUESTION_AI_*), lỗi thì dự phòng sang Gemini (GEMINI_API_KEYS).
 */
class AiChatClient
{
    private const GEMINI_MODEL = 'gemini-2.5-flash';
    private const GEMINI_BASE_URL = 'https://generativelanguage.googleapis.com/v1beta';

    /**
     * @param  array<int, array{role: string, text: string}>  $turns  Lượt hội thoại; lượt cuối là của người dùng
     */
    public function complete(string $system, array $turns, int $maxTokens = 600): ?string
    {
        return $this->askNineRouter($system, $turns, $maxTokens) ?? $this->askGemini($system, $turns, $maxTokens);
    }

    /**
     * Gọi 9Router kèm danh sách hàm AI được tự gọi (function calling). Chỉ 9Router, không dự phòng Gemini:
     * lỗi thì trả null để nơi gọi quay về cách trả lời thường.
     *
     * @param  array<int, array<string, mixed>>  $messages  Tin nhắn chuẩn OpenAI (system/user/assistant/tool)
     * @param  array<int, array<string, mixed>>  $tools
     * @param  bool  $allowTools  false = có hàm trong lịch sử nhưng bắt AI trả lời luôn (vòng cuối)
     * @return array{content: string, tool_calls: array<int, array{id: string, name: string, arguments: array}>}|null
     */
    public function chatWithTools(array $messages, array $tools, int $maxTokens, bool $allowTools = true): ?array
    {
        $baseUrl = rtrim(trim((string) config('services.question_ai.base_url')), '/');
        $model = trim((string) config('services.question_ai.model'));
        if ($baseUrl === '' || $model === '') {
            return null;
        }

        try {
            $request = Http::acceptJson()
                ->connectTimeout(max(1, (int) config('services.question_ai.connect_timeout', 10)))
                ->timeout(max(1, min(30, (int) config('services.question_ai.timeout', 60))))
                ->withoutRedirecting();
            $apiKey = trim((string) config('services.question_ai.api_key'));
            if ($apiKey !== '') {
                $request = $request->withToken($apiKey);
            }

            $response = $request->post($baseUrl . '/chat/completions', [
                'model' => $model,
                'messages' => $messages,
                'tools' => $tools,
                'tool_choice' => $allowTools ? 'auto' : 'none',
                'temperature' => 0.3,
                'max_tokens' => $maxTokens,
                'stream' => false,
            ]);

            if (! $response->successful()) {
                Log::warning("AiChatClient: 9Router (có hàm) trả về HTTP {$response->status()}.");

                return null;
            }

            return $this->readToolMessage($response->body());
        } catch (\Throwable $e) {
            Log::warning('AiChatClient: không gọi được 9Router có hàm (' . $e::class . ').');

            return null;
        }
    }

    /** Đọc nội dung + các lời gọi hàm, từ JSON thường hoặc luồng SSE */
    private function readToolMessage(string $body): ?array
    {
        $content = '';
        $calls = [];

        $json = json_decode($body, true);
        if (is_array($json)) {
            $content = (string) data_get($json, 'choices.0.message.content', '');
            foreach ((array) data_get($json, 'choices.0.message.tool_calls', []) as $i => $call) {
                $calls[$i] = ['id' => (string) ($call['id'] ?? ''), 'name' => (string) data_get($call, 'function.name', ''), 'arguments' => (string) data_get($call, 'function.arguments', '')];
            }
        } else {
            foreach (preg_split('/\r?\n/', $body) as $line) {
                $payload = str_starts_with($line, 'data:') ? trim(substr($line, 5)) : '';
                if ($payload === '' || $payload === '[DONE]') {
                    continue;
                }
                $delta = (array) data_get(json_decode($payload, true), 'choices.0.delta', []);
                $content .= (string) ($delta['content'] ?? '');
                foreach ((array) ($delta['tool_calls'] ?? []) as $call) {
                    $i = (int) ($call['index'] ?? 0);
                    $calls[$i] ??= ['id' => '', 'name' => '', 'arguments' => ''];
                    $calls[$i]['id'] = (string) ($call['id'] ?? $calls[$i]['id']);
                    $calls[$i]['name'] .= (string) data_get($call, 'function.name', '');
                    $calls[$i]['arguments'] .= (string) data_get($call, 'function.arguments', '');
                }
            }
        }

        $toolCalls = [];
        foreach ($calls as $i => $call) {
            if ($call['name'] === '') {
                continue;
            }
            $args = json_decode($call['arguments'] !== '' ? $call['arguments'] : '{}', true);
            $toolCalls[] = ['id' => $call['id'] !== '' ? $call['id'] : 'call_' . $i, 'name' => $call['name'], 'arguments' => is_array($args) ? $args : []];
        }

        if (trim($content) === '' && $toolCalls === []) {
            return null;
        }

        return ['content' => trim($content), 'tool_calls' => $toolCalls];
    }

    private function askNineRouter(string $system, array $turns, int $maxTokens): ?string
    {
        $baseUrl = rtrim(trim((string) config('services.question_ai.base_url')), '/');
        $model = trim((string) config('services.question_ai.model'));
        if ($baseUrl === '' || $model === '') {
            return null;
        }

        $messages = [['role' => 'system', 'content' => $system]];
        foreach ($turns as $turn) {
            $messages[] = ['role' => $turn['role'], 'content' => $turn['text']];
        }

        try {
            $request = Http::acceptJson()
                ->connectTimeout(max(1, (int) config('services.question_ai.connect_timeout', 10)))
                ->timeout(max(1, min(30, (int) config('services.question_ai.timeout', 60))))
                ->withoutRedirecting();
            $apiKey = trim((string) config('services.question_ai.api_key'));
            if ($apiKey !== '') {
                $request = $request->withToken($apiKey);
            }

            $response = $request->post($baseUrl . '/chat/completions', [
                'model' => $model,
                'messages' => $messages,
                'temperature' => 0.3,
                'max_tokens' => $maxTokens,
                'stream' => false,
            ]);

            if ($response->successful()) {
                $text = $this->readCompletionText($response->body());
                if ($text !== null) {
                    return $text;
                }
            }

            Log::warning("AiChatClient: 9Router trả về HTTP {$response->status()}, chuyển sang Gemini.");
        } catch (\Throwable $e) {
            Log::warning('AiChatClient: không gọi được 9Router (' . $e::class . '), chuyển sang Gemini.');
        }

        return null;
    }

    /**
     * Đọc nội dung trả lời từ 9Router. Có nơi trả JSON thường, có nơi trả luồng SSE ("data: {...}") dù không yêu cầu stream.
     */
    private function readCompletionText(string $body): ?string
    {
        $json = json_decode($body, true);
        if (is_array($json)) {
            $text = trim((string) data_get($json, 'choices.0.message.content', ''));

            return $text !== '' ? $text : null;
        }

        $text = '';
        foreach (preg_split('/\r?\n/', $body) as $line) {
            if (! str_starts_with($line, 'data:')) {
                continue;
            }
            $payload = trim(substr($line, 5));
            if ($payload === '' || $payload === '[DONE]') {
                continue;
            }
            $chunk = json_decode($payload, true);
            $text .= (string) data_get($chunk, 'choices.0.delta.content', '');
        }

        $text = trim($text);

        return $text !== '' ? $text : null;
    }

    private function askGemini(string $system, array $turns, int $maxTokens): ?string
    {
        $keys = array_values(array_filter(array_map('trim', explode(',', (string) config('services.gemini.api_keys')))));
        $contents = array_map(fn (array $turn) => [
            'role' => $turn['role'] === 'user' ? 'user' : 'model',
            'parts' => [['text' => $turn['text']]],
        ], $turns);

        foreach ($keys as $index => $key) {
            try {
                $response = Http::connectTimeout(8)->timeout(30)
                    ->withHeaders(['x-goog-api-key' => $key])
                    ->post(self::GEMINI_BASE_URL . '/models/' . self::GEMINI_MODEL . ':generateContent', [
                        'systemInstruction' => ['parts' => [['text' => $system]]],
                        'contents' => $contents,
                        'generationConfig' => ['temperature' => 0.3, 'maxOutputTokens' => $maxTokens],
                    ]);

                if ($response->successful()) {
                    $text = trim((string) data_get($response->json(), 'candidates.0.content.parts.0.text', ''));
                    if ($text !== '') {
                        return $text;
                    }
                }

                Log::warning("AiChatClient: khóa Gemini #{$index} trả về HTTP {$response->status()}.");
            } catch (\Throwable $e) {
                Log::warning('AiChatClient: không gọi được Gemini (' . $e::class . ').');
            }
        }

        return null;
    }
}
