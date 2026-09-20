<?php

use App\Models\Level;
use App\Models\Package;
use App\Models\PackageOrder;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tự động nạp và cập nhật danh mục Gói dịch vụ bản quyền cùng đơn hàng cho Giáo viên trên Server (Railway/Production)
     */
    public function up(): void
    {
        if (app()->runningUnitTests()) {
            return;
        }

        // 1. Lấy thông tin các Khối lớp hiện có
        $level3 = Level::where('grade', 3)->first();
        $level4 = Level::where('grade', 4)->first();
        $level5 = Level::where('grade', 5)->first();

        // 2. Định nghĩa danh mục các Gói bản quyền giảng dạy IC3 GS6
        $packagesData = [
            [
                'slug' => 'goi-khoi-dau-starter',
                'name' => 'Gói Khởi Đầu (Starter)',
                'badge' => 'Trải nghiệm 🚀',
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
                'levels' => array_filter([$level3?->id]),
            ],
            [
                'slug' => 'goi-tieu-chuan-standard',
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
                'levels' => array_filter([$level3?->id, $level4?->id]),
            ],
            [
                'slug' => 'goi-truong-hoc-toan-dien',
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
                'levels' => array_filter([$level3?->id, $level4?->id, $level5?->id]),
            ],
            [
                'slug' => 'goi-toan-nang-vip-enterprise',
                'name' => 'Gói Bản Quyền Toàn Năng VIP (Enterprise)',
                'badge' => 'VIP Toàn Năng 💎',
                'description' => 'Giải pháp đặc quyền dài hạn 2 năm cho hệ thống giáo dục, trường liên cấp và đối tác chiến lược.',
                'price' => 4990000,
                'original_price' => 7500000,
                'duration_days' => 730,
                'max_students' => 1000,
                'features' => [
                    'Quản lý không giới hạn đến 1.000 học sinh',
                    'Mở khóa toàn diện tất cả các Khối lớp & Chương trình mở rộng',
                    'Thời hạn bản quyền dài hạn 2 năm (730 ngày)',
                    'Bộ công cụ Quản trị Trường học & Báo cáo nâng cao cho Ban Giám Hiệu',
                    'Quyền sử dụng toàn bộ Question Studio soạn đề thi chuyên biệt',
                    'Kênh hỗ trợ kỹ thuật và bảo trì hệ thống ưu tiên 24/7',
                ],
                'is_active' => true,
                'sort_order' => 4,
                'levels' => array_filter([$level3?->id, $level4?->id, $level5?->id]),
            ],
        ];

        // 3. Cập nhật hoặc thêm mới từng gói vào CSDL
        foreach ($packagesData as $pData) {
            $levels = $pData['levels'] ?? [];
            unset($pData['levels']);

            $pkg = Package::updateOrCreate(
                ['slug' => $pData['slug']],
                $pData
            );

            if (! empty($levels)) {
                $pkg->levels()->sync($levels);
            }
        }

        // 4. Đồng bộ Đơn thuê gói bản quyền đang hoạt động cho các Giáo viên
        $standardPkg = Package::where('slug', 'goi-tieu-chuan-standard')->first();
        if ($standardPkg) {
            $teachers = [
                'teacher@ic3.test' => [
                    'code' => 'MOS-202609-ML3A1',
                    'grade' => 3,
                ],
                'teacher4@ic3.test' => [
                    'code' => 'MOS-202609-TT4A1',
                    'grade' => 4,
                ],
                'teacher5@ic3.test' => [
                    'code' => 'MOS-202609-LH5A1',
                    'grade' => 5,
                ],
            ];

            foreach ($teachers as $email => $info) {
                $teacher = User::where('email', $email)->first();
                if ($teacher) {
                    // Cập nhật User
                    $teacher->update([
                        'max_students' => 100,
                        'expires_at' => '2027-08-31 23:59:59',
                        'status' => 'active',
                    ]);

                    // Gán khối giảng dạy nếu chưa có
                    $lvl = Level::where('grade', $info['grade'])->first();
                    if ($lvl && ! $teacher->teacherLevels()->where('level_id', $lvl->id)->exists()) {
                        $teacher->teacherLevels()->syncWithoutDetaching([$lvl->id]);
                    }

                    // Tạo hoặc kích hoạt đơn hàng
                    PackageOrder::updateOrCreate(
                        ['code' => $info['code']],
                        [
                            'user_id' => $teacher->id,
                            'package_id' => $standardPkg->id,
                            'package_name' => $standardPkg->name,
                            'price' => $standardPkg->price,
                            'duration_days' => 365,
                            'max_students' => 100,
                            'status' => PackageOrder::STATUS_ACTIVE,
                            'payment_method' => 'payos',
                            'activated_at' => now(),
                            'notes' => 'Kích hoạt bản quyền giáo viên tự động trên hệ thống máy chủ.',
                        ]
                    );
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Giữ an toàn dữ liệu
    }
};
