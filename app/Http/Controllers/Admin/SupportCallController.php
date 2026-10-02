<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportCall;
use App\Models\SupportMessage;
use App\Services\StringeeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Gọi điện cho khách từ trang Live Chat bằng Stringee, có nhật ký và ghi âm cuộc gọi.
 * Chỉ Quản trị viên tổng được gọi; webhook Stringee nằm ở các hàm answer() và event().
 */
class SupportCallController extends Controller
{
    public function __construct(private StringeeService $stringee)
    {
    }

    /**
     * Cấp token đăng nhập Web SDK cho quản trị viên đang đăng nhập.
     */
    public function token(Request $request): JsonResponse
    {
        abort_unless($request->user()->isAdmin(), 403);

        if (! $this->stringee->isConfigured()) {
            return response()->json([
                'configured' => false,
                'message' => 'Chưa cấu hình Stringee. Hãy điền STRINGEE_KEY_SID, STRINGEE_KEY_SECRET và STRINGEE_FROM_NUMBER trong file .env.',
            ]);
        }

        return response()->json([
            'configured' => true,
            'token' => $this->stringee->makeToken('admin-' . $request->user()->id),
            'from_number' => $this->stringee->fromNumber(),
        ]);
    }

    /**
     * Tạo bản ghi cuộc gọi trước khi quay số để có mã đối soát.
     */
    public function start(Request $request): JsonResponse
    {
        abort_unless($request->user()->isAdmin(), 403);

        $data = $request->validate([
            'support_message_id' => ['nullable', 'integer', 'exists:support_messages,id'],
            'phone' => ['required', 'string', 'max:30'],
        ]);

        $to = $this->stringee->normalizePhone($data['phone']);
        if (! $to) {
            return response()->json(['message' => 'Số điện thoại chưa đúng định dạng. Vui lòng nhập số di động Việt Nam, ví dụ 0901234567.'], 422);
        }

        $call = SupportCall::create([
            'support_message_id' => $data['support_message_id'] ?? null,
            'admin_id' => $request->user()->id,
            'from_number' => $this->stringee->fromNumber(),
            'to_number' => $to,
            'status' => 'calling',
            'started_at' => now(),
        ]);

        return response()->json(['call' => $call->toPanelArray(), 'to_number' => $to]);
    }

    /**
     * Trình duyệt báo mã cuộc gọi Stringee, trạng thái và ghi chú.
     */
    public function update(Request $request, SupportCall $call): JsonResponse
    {
        abort_unless($request->user()->isAdmin(), 403);

        $data = $request->validate([
            'stringee_call_id' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', 'in:calling,answered,ended,missed,failed'],
            'end_reason' => ['nullable', 'string', 'max:120'],
            'note' => ['nullable', 'string', 'max:2000'],
        ]);

        $updates = array_filter($data, fn ($v) => $v !== null);
        if (($updates['status'] ?? null) === 'answered' && ! $call->answered_at) {
            $updates['answered_at'] = now();
        }
        if (in_array($updates['status'] ?? null, ['ended', 'missed', 'failed'], true)) {
            $updates['ended_at'] = now();
            if ($call->answered_at) {
                $updates['duration'] = $call->answered_at->diffInSeconds(now());
                $updates['status'] = 'ended';
            } elseif ($updates['status'] === 'ended') {
                $updates['status'] = 'missed';
            }
        }
        $call->update($updates);

        return response()->json(['call' => $call->fresh()->toPanelArray()]);
    }

    /**
     * Lịch sử cuộc gọi của một đoạn chat.
     */
    public function index(Request $request, SupportMessage $supportMessage): JsonResponse
    {
        abort_unless($request->user()->isAdmin(), 403);

        $calls = $supportMessage->calls()->with('admin:id,name')->latest('id')->limit(20)->get();
        // Ghi âm có thể về chậm vài chục giây sau khi cúp máy nên thử tải lại lúc xem lịch sử.
        foreach ($calls as $call) {
            if ($call->ended_at && $call->answered_at && ! $call->recording_path) {
                $this->stringee->downloadRecording($call);
            }
        }

        return response()->json(['calls' => $calls->map->toPanelArray()->values()]);
    }

    /**
     * Phát lại ghi âm (chỉ Quản trị viên tổng, file không công khai).
     */
    public function recording(Request $request, SupportCall $call): StreamedResponse
    {
        abort_unless($request->user()->isAdmin(), 403);
        abort_unless($call->recording_path && Storage::disk('local')->exists($call->recording_path), 404);

        return Storage::disk('local')->response($call->recording_path, "cuoc-goi-{$call->id}.mp3", [
            'Content-Type' => 'audio/mpeg',
        ]);
    }

    /**
     * Webhook Answer URL: Stringee hỏi cần xử lý cuộc gọi thế nào, trả về kịch bản SCCO.
     */
    public function answer(Request $request): JsonResponse
    {
        abort_unless($this->stringee->verifySignature('answer', $request->query('sig')), 403);

        $to = $this->stringee->normalizePhone($request->input('to'));
        if (! $to) {
            return response()->json([['action' => 'hangup']]);
        }

        // Ưu tiên customData do trình duyệt gửi lên, nếu thiếu thì lấy cuộc gọi đang quay số gần nhất tới số này.
        $call = SupportCall::find((int) $request->input('customData'))
            ?? SupportCall::where('to_number', $to)->where('status', 'calling')->latest('id')->first();
        if ($call && $request->filled('callId')) {
            $call->update(['stringee_call_id' => $request->input('callId')]);
        }

        return response()->json($this->stringee->buildScco($to, (string) ($call?->id ?? '')));
    }

    /**
     * Webhook Event URL: nhận báo cáo ghi âm và trạng thái cuộc gọi từ Stringee.
     */
    public function event(Request $request): JsonResponse
    {
        abort_unless($this->stringee->verifySignature('event', $request->query('sig')), 403);

        $callId = $request->input('callId') ?? $request->input('call_id');
        $call = $callId ? SupportCall::where('stringee_call_id', $callId)->latest('id')->first() : null;
        if ($call) {
            $recordUrl = $request->input('recordUrl') ?? $request->input('recording_url') ?? $request->input('url');
            if (is_string($recordUrl) && preg_match('~^https://([a-z0-9-]+\.)*stringee\.com/~i', $recordUrl)) {
                $call->update(['recording_url' => $recordUrl]);
            }
            if ($call->ended_at && ! $call->recording_path) {
                $this->stringee->downloadRecording($call);
            }
        }

        return response()->json(['ok' => true]);
    }
}
