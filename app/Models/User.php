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
use Illuminate\Support\Facades\DB;

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
#[Fillable(['name', 'email', 'password', 'role', 'student_code', 'classroom_id', 'created_by', 'max_students', 'expires_at', 'status', 'reward_stars', 'game_time_seconds', 'google_id', 'avatar'])]
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
     * Mối quan hệ: Sổ tay các câu hỏi học sinh đã làm sai (StudentMistakes)
     */
    public function mistakes(): HasMany
    {
        return $this->hasMany(StudentMistake::class);
    }

    /**
     * Helper: Đếm số câu hỏi học sinh đang bị sai (chưa sửa được)
     */
    public function unresolvedMistakesCount(): int
    {
        return $this->mistakes()->where('status', 'unresolved')->count();
    }

    /**
     * Mối quan hệ: Lịch sử đơn thuê gói dịch vụ của người dùng / Giáo viên
     */
    public function packageOrders(): HasMany
    {
        return $this->hasMany(PackageOrder::class)->latest('id');
    }

    /**
     * Tóm tắt gói đang dùng để hiển thị trong hồ sơ.
     * Học sinh do giáo viên quản lý kế thừa gói và hạn dùng của giáo viên; học sinh mua lẻ dùng đơn của chính mình.
     *
     * @return array{teacher: ?string, package: ?string, expires_at: ?\Illuminate\Support\Carbon, inherited: bool}
     */
    public function packageSummary(): array
    {
        $owner = ($this->isStudent() && $this->created_by && $this->teacher) ? $this->teacher : $this;
        // Dùng danh sách đơn đã nạp sẵn (nếu có) để trang danh sách người dùng không phải truy vấn lại cho từng dòng
        $order = $owner->relationLoaded('packageOrders')
            ? $owner->packageOrders->firstWhere('status', PackageOrder::STATUS_ACTIVE)
            : $owner->packageOrders()->where('status', PackageOrder::STATUS_ACTIVE)->with('package')->first();

        return [
            'teacher' => $owner->is($this) ? null : $owner->name,
            'package' => $order?->package?->name,
            'expires_at' => $owner->expires_at,
            'inherited' => ! $owner->is($this),
        ];
    }

    /**
     * Thông tin ngắn gọn về tài khoản và gói đang dùng để hiển thị ở hộp gợi ý trong Tin nhắn tư vấn.
     *
     * @return array<string, mixed>
     */
    public function accountSnapshot(): array
    {
        $summary = $this->packageSummary();
        $pending = $this->packageOrders()->where('status', PackageOrder::STATUS_PENDING)->first();

        return [
            'role' => $this->role,
            'status' => $this->status ?? 'active',
            'independent' => $this->isIndependentStudent(),
            'package' => $summary['package'],
            'expires' => $summary['expires_at']?->format('d/m/Y'),
            'teacher' => $summary['teacher'],
            'inherited' => $summary['inherited'],
            'pending_package' => $pending?->package_name,
            'students' => $this->isTeacher() ? $this->students()->count() : null,
            'max_students' => $this->isTeacher() ? (int) $this->max_students : null,
        ];
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
     * Tài khoản tự đăng ký nhưng bỏ dở: chưa từng được kích hoạt, chưa có đơn nào đang chờ thanh toán còn hiệu lực.
     * Email của tài khoản này được phép đăng ký lại (vd: đơn bị hủy/hết hạn), không bị báo "đã tồn tại".
     */
    public function scopeAbandonedPending(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('status', 'pending')
            ->whereNull('created_by')
            ->whereIn('role', [UserRole::Student->value, UserRole::Teacher->value])
            ->whereHas('packageOrders') // chỉ tài khoản sinh ra từ luồng đăng ký + đặt gói (luôn có đơn)
            ->whereDoesntHave('packageOrders', function ($q) {
                $q->where('status', PackageOrder::STATUS_ACTIVE)
                    ->orWhereIn('id', PackageOrder::query()->pendingLive()->select('id'));
            });
    }

    /**
     * Học sinh mua lẻ: không do giáo viên nào quản lý nên liên hệ trực tiếp Ban Quản Trị
     */
    public function isIndependentStudent(): bool
    {
        return $this->isStudent() && ! $this->created_by;
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

        // 🎒 Nếu là Học sinh thuộc quyền quản lý của Giáo viên:
        // Hạn dùng và trạng thái hoạt động kế thừa trực tiếp từ gói của Giáo viên phụ trách
        if ($this->isStudent() && $this->created_by && $this->teacher) {
            return $this->teacher->isSubscriptionActive();
        }

        if ($this->expires_at && $this->expires_at->isPast() && ! $this->expires_at->isToday()) {
            return false;
        }

        return true;
    }

    /**
     * Lý do không được sử dụng hệ thống vì gói/hạn dùng, hoặc null nếu còn hiệu lực.
     * Học sinh do giáo viên quản lý bị khóa theo gói của giáo viên đó (không xét hạn riêng của học sinh).
     */
    public function subscriptionBlockedMessage(): ?string
    {
        if ($this->isSubscriptionActive()) {
            return null;
        }

        if ($this->isStudent() && $this->created_by && $this->teacher) {
            $teacher = $this->teacher;
            if (($teacher->status ?? 'active') !== 'active') {
                return 'Gói học của lớp đang tạm khóa. Vui lòng liên hệ Thầy/Cô phụ trách.';
            }

            return 'Gói học của lớp đã hết hạn. Vui lòng liên hệ Thầy/Cô phụ trách để gia hạn.';
        }

        if (($this->status ?? 'active') === 'suspended') {
            return 'Tài khoản của bạn đang bị tạm khóa. Vui lòng liên hệ quản trị viên hoặc giáo viên.';
        }

        return 'Tài khoản của bạn đã hết hạn sử dụng. Vui lòng liên hệ để gia hạn gói.';
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
     * Gói Trợ lý AI còn hạn: tài khoản được AI đọc dữ liệu học tập của chính mình và thao tác điều hướng.
     */
    public function hasAiAssistant(): bool
    {
        return $this->ai_assistant_until !== null && $this->ai_assistant_until->isFuture();
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

        // Nếu là Học sinh: Kiểm tra tài khoản còn hạn và các Khối được cấp quyền qua bảng level_user
        if (! $this->isSubscriptionActive()) {
            return false;
        }

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
        if ($this->isAdmin()) {
            return [
                'success' => true,
                'message' => 'Quản trị viên có giờ chơi không giới hạn nên không cần đổi sao.',
                'reward_stars' => (int) $this->reward_stars,
                'remaining_stars' => (int) $this->reward_stars,
                'game_time_seconds' => self::UNLIMITED_GAME_SECONDS,
                'added_seconds' => 0,
            ];
        }

        $pkgStars = (int) GameSetting::get("pkg{$packageNum}_stars", $packageNum === 2 ? 1000 : 500);
        $pkgMinutes = (int) GameSetting::get("pkg{$packageNum}_minutes", $packageNum === 2 ? 7 : 3);
        $pkgTitle = (string) GameSetting::get("pkg{$packageNum}_title", "Gói {$packageNum}");
        $addSeconds = $pkgMinutes * 60;

        // Khóa dòng người dùng trong transaction để hai lần đổi gửi song song không cùng tiêu một số sao
        return DB::transaction(function () use ($pkgStars, $pkgMinutes, $pkgTitle, $addSeconds) {
            $current = static::query()->whereKey($this->getKey())->lockForUpdate()->firstOrFail();

            if ($current->reward_stars < $pkgStars) {
                return [
                    'success' => false,
                    'message' => "Bé cần {$pkgStars} Sao để đổi {$pkgTitle}. Hiện bé đang có {$current->reward_stars} Sao.",
                ];
            }

            $current->decrement('reward_stars', $pkgStars);
            $current->increment('game_time_seconds', $addSeconds);

            $current->gameTransactions()->create([
                'type' => 'exchange',
                'stars_change' => -$pkgStars,
                'time_seconds_change' => $addSeconds,
                'description' => "Đổi {$pkgTitle} ({$pkgStars} Sao ➔ {$pkgMinutes} phút chơi)",
            ]);

            $current->refresh();
            $this->setRawAttributes($current->getAttributes(), true);

            return [
                'success' => true,
                'message' => "Chúc mừng bé đã đổi thành công {$pkgMinutes} phút chơi game!",
                'reward_stars' => (int) $current->reward_stars,
                'remaining_stars' => (int) $current->reward_stars,
                'game_time_seconds' => (int) $current->game_time_seconds,
                'added_seconds' => $addSeconds,
            ];
        });
    }

    /** Giờ chơi hiển thị cho Quản trị viên: luôn đầy, không bị trừ để thử nghiệm đầy đủ tính năng. */
    public const UNLIMITED_GAME_SECONDS = 86400;

    /**
     * Số giây đã chơi mini-game trong ngày hôm nay (theo múi giờ hiển thị).
     */
    public function dailyGameSecondsUsed(): int
    {
        $startOfDay = now(config('learning.display_timezone', 'Asia/Ho_Chi_Minh'))->startOfDay();

        return (int) abs($this->gameTransactions()
            ->where('type', 'play')
            ->where('created_at', '>=', $startOfDay)
            ->sum('time_seconds_change'));
    }

    /**
     * Số giây còn được chơi trong hôm nay theo giới hạn mỗi ngày do Quản trị viên cài đặt (bảo vệ mắt học sinh).
     */
    public function dailyGameSecondsRemaining(): int
    {
        if ($this->isAdmin()) {
            return self::UNLIMITED_GAME_SECONDS;
        }

        $limit = (int) GameSetting::get('max_daily_minutes', 20) * 60;

        return max(0, $limit - $this->dailyGameSecondsUsed());
    }

    /**
     * Hôm nay đã chơi đủ số phút tối đa của ngày (và còn giờ trong ví) nên phải nghỉ đến mai.
     */
    public function hasReachedDailyGameLimit(): bool
    {
        return ! $this->isAdmin() && (int) ($this->game_time_seconds ?? 0) > 0 && $this->dailyGameSecondsRemaining() <= 0;
    }

    /**
     * Giờ chơi mini-game thực tế: Quản trị viên không giới hạn; học sinh là số nhỏ hơn giữa giờ còn trong ví và giờ còn được chơi hôm nay.
     */
    public function effectiveGameTimeSeconds(): int
    {
        if ($this->isAdmin()) {
            return self::UNLIMITED_GAME_SECONDS;
        }

        return min((int) ($this->game_time_seconds ?? 0), $this->dailyGameSecondsRemaining());
    }

    /**
     * Tiêu hao thời gian khi chơi mini-game
     */
    public function consumeGameTime(int $seconds): int
    {
        if ($this->isAdmin()) {
            return self::UNLIMITED_GAME_SECONDS; // Quản trị viên chơi thoải mái, không trừ giờ
        }

        if ($seconds <= 0) {
            return (int) $this->game_time_seconds;
        }

        return DB::transaction(function () use ($seconds) {
            // Đọc lại số giây hiện có dưới khóa dòng, tránh trừ lố khi có nhiều request cùng lúc
            $current = static::query()->whereKey($this->getKey())->lockForUpdate()->firstOrFail();
            if ($current->game_time_seconds <= 0) {
                $this->setRawAttributes($current->getAttributes(), true);

                return (int) $current->game_time_seconds;
            }

            // Không trừ quá số giây còn được chơi trong hôm nay
            $actualSeconds = min($seconds, (int) $current->game_time_seconds, $current->dailyGameSecondsRemaining());
            if ($actualSeconds <= 0) {
                $this->setRawAttributes($current->getAttributes(), true);

                return $current->effectiveGameTimeSeconds();
            }
            $current->decrement('game_time_seconds', $actualSeconds);

            // Mỗi lần trừ giờ chỉ vài giây, nên gộp vào dòng nhật ký 'chơi game' gần nhất (trong 2 phút) thay vì tạo dòng mới
            $latestPlay = $current->gameTransactions()->where('type', 'play')->first();
            if ($latestPlay && $latestPlay->updated_at && $latestPlay->updated_at->gt(now()->subSeconds(120))) {
                $total = abs((int) $latestPlay->time_seconds_change) + $actualSeconds;
                $latestPlay->update([
                    'time_seconds_change' => -$total,
                    'description' => 'Chơi mini-game '.GameTransaction::formatSeconds($total),
                ]);
            } else {
                $current->gameTransactions()->create([
                    'type' => 'play',
                    'stars_change' => 0,
                    'time_seconds_change' => -$actualSeconds,
                    'description' => 'Chơi mini-game '.GameTransaction::formatSeconds($actualSeconds),
                ]);
            }

            $current->refresh();
            $this->setRawAttributes($current->getAttributes(), true);

            return $current->effectiveGameTimeSeconds();
        });
    }

    /**
     * Ép kiểu dữ liệu tự động của Laravel Eloquent
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'expires_at' => 'date',
            'ai_assistant_until' => 'datetime',
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
