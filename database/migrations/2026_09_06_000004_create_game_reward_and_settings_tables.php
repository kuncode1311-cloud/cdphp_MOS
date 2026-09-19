<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Bổ sung trường điểm thưởng và thời gian chơi vào bảng users
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('reward_stars')->default(0)->after('classroom_id');
            $table->unsignedInteger('game_time_seconds')->default(0)->after('reward_stars');
        });

        // 2. Bảng cài đặt cấu hình Khu trò chơi
        Schema::create('game_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // 3. Bảng lịch sử giao dịch Điểm thưởng / Đổi giờ chơi
        Schema::create('game_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // 'earn', 'exchange', 'play', 'admin_adjust'
            $table->integer('stars_change')->default(0);
            $table->integer('time_seconds_change')->default(0);
            $table->string('description')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'created_at']);
        });

        // 4. Khởi tạo cấu hình mặc định ban đầu
        $now = now();
        $defaultSettings = [
            ['key' => 'game_enabled', 'value' => '1', 'description' => 'Trạng thái hoạt động của Khu trò chơi (1 = Bật, 0 = Tắt)', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'pkg1_stars', 'value' => '500', 'description' => 'Gói 1: Số Sao cần đổi', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'pkg1_minutes', 'value' => '3', 'description' => 'Gói 1: Số Phút chơi nhận được', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'pkg1_title', 'value' => 'Gói Khởi Động', 'description' => 'Gói 1: Tiêu đề hiển thị', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'pkg2_stars', 'value' => '1000', 'description' => 'Gói 2: Số Sao cần đổi', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'pkg2_minutes', 'value' => '7', 'description' => 'Gói 2: Số Phút chơi nhận được (Thưởng +1 phút)', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'pkg2_title', 'value' => 'Gói Siêu Hiệp Sĩ', 'description' => 'Gói 2: Tiêu đề hiển thị', 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'max_daily_minutes', 'value' => '20', 'description' => 'Giới hạn thời gian chơi tối đa mỗi ngày của học sinh (phút)', 'created_at' => $now, 'updated_at' => $now],
        ];
        DB::table('game_settings')->insert($defaultSettings);

        // 5. Đồng bộ số Sao ban đầu cho học sinh dựa trên tổng điểm các bài thi đã làm trong quá khứ
        $attemptsByUser = DB::table('test_attempts')
            ->select('user_id', DB::raw('SUM(score) as total_score'))
            ->groupBy('user_id')
            ->get();

        foreach ($attemptsByUser as $item) {
            DB::table('users')->where('id', $item->user_id)->update([
                'reward_stars' => (int) $item->total_score,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_transactions');
        Schema::dropIfExists('game_settings');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['reward_stars', 'game_time_seconds']);
        });
    }
};
