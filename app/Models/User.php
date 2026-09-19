<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Model Tài Khoản Người Dùng (User)
 * 
 * Đại diện cho Bảng Cơ Sở Dữ Liệu: `users`
 * 
 * Danh sách các cột trong CSDL:
 * - `id` (int): Khóa chính, mã định danh tài khoản.
 * - `name` (string): Họ và tên của người dùng (Ví dụ: "An Nhiên").
 * - `email` (string): Địa chỉ email đăng nhập (Ví dụ: "hs001@student.ic3.local").
 * - `password` (string): Mật khẩu đã được mã hóa an toàn (Hashed bcrypt).
 * - `role` (string): Vai trò tài khoản ('admin' = Quản trị viên, 'teacher' = Giáo viên, 'student' = Học sinh).
 * - `student_code` (string): Mã số học sinh dùng để đăng nhập nhanh (Ví dụ: "HS001").
 * - `classroom_id` (int): Khóa ngoại liên kết tới bảng `classrooms` (Lớp học của học sinh).
 * - `remember_token` (string): Mã ghi nhớ phiên đăng nhập.
 * - `created_at` / `updated_at`: Thời điểm tạo và cập nhật tài khoản.
 */
// Fillable cho phép gán hàng loạt; Hidden ẩn mật khẩu/token khi xuất model thành JSON.
#[Fillable(['name', 'email', 'password', 'role', 'student_code', 'classroom_id', 'created_by', 'max_students', 'expires_at', 'status', 'reward_stars', 'game_time_seconds'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Mối quan hệ: Học sinh thuộc về 1 Nhóm/Lớp học (Classroom - tùy chọn)
     */
    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    /**
     * Mối quan hệ: Giáo viên sở hữu/tạo ra Học sinh này
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Mối quan hệ: Danh sách Học sinh do Giáo viên này quản lý (Teacher ➔ Students)
     */
    public function students(): HasMany
    {
        return $this->hasMany(User::class, 'created_by');
    }

    /**
     * Mối quan hệ: Các Khối lớp mà Giáo viên được Admin cấp quyền sử dụng
     */
    public function teacherLevels(): BelongsToMany
    {
        return $this->belongsToMany(Level::class, 'teacher_level', 'teacher_id', 'level_id')->withTimestamps();
    }

    /**
     * Mối quan hệ: 1 Học sinh có nhiều Lượt làm bài thi (TestAttempts)
     */
    public function attempts(): HasMany
    {
        return $this->hasMany(TestAttempt::class);
    }

    /**
     * Mối quan hệ: Lịch sử đơn thuê gói dịch vụ của người dùng / Giáo viên
     */
    public function packageOrders(): HasMany
    {
        return $this->hasMany(PackageOrder::class)->latest('id');
    }

    /**
     * Lấy đơn thuê gói gần nhất
     */
    public function latestPackageOrder(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(PackageOrder::class)->latestOfMany();
    }

    /**
     * Kiểm tra xem tài khoản này có phải là Quản trị viên (Admin) không
     */
    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin->value;
    }

    /**
     * Kiểm tra xem tài khoản này có phải là Giáo viên (Teacher) không
     */
    public function isTeacher(): bool
    {
        return $this->role === UserRole::Teacher->value;
    }

    /**
     * Kiểm tra xem tài khoản này có phải là Học sinh (Student) không
     */
    public function isStudent(): bool
    {
        return $this->role === UserRole::Student->value;
    }

    /**
     * Kiểm tra quyền truy cập vào Khu vực Quản trị & Điều hành (Admin & Teacher)
     */
    public function canAccessAdmin(): bool
    {
        return in_array($this->role, [UserRole::Admin->value, UserRole::Teacher->value], true);
    }

    /**
     * Kiểm tra xem gói thuê bao của Giáo viên có đang hoạt động và còn hạn hay không
     */
    public function isSubscriptionActive(): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        if (($this->status ?? 'active') !== 'active') {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast() && ! $this->expires_at->isToday()) {
            return false;
        }

        return true;
    }

    /**
     * Kiểm tra xem Giáo viên còn slot tạo thêm học sinh hay không
     */
    public function hasAvailableStudentSlots(): bool
    {
        if ($this->isAdmin() || ! $this->max_students) {
            return true;
        }

        return $this->students()->count() < $this->max_students;
    }

    /**
     * Số lượng học sinh còn lại mà Giáo viên có thể tạo thêm
     */
    public function remainingStudentSlots(): int
    {
        if ($this->isAdmin() || ! $this->max_students) {
            return 999999;
        }

        return max(0, $this->max_students - $this->students()->count());
    }

    /**
     * Mối quan hệ: Giáo viên phụ trách nhiều Nhóm/Lớp học (Classrooms)
     */
    public function teachingClassrooms(): HasMany
    {
        return $this->hasMany(Classroom::class, 'teacher_id');
    }

    /**
     * Mối quan hệ: Các Khối lớp (Levels) mà Học sinh được cấp quyền học
     */
    public function accessibleLevels(): BelongsToMany
    {
        return $this->belongsToMany(Level::class, 'level_user')->withTimestamps();
    }

    /**
     * Kiểm tra nhanh xem Người dùng (Teacher/Student) có quyền truy cập Khối lớp này không
     */
    public function canAccessLevel(int|Level $level): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        $levelId = is_int($level) ? $level : $level->id;

        // Nếu là Giáo viên: Kiểm tra các Khối được Admin cấp quyền qua bảng teacher_level
        if ($this->isTeacher()) {
            // Quan hệ đã nạp thì dùng lại, tránh truy vấn database thêm lần nữa.
            if ($this->relationLoaded('teacherLevels')) {
                return $this->teacherLevels->contains('id', $levelId);
            }

            return $this->teacherLevels()->where('levels.id', $levelId)->exists();
        }

        // Nếu là Học sinh: Kiểm tra các Khối được Giáo viên cấp quyền qua bảng level_user
        if ($this->relationLoaded('accessibleLevels')) {
            return $this->accessibleLevels->contains('id', $levelId);
        }

        return $this->accessibleLevels()->where('levels.id', $levelId)->exists();
    }

    /**
     * Mối quan hệ: Lịch sử giao dịch Điểm thưởng và Giờ chơi của học sinh
     */
    public function gameTransactions(): HasMany
    {
        return $this->hasMany(GameTransaction::class)->latest();
    }

    /**
     * Tích lũy Sao thưởng khi hoàn thành bài thi
     */
    public function addRewardStars(int $amount, string $reason = 'Thưởng hoàn thành bài luyện'): void
    {
        if ($amount <= 0) {
            return;
        }

        $this->increment('reward_stars', $amount);

        $this->gameTransactions()->create([
            'type' => 'earn',
            'stars_change' => $amount,
            'time_seconds_change' => 0,
            'description' => $reason,
        ]);
    }

    /**
     * Đổi gói giờ chơi mini-game
     */
    public function exchangeGamePackage(int $packageNum): array
    {
        $pkgStars = (int) GameSetting::get("pkg{$packageNum}_stars", $packageNum === 2 ? 1000 : 500);
        $pkgMinutes = (int) GameSetting::get("pkg{$packageNum}_minutes", $packageNum === 2 ? 7 : 3);
        $pkgTitle = (string) GameSetting::get("pkg{$packageNum}_title", "Gói {$packageNum}");
        $addSeconds = $pkgMinutes * 60;

        if ($this->reward_stars < $pkgStars) {
            return [
                'success' => false,
                'message' => "Bé cần {$pkgStars} Sao để đổi {$pkgTitle}. Hiện bé đang có {$this->reward_stars} Sao.",
            ];
        }

        $this->decrement('reward_stars', $pkgStars);
        $this->increment('game_time_seconds', $addSeconds);

        $this->gameTransactions()->create([
            'type' => 'exchange',
            'stars_change' => -$pkgStars,
            'time_seconds_change' => $addSeconds,
            'description' => "Đổi {$pkgTitle} ({$pkgStars} Sao ➔ {$pkgMinutes} phút chơi)",
        ]);

        return [
            'success' => true,
            'message' => "Chúc mừng bé đã đổi thành công {$pkgMinutes} phút chơi game!",
            'reward_stars' => (int) $this->fresh()->reward_stars,
            'remaining_stars' => (int) $this->fresh()->reward_stars,
            'game_time_seconds' => (int) $this->fresh()->game_time_seconds,
            'added_seconds' => $addSeconds,
        ];
    }

    /**
     * Tiêu hao thời gian khi chơi mini-game
     */
    public function consumeGameTime(int $seconds): int
    {
        if ($seconds <= 0 || $this->game_time_seconds <= 0) {
            return (int) $this->game_time_seconds;
        }

        $actualSeconds = min($seconds, (int) $this->game_time_seconds);
        $this->decrement('game_time_seconds', $actualSeconds);

        $this->gameTransactions()->create([
            'type' => 'play',
            'stars_change' => 0,
            'time_seconds_change' => -$actualSeconds,
            'description' => "Chơi mini-game {$actualSeconds} giây",
        ]);

        return (int) $this->fresh()->game_time_seconds;
    }

    /**
     * Ép kiểu dữ liệu tự động của Laravel Eloquent
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'expires_at' => 'date',
            'max_students' => 'integer',
            'password' => 'hashed',
            'reward_stars' => 'integer',
            'game_time_seconds' => 'integer',
        ];
    }

    /**
     * Accessor: Lấy ngày tạo tài khoản theo múi giờ Việt Nam (d/m/Y)
     */
    public function getCreatedDateVnAttribute(): string
    {
        $tz = config('learning.display_timezone', 'Asia/Ho_Chi_Minh');

        return $this->created_at ? $this->created_at->setTimezone($tz)->format('d/m/Y') : '';
    }
}
