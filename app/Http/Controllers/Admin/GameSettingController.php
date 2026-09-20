<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GameSetting;
use App\Models\GameTransaction;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Controller Quản trị Cấu hình Khu Trò Chơi (Game Settings)
 *
 * Chức năng:
 * - Điều chỉnh tỷ lệ quy đổi Sao ➔ Giờ chơi (Gói 1, Gói 2).
 * - Bật/Tắt mini-game toàn trường.
 * - Giới hạn thời gian chơi an toàn mỗi ngày cho học sinh.
 * - Xem báo cáo thống kê sử dụng và nhật ký giao dịch.
 * - Hỗ trợ Thầy/Cô thưởng nóng Sao cho học sinh.
 */
class GameSettingController extends Controller
{
    /**
     * Màn hình quản trị cấu hình Game (Chỉ dành riêng cho Admin)
     */
    public function index(Request $request): View
    {
        // Chặn quyền Giáo viên: Chỉ Quản trị viên Tổng (Admin) mới có quyền cài đặt Khu trò chơi
        abort_unless($request->user()?->isAdmin(), 403, 'Chức năng Cài đặt Khu Trò Chơi chỉ dành riêng cho Quản trị viên (Admin).');

        $settings = GameSetting::pluck('value', 'key')->all();

        // Thống kê toàn trường
        $totalStarsBalance = User::where('role', 'student')->sum('reward_stars');
        $totalSecondsPlayed = abs(GameTransaction::where('type', 'play')->sum('time_seconds_change'));
        $totalMinutesPlayed = (int) round($totalSecondsPlayed / 60);
        $totalExchanges = GameTransaction::where('type', 'exchange')->count();
        $totalStudentsWithTime = User::where('role', 'student')->where('game_time_seconds', '>', 0)->count();

        // Danh sách giao dịch gần nhất
        $recentTransactions = GameTransaction::with('user.classroom')
            ->latest()
            ->take(30)
            ->get();

        // Danh sách học sinh phục vụ modal thưởng nóng
        $students = User::where('role', 'student')
            ->with('classroom')
            ->orderBy('name')
            ->get();

        $leaderboardPeriod = GameSetting::getLeaderboardResetPeriod();
        $leaderboardStart = GameSetting::getLeaderboardResetStart();
        $leaderboardNext = GameSetting::getLeaderboardNextReset();
        $leaderboardLastReset = GameSetting::get('leaderboard_last_reset_at');

        return view('admin.games.settings', compact(
            'settings',
            'totalStarsBalance',
            'totalMinutesPlayed',
            'totalExchanges',
            'totalStudentsWithTime',
            'recentTransactions',
            'students',
            'leaderboardPeriod',
            'leaderboardStart',
            'leaderboardNext',
            'leaderboardLastReset'
        ));
    }

    /**
     * Lưu cập nhật cấu hình Game (Chỉ dành riêng cho Admin)
     */
    public function update(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403, 'Chức năng Cài đặt Khu Trò Chơi chỉ dành riêng cho Quản trị viên (Admin).');

        $validated = $request->validate([
            'game_enabled' => 'nullable|boolean',
            'pkg1_stars' => 'required|integer|min:10|max:10000',
            'pkg1_minutes' => 'required|integer|min:1|max:60',
            'pkg1_title' => 'required|string|max:100',
            'pkg2_stars' => 'required|integer|min:10|max:20000',
            'pkg2_minutes' => 'required|integer|min:1|max:120',
            'pkg2_title' => 'required|string|max:100',
            'max_daily_minutes' => 'required|integer|min:5|max:180',
        ]);

        GameSetting::set('game_enabled', $request->boolean('game_enabled') ? '1' : '0');
        GameSetting::set('pkg1_stars', $validated['pkg1_stars']);
        GameSetting::set('pkg1_minutes', $validated['pkg1_minutes']);
        GameSetting::set('pkg1_title', $validated['pkg1_title']);
        GameSetting::set('pkg2_stars', $validated['pkg2_stars']);
        GameSetting::set('pkg2_minutes', $validated['pkg2_minutes']);
        GameSetting::set('pkg2_title', $validated['pkg2_title']);
        GameSetting::set('max_daily_minutes', $validated['max_daily_minutes']);

        return back()->with('ok', 'Đã lưu cấu hình Khu trò chơi thành công!');
    }

    /**
     * Admin thưởng nóng / điều chỉnh Sao cho học sinh
     */
    public function adjustStars(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403, 'Chức năng Điều chỉnh Sao chỉ dành riêng cho Quản trị viên (Admin).');

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|integer|not_in:0',
            'reason' => 'required|string|max:255',
        ]);

        $student = User::findOrFail($validated['user_id']);
        $amount = (int) $validated['amount'];

        if ($amount > 0) {
            $student->addRewardStars($amount, "Quản trị viên thưởng: " . $validated['reason']);
        } else {
            $deduct = abs($amount);
            $actual = min($deduct, $student->reward_stars);
            $student->decrement('reward_stars', $actual);
            $student->gameTransactions()->create([
                'type' => 'admin_adjust',
                'stars_change' => -$actual,
                'time_seconds_change' => 0,
                'description' => "Quản trị viên trừ điểm: " . $validated['reason'],
            ]);
        }

        return back()->with('ok', "Đã điều chỉnh Sao cho học sinh '{$student->name}' thành công!");
    }

    /**
     * Cập nhật chu kỳ reset Bảng xếp hạng thi đua
     */
    public function updateLeaderboardSettings(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403, 'Chức năng này chỉ dành riêng cho Quản trị viên (Admin).');

        $validated = $request->validate([
            'leaderboard_reset_period' => 'required|in:weekly,monthly,manual',
        ]);

        GameSetting::set('leaderboard_reset_period', $validated['leaderboard_reset_period'], 'Chu kỳ reset Bảng xếp hạng thi đua');

        return back()->with('ok', 'Đã cập nhật chu kỳ reset Bảng xếp hạng thành công!');
    }

    /**
     * Bắt đầu vòng thi đua mới ngay lập tức (Reset Bảng xếp hạng)
     */
    public function resetLeaderboard(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403, 'Chức năng này chỉ dành riêng cho Quản trị viên (Admin).');

        $tz = config('learning.display_timezone', 'Asia/Ho_Chi_Minh');
        $now = now($tz);

        GameSetting::set('leaderboard_last_reset_at', $now->toDateTimeString(), 'Thời điểm bắt đầu vòng thi đua mới');

        return back()->with('ok', 'Đã bắt đầu vòng thi đua mới thành công! Bảng xếp hạng sẽ tính điểm bài làm từ thời điểm này (' . $now->format('H:i d/m/Y') . ').');
    }
}
