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
}
