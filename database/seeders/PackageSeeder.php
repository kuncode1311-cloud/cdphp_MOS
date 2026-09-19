<?php

namespace Database\Seeders;

use App\Models\Level;
use App\Models\Package;
use Illuminate\Database\Seeder;

class PackageSeeder extends Seeder
{
    /**
     * Khởi tạo các Gói dịch vụ mẫu ban đầu cho hệ thống IC3 Quest.
     */
    public function run(): void
    {
        $level3 = Level::where('grade', 3)->first();
        $level4 = Level::where('grade', 4)->first();
        $level5 = Level::where('grade', 5)->first();

        // 1. Gói Khởi Đầu (Starter)
        $pkg1 = Package::updateOrCreate(
            ['slug' => 'goi-khoi-dau-starter'],
            [
                'name' => 'Gói Khởi Đầu (Starter)',
                'badge' => 'Trải nghiệm',
                'description' => 'Phù hợp cho giáo viên chủ nhiệm trải nghiệm ôn luyện cho một lớp học trong 1 tháng.',
                'price' => 390000,
                'original_price' => 590000,
                'duration_days' => 30,
                'max_students' => 35,
                'features' => [
                    'Quản lý tối đa 35 học sinh',
                    'Cấp quyền bài học & đề thi Khối 3',
                    'Làm bài thi trắc nghiệm & mô phỏng tương tác',
                    'Tự động chấm điểm & xếp loại chuẩn quốc tế',
                    'Bảng vàng thi đua & báo cáo kết quả cơ bản',
                ],
                'is_active' => true,
                'sort_order' => 1,
            ]
        );
        if ($level3) {
            $pkg1->levels()->sync([$level3->id]);
        }

        // 2. Gói Lớp Học Tiêu Chuẩn (Standard - Phổ biến nhất)
        $pkg2 = Package::updateOrCreate(
            ['slug' => 'goi-tieu-chuan-standard'],
            [
                'name' => 'Gói Tiêu Chuẩn (Standard)',
                'badge' => 'Phổ biến nhất ⭐',
                'description' => 'Giải pháp tối ưu cho giáo viên giảng dạy nhiều lớp trong một học kỳ hoàn chỉnh.',
                'price' => 990000,
                'original_price' => 1490000,
                'duration_days' => 90,
                'max_students' => 100,
                'features' => [
                    'Quản lý tối đa 100 học sinh',
                    'Mở khóa 2 Khối lớp (Khối 3 và Khối 4)',
                    'Toàn bộ ngân hàng đề thi chuẩn IC3 GS6',
                    'Khu trò chơi tích sao đổi quà & mini-game giáo dục',
                    'Báo cáo phân tích chuyên sâu từng chủ đề',
                    'Xuất kết quả bài thi ra tệp Excel / CSV',
                    'Hỗ trợ kỹ thuật nhanh chóng',
                ],
                'is_active' => true,
                'sort_order' => 2,
            ]
        );
        $levelsPkg2 = array_filter([$level3?->id, $level4?->id]);
        if (! empty($levelsPkg2)) {
            $pkg2->levels()->sync($levelsPkg2);
        }

        // 3. Gói Trường Học Toàn Diện (Pro School - Siêu tiết kiệm)
        $pkg3 = Package::updateOrCreate(
            ['slug' => 'goi-truong-hoc-toan-dien'],
            [
                'name' => 'Gói Trường Học Toàn Diện (Pro School)',
                'badge' => 'Siêu tiết kiệm 🔥',
                'description' => 'Gói cao cấp dành cho trường học, trung tâm tin học và giáo viên phụ trách toàn trường cả năm học.',
                'price' => 2490000,
                'original_price' => 3800000,
                'duration_days' => 365,
                'max_students' => 300,
                'features' => [
                    'Quản lý tối đa 300 học sinh',
                    'Mở khóa TOÀN BỘ 3 Khối lớp (Khối 3, 4, 5)',
                    'Không giới hạn số lượt làm bài & thi thử',
                    'Trợ lý AI phân tích điểm mạnh & lỗ hổng kiến thức',
                    'Quyền truy cập Studio tùy chỉnh và tạo bộ đề riêng',
                    'Cấu hình xáo trộn câu hỏi & bảo mật phòng thi',
                    'Bàn giao hệ thống & Hỗ trợ kỹ thuật VIP 1-1',
                ],
                'is_active' => true,
                'sort_order' => 3,
            ]
        );
        $levelsPkg3 = array_filter([$level3?->id, $level4?->id, $level5?->id]);
        if (! empty($levelsPkg3)) {
            $pkg3->levels()->sync($levelsPkg3);
        }
    }
}
