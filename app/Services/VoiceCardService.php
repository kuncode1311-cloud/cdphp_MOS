<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Str;

/**
 * Thẻ thống kê hiện trên màn hình trò chuyện (ô số liệu + biểu đồ cột), thay cho việc đọc cả loạt con số thành một đoạn dài.
 * Mọi con số lấy thẳng từ cơ sở dữ liệu (cùng nguồn với Trợ lý AI), không để AI tự chép lại nên không bị sai.
 */
class VoiceCardService
{
    /** Số bài gần đây vẽ trên biểu đồ */
    private const MAX_BARS = 8;

    public function __construct(private AiAssistantService $assistant, private VoiceQuizService $quiz)
    {
    }

    /**
     * Thẻ thống kê của học sinh. Trả về null nếu không phải học sinh hoặc chưa có số liệu nào để hiện.
     *
     * @return array<string, mixed>|null
     */
    public function statsCard(User $user): ?array
    {
        if (! $user->isStudent()) {
            return null;
        }

        $data = $this->assistant->dataFor($user);
        $stats = $data['thong_ke'] ?? [];
        $recent = $data['ket_qua_gan_day'] ?? [];
        $mistakes = $this->quiz->mistakeSummary($user);
        $done = (int) ($stats['so_bai_da_lam'] ?? 0);

        $tiles = [];
        if ($done > 0) {
            $tiles[] = ['icon' => '📝', 'label' => 'Bài đã làm', 'value' => $done];
            $tiles[] = ['icon' => '✅', 'label' => 'Bài đạt', 'value' => (int) ($stats['so_bai_dat'] ?? 0)];
            $tiles[] = ['icon' => '📊', 'label' => 'Điểm trung bình', 'value' => (int) ($stats['diem_trung_binh'] ?? 0)];
            $tiles[] = ['icon' => '🏆', 'label' => 'Điểm cao nhất', 'value' => (int) ($stats['bai_tot_nhat']['diem'] ?? 0)];
            $tiles[] = ['icon' => '🌱', 'label' => 'Điểm thấp nhất', 'value' => (int) ($stats['bai_thap_nhat']['diem'] ?? 0)];
        }
        $tiles[] = ['icon' => '⭐', 'label' => 'Sao thưởng', 'value' => (int) ($user->reward_stars ?? 0)];
        $tiles[] = ['icon' => '🔁', 'label' => 'Câu sai cần ôn', 'value' => (int) $mistakes['tong_so_cau_chua_khac_phuc']];

        // Biểu đồ cột: các bài gần đây theo thứ tự cũ → mới
        $bars = collect($recent)->take(self::MAX_BARS)->reverse()->values()->map(fn (array $row) => [
            'label' => $this->shortDate((string) ($row['ngay'] ?? '')),
            'value' => (int) ($row['diem'] ?? 0),
            'pass' => (bool) ($row['dat'] ?? false),
            'name' => (string) ($row['bai'] ?? ''),
        ])->all();

        $weak = array_slice($mistakes['theo_chu_de'], 0, 4);

        return [
            'type' => 'stats',
            'title' => 'Kết quả học tập của em',
            'tiles' => $tiles,
            'bars_title' => 'Điểm các bài làm gần đây (thang ' . (int) config('learning.max_score', 1000) . ')',
            'bars' => $bars,
            'max' => (int) config('learning.max_score', 1000),
            'pass' => (int) config('learning.pass_score', 700),
            'weak_title' => 'Chủ đề còn câu sai cần ôn',
            'weak' => array_map(fn (array $w) => ['label' => $w['chu_de'], 'value' => (int) $w['so_cau']], $weak),
        ];
    }

    /** "27/09/2026 15:54" → "27/09" */
    private function shortDate(string $value): string
    {
        return preg_match('~^(\d{1,2}/\d{1,2})~', $value, $m) ? $m[1] : '';
    }
}
