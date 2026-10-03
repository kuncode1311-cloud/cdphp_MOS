<?php

use App\Models\Level;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Tạo tài khoản học sinh mua lẻ để thử nghiệm: không thuộc giáo viên nào, được mở toàn bộ khối lớp.
     * Chạy lặp lại không tạo trùng.
     */
    public function up(): void
    {
        if (app()->runningUnitTests()) {
            return;
        }

        $student = User::firstOrCreate(
            ['student_code' => 'HSLE01'],
            [
                'name' => 'Bé Mua Lẻ',
                'email' => 'hsle01@student.ic3.local',
                'password' => '123456',
                'role' => 'student',
                'created_by' => null,
                'classroom_id' => null,
                'status' => 'active',
            ]
        );

        $student->accessibleLevels()->syncWithoutDetaching(Level::pluck('id')->all());
    }

    public function down(): void
    {
        User::where('student_code', 'HSLE01')->delete();
    }
};
