<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\VoiceAssistantService;
use App\Services\VoiceQuizService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * Trò chuyện bằng giọng nói với Trợ lý AI: chỉ thành viên có gói Trợ lý AI còn hạn.
 * Máy chủ không lưu âm thanh; âm thanh gửi lên (nếu có) chỉ dùng để chép thành chữ rồi bỏ.
 * Làm câu hỏi trong cuộc trò chuyện chỉ để luyện tập: không cộng sao, không ghi vào kết quả.
 */
class VoiceChatController extends Controller
{
    /** Nhận câu nói đã chuyển thành chữ, trả lời bằng chữ (trình duyệt sẽ đọc to). Có thể kèm câu hỏi luyện tập. */
    public function reply(Request $request, VoiceAssistantService $voice, VoiceQuizService $quiz): JsonResponse
    {
        $user = $request->user();
        if ($denied = $this->denyIfNoAi($user)) {
            return $denied;
        }

        $data = $request->validate([
            'text' => ['required', 'string', 'max:' . (int) config('voice.max_text_length', 500)],
            'history' => ['nullable', 'array', 'max:20'],
            'history.*.role' => ['required_with:history', 'in:user,assistant'],
            'history.*.text' => ['required_with:history', 'string', 'max:700'],
        ]);

        if ($limited = $this->denyIfOverLimit($user)) {
            return $limited;
        }

        $result = $voice->reply($user, $data['history'] ?? [], trim($data['text']));
        $acted = ! empty($result['choice']) || ! empty($result['control']);
        if ($result === null || ($result['text'] === '' && ! $acted)) {
            return response()->json(['ok' => false, 'message' => 'Cô đang bận một chút. Em nói lại giúp cô nhé!'], 503);
        }

        $this->countTurn($user->id);

        $payload = [
            'ok' => true, 'text' => $result['text'], 'display' => $result['display'] ?? $result['text'],
            'quiz' => null, 'graded' => null, 'control' => $result['control'] ?? null,
            'action' => $result['action'] ?? null, 'card' => $result['card'] ?? null,
        ];

        // AI hiểu em đã chọn đáp án: máy chủ tự chấm theo database (AI không biết đáp án nên không thể chấm sai)
        if (! empty($result['choice'])) {
            $graded = $quiz->answer($user, trim($data['text']), null, $result['choice']);
            if ($graded['status'] === 'graded') {
                $payload['graded'] = $graded;
                $payload['text'] = $graded['spoken'];
                $payload['display'] = $graded['feedback_display'];
            } else {
                $payload['text'] = $payload['display'] = (string) $graded['spoken'];
            }
        }

        // AI muốn cho em làm câu hỏi: lấy câu hỏi thật từ ngân hàng đề
        if ($result['quiz_hint'] !== null) {
            $payload['quiz'] = $quiz->start($user, $result['quiz_hint']);
            if ($payload['quiz'] !== null && $payload['quiz']['matched'] === false) {
                $note = 'Cô chưa có câu nào nói đúng về «' . $payload['quiz']['keywords'] . '», nên cô ra một câu cùng chủ đề nhé.';
                $payload['text'] = trim($payload['text'] . ' ' . $note);
                $payload['display'] = trim($payload['display'] . "\n\n" . $note);
            }
            if ($payload['quiz'] === null) {
                $extra = ' Nhưng cô chưa tìm được câu hỏi phù hợp cho phần này. Em thử chủ đề khác nhé!';
                $payload['text'] = trim($payload['text'] . $extra);
                $payload['display'] = trim($payload['display'] . "\n\n" . trim($extra));
            }
        }

        return response()->json($payload);
    }

    /** Ra một câu hỏi luyện tập (làm tiếp, đổi câu). */
    public function quizStart(Request $request, VoiceQuizService $quiz): JsonResponse
    {
        $user = $request->user();
        if ($denied = $this->denyIfNoAi($user)) {
            return $denied;
        }

        $data = $request->validate(['hint' => ['nullable', 'string', 'max:300']]);

        $payload = $quiz->start($user, (string) ($data['hint'] ?? ''));
        if ($payload === null) {
            return response()->json(['ok' => false, 'message' => 'Cô chưa tìm được câu hỏi phù hợp. Em thử chủ đề khác nhé!'], 404);
        }

        return response()->json(['ok' => true, 'quiz' => $payload]);
    }

    /**
     * Em dừng/thoát phần làm câu hỏi: xóa câu đang chờ để cô không còn nhắc "quay lại câu hỏi" khi thẻ đã tắt trên màn hình.
     */
    public function quizCancel(Request $request, VoiceQuizService $quiz): JsonResponse
    {
        $user = $request->user();
        if ($denied = $this->denyIfNoAi($user)) {
            return $denied;
        }

        $quiz->clear($user);

        return response()->json(['ok' => true]);
    }

    /** Em nói đáp án: máy chủ hiểu, chấm theo cơ sở dữ liệu và giải thích. */
    public function quizAnswer(Request $request, VoiceQuizService $quiz): JsonResponse
    {
        $user = $request->user();
        if ($denied = $this->denyIfNoAi($user)) {
            return $denied;
        }

        $data = $request->validate([
            'text' => ['required', 'string', 'max:' . (int) config('voice.max_text_length', 500)],
            // Kết quả em làm trên màn hình (dạng chọn trong ô / ghép nối): mỗi dòng một số
            'answers' => ['nullable', 'array', 'max:12'],
            'answers.*' => ['integer', 'min:0', 'max:50'],
            // Chữ cái em bấm chọn (dạng chọn đáp án)
            'keys' => ['nullable', 'array', 'max:6'],
            'keys.*' => ['string', 'regex:/^[A-Fa-f]$/'],
        ]);

        if ($limited = $this->denyIfOverLimit($user)) {
            return $limited;
        }

        $result = $quiz->answer($user, trim($data['text']), $data['answers'] ?? null, $data['keys'] ?? null);
        if ($result['status'] === 'graded') {
            $this->countTurn($user->id);
        }

        return response()->json(['ok' => true] + $result);
    }

    /** Dự phòng cho trình duyệt không tự nhận dạng được giọng nói: nhận âm thanh WAV ngắn, trả về chữ. */
    public function transcribe(Request $request, VoiceAssistantService $voice): JsonResponse
    {
        $user = $request->user();
        if ($denied = $this->denyIfNoAi($user)) {
            return $denied;
        }

        $request->validate([
            'audio' => ['required', 'file', 'max:' . (int) config('voice.max_audio_kb', 2048)],
        ]);

        $binary = (string) file_get_contents($request->file('audio')->getRealPath());
        // Chỉ nhận đúng tệp WAV (RIFF....WAVE), không nhận loại khác
        if (strlen($binary) < 44 || substr($binary, 0, 4) !== 'RIFF' || substr($binary, 8, 4) !== 'WAVE') {
            return response()->json(['ok' => false, 'message' => 'Âm thanh không hợp lệ.'], 422);
        }

        $text = $voice->transcribe($binary);

        return response()->json(['ok' => $text !== null, 'text' => $text ?? '']);
    }

    private function denyIfNoAi(User $user): ?JsonResponse
    {
        return $user->hasAiAssistant()
            ? null
            : response()->json(['ok' => false, 'message' => 'Em cần có gói Trợ lý AI còn hạn để trò chuyện bằng giọng nói nhé.'], 403);
    }

    private function denyIfOverLimit(User $user): ?JsonResponse
    {
        return $this->turnsToday($user->id) >= (int) config('voice.daily_turns', 150)
            ? response()->json(['ok' => false, 'message' => 'Hôm nay em đã trò chuyện bằng giọng nói rất nhiều rồi. Em nghỉ ngơi và quay lại vào ngày mai nhé!'], 429)
            : null;
    }

    private function key(int $userId): string
    {
        return 'voice_turns:' . $userId . ':' . now()->format('Ymd');
    }

    private function turnsToday(int $userId): int
    {
        return (int) Cache::get($this->key($userId), 0);
    }

    private function countTurn(int $userId): void
    {
        $key = $this->key($userId);
        Cache::add($key, 0, now()->endOfDay());
        Cache::increment($key);
    }
}
