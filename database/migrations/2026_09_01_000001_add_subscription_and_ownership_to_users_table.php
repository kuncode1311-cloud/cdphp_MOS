<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Chạy migrate thì Laravel gọi up() để tạo hoặc bổ sung cấu trúc bảng.
     * Bổ sung các trường quản lý gói thuê bao, thời hạn và phân quyền sở hữu người dùng.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Teacher sở hữu Student này (với Teacher/Admin thì để null)
            $table->foreignId('created_by')->nullable()->after('classroom_id')->constrained('users')->nullOnDelete();
            
            // Giới hạn số học sinh tối đa Teacher được phép tạo (Áp dụng cho Giáo viên)
            // Trong model User, 0 được hiểu là không giới hạn số học sinh.
            $table->unsignedSmallInteger('max_students')->default(0)->after('created_by');
            
            // Thời điểm hết hạn gói tài khoản của Giáo viên (null = vĩnh viễn)
            $table->date('expires_at')->nullable()->after('max_students');
            
            // Trạng thái tài khoản: active (hoạt động), suspended (tạm khóa), expired (hết hạn)
            $table->string('status', 30)->default('active')->after('expires_at');
        });
    }

    /**
     * Khi rollback, down() gỡ lại phần cấu trúc mà migration này đã tạo.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('created_by');
            $table->dropColumn(['max_students', 'expires_at', 'status']);
        });
    }
};
