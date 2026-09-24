<?php

use App\Models\Level;
use App\Models\Package;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Chạy migration: Bổ sung cột target_audience và khởi tạo 3 gói mẫu cho học sinh
     */
    public function up(): void
    {
        // 1. Thêm cột target_audience vào bảng packages nếu chưa có
        if (! Schema::hasColumn('packages', 'target_audience')) {
            Schema::table('packages', function (Blueprint $table) {
                $table->string('target_audience', 30)
                    ->default('teacher')
                    ->after('badge')
                    ->comment('Đối tượng phục vụ: teacher (Giáo viên / Trường học) hoặc student (Học sinh / Cá nhân)');
                
                $table->index(['target_audience', 'is_active', 'sort_order']);
            });
        }

        // Cập nhật tất cả các gói hiện có thành gói giáo viên
        DB::table('packages')
            ->whereNull('target_audience')
            ->orWhere('target_audience', '')
            ->update(['target_audience' => 'teacher']);

        if (app()->runningUnitTests()) {
            return;
        }

        // 2. Lấy thông tin các Khối lớp để cấp quyền
        $level3 = Level::where('grade', 3)->first();
        $level4 = Level::where('grade', 4)->first();
        $level5 = Level::where('grade', 5)->first();

        // 3. Khởi tạo 3 gói tự luyện chuẩn cho Học sinh & Phụ huynh
        $studentPackages = [
            [
                'slug' => 'goi-kham-pha-hoc-sinh',
                'name' => 'Gói Khám Phá (1 Tháng)',
                'badge' => '🚀 Tự Luyện Cấp Tốc',
                'target_audience' => 'student',
                'description' => 'Dành cho học sinh tự ôn luyện 1 khối lớp trọng điểm trong 1 tháng trước kỳ thi.',
                'price' => 69000,
                'original_price' => 99000,
                'duration_days' => 30,
                'max_students' => 1,
                'features' => [
                    'Dành cho 1 tài khoản học sinh tự luyện',
                    'Mở khóa toàn bộ bài luyện & đề thi Khối 3',
                    'Chấm điểm và giải thích đáp án chi tiết',
                    'Tích Sao thưởng đổi giờ chơi mini-game',
                    'Đua top Bảng vàng thi đua tuần',
                ],
                'is_active' => true,
                'sort_order' => 1,
                'levels' => array_filter([$level3?->id]),
            ],
            [
                'slug' => 'goi-but-pha-hoc-sinh',
                'name' => 'Gói Bứt Phá (1 Học Kỳ)',
                'badge' => '⭐ Được Chọn Nhiều Nhất',
                'target_audience' => 'student',
                'description' => 'Gói ôn luyện xuyên suốt 1 học kỳ, nâng cao phản xạ tin học và làm quen đề thi quốc tế.',
                'price' => 149000,
                'original_price' => 249000,
                'duration_days' => 90,
                'max_students' => 1,
                'features' => [
                    'Dành cho 1 tài khoản học sinh tự luyện',
                    'Mở khóa 2 Khối học liên thông (Khối 3 và Khối 4)',
                    'Làm đề thi thử không giới hạn số lượt',
                    'Báo cáo phân tích điểm mạnh & điểm yếu',
                    'Tặng thêm 500 Sao thưởng khởi đầu',
                    'Hỗ trợ giải đáp thắc mắc 24/7',
                ],
                'is_active' => true,
                'sort_order' => 2,
                'levels' => array_filter([$level3?->id, $level4?->id]),
            ],
            [
                'slug' => 'goi-chinh-phuc-combo-hoc-sinh',
                'name' => 'Gói Chinh Phục Toàn Năng (1 Năm)',
                'badge' => '👑 Siêu Tiết Kiệm Cả Năm',
                'target_audience' => 'student',
                'description' => 'Giải pháp toàn diện cả năm học mở khóa TOÀN BỘ 3 Khối Tiểu học (3, 4, 5) chinh phục chứng chỉ quốc tế IC3.',
                'price' => 299000,
                'original_price' => 499000,
                'duration_days' => 365,
                'max_students' => 1,
                'features' => [
                    'Dành cho 1 tài khoản học sinh tự luyện',
                    'Mở khóa TOÀN BỘ 3 Khối lớp (Khối 3, 4, 5)',
                    'Toàn bộ ngân hàng đề thi chuẩn IC3 GS6 mới nhất',
                    'Trợ lý học tập thông minh phát hiện lỗ hổng',
                    'Quyền tham gia mọi kỳ thi thử định kỳ',
                    'Đổi giờ chơi mini-game không giới hạn',
                ],
                'is_active' => true,
                'sort_order' => 3,
                'levels' => array_filter([$level3?->id, $level4?->id, $level5?->id]),
            ],
        ];

        foreach ($studentPackages as $pkgData) {
            $levels = $pkgData['levels'];
            unset($pkgData['levels']);

            $package = Package::updateOrCreate(
                ['slug' => $pkgData['slug']],
                $pkgData
            );

            if (! empty($levels)) {
                $package->levels()->sync($levels);
            }
        }
    }

    /**
     * Đảo ngược migration
     */
    public function down(): void
    {
        // Xóa các gói học sinh mẫu
        Package::where('target_audience', 'student')->delete();

        if (Schema::hasColumn('packages', 'target_audience')) {
            Schema::table('packages', function (Blueprint $table) {
                $table->dropIndex(['target_audience', 'is_active', 'sort_order']);
                $table->dropColumn('target_audience');
            });
        }
    }
};
