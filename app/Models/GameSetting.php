<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Model Quản lý Cài đặt Cấu hình Khu trò chơi (GameSetting)
 *
 * Lưu trữ các thông số vận hành của hệ thống Gamification:
 * - `game_enabled`: Bật/Tắt mini-game (1/0)
 * - `pkg1_stars`, `pkg1_minutes`, `pkg1_title`: Thông số Gói 1
 * - `pkg2_stars`, `pkg2_minutes`, `pkg2_title`: Thông số Gói 2
 * - `max_daily_minutes`: Giới hạn thời gian chơi tối đa/ngày
 */
class GameSetting extends Model
{
    protected $fillable = ['key', 'value', 'description'];

    /** Cache key prefix */
    private const CACHE_PREFIX = 'game_setting_';

    /**
     * Lấy giá trị cài đặt theo key (tự động đọc từ cache 1 ngày)
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::remember(self::CACHE_PREFIX . $key, 86400, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
    }

    /**
     * Cập nhật hoặc tạo mới một cài đặt và xóa cache
     */
    public static function set(string $key, mixed $value, ?string $description = null): static
    {
        $setting = static::updateOrCreate(
            ['key' => $key],
            array_filter(['value' => (string) $value, 'description' => $description], fn ($v) => $v !== null)
        );

        Cache::forget(self::CACHE_PREFIX . $key);

        return $setting;
    }

    /**
     * Lấy toàn bộ danh sách cài đặt dưới dạng mảng key => value
     */
    public static function getAllSettings(): array
    {
        return static::pluck('value', 'key')->all();
    }

    /**
     * Lấy chu kỳ reset bảng xếp hạng: 'weekly' (hàng tuần), 'monthly' (hàng tháng), 'manual' (thủ công)
     */
    public static function getLeaderboardResetPeriod(): string
    {
        return (string) static::get('leaderboard_reset_period', 'weekly');
    }

    /**
     * Lấy chế độ tính điểm xếp hạng:
     * - 'passed_only' (mặc định): Chỉ tính điểm các bài thi ĐẠT CHUẨN IC3 (score >= min_pass_score)
     * - 'all_attempts': Tính điểm tất cả các bài thi (không phân biệt đạt hay chưa đạt)
     */
    public static function getLeaderboardScoreMode(): string
    {
        return (string) static::get('leaderboard_score_mode', 'passed_only');
    }

    /**
     * Lấy điểm chuẩn tối thiểu để tính điểm xếp hạng (chuẩn IC3 là 700 điểm)
     */
    public static function getLeaderboardMinPassScore(): int
    {
        return (int) static::get('leaderboard_min_pass_score', 700);
    }

    /**
     * Lấy mốc thời gian bắt đầu của đợt thi đua hiện tại (UTC để truy vấn Database)
     */
    public static function getLeaderboardResetStart(): \Carbon\Carbon
    {
        $tz = config('learning.display_timezone', 'Asia/Ho_Chi_Minh');
        $now = now($tz);
        $period = static::getLeaderboardResetPeriod();
        $lastResetStr = static::get('leaderboard_last_reset_at');
        $lastReset = $lastResetStr ? \Carbon\Carbon::parse($lastResetStr, $tz) : null;

        if ($period === 'monthly') {
            $defaultStart = $now->copy()->startOfMonth();
        } elseif ($period === 'manual') {
            $defaultStart = $lastReset ?? $now->copy()->startOfWeek();
        } else {
            $defaultStart = $now->copy()->startOfWeek();
        }

        // Nếu có mốc reset thủ công gần hơn mốc mặc định thì ưu tiên mốc thủ công
        if ($lastReset && $lastReset->greaterThan($defaultStart)) {
            $start = $lastReset;
        } else {
            $start = $defaultStart;
        }

        return $start->setTimezone('UTC');
    }

    /**
     * Lấy thời điểm reset tiếp theo (để hiển thị đồng hồ đếm ngược kết thúc vòng đua)
     */
    public static function getLeaderboardNextReset(): ?\Carbon\Carbon
    {
        $tz = config('learning.display_timezone', 'Asia/Ho_Chi_Minh');
        $now = now($tz);
        $period = static::getLeaderboardResetPeriod();

        if ($period === 'monthly') {
            return $now->copy()->addMonth()->startOfMonth();
        } elseif ($period === 'manual') {
            return null;
        } else {
            return $now->copy()->addWeek()->startOfWeek();
        }
    }
}
