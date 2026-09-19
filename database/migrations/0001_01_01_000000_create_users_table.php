<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Chạy migrate thì Laravel gọi up() để tạo hoặc bổ sung cấu trúc bảng.
     */
    public function up(): void
    {
        // Bảng tài khoản chung; vai trò và thông tin lớp học được thêm ở các migration sau.
        Schema::create('users', function (Blueprint $table) {
            // id là mã tự tăng; các bảng khác dùng mã này để liên kết tới tài khoản.
            $table->id();
            $table->string('name');
            // unique: không cho hai tài khoản dùng trùng email.
            $table->string('email')->unique();
            // nullable: được để trống, vì tài khoản có thể chưa xác minh email.
            $table->timestamp('email_verified_at')->nullable();
            // Lưu mật khẩu đã băm; việc băm nằm ở model User, không phải migration.
            $table->string('password');
            // Token để ghi nhớ đăng nhập; timestamps thêm created_at và updated_at.
            $table->rememberToken();
            $table->timestamps();
        });

        // Lưu token để đặt lại mật khẩu khi người dùng quên mật khẩu.
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        // Phiên truy cập khi dùng session bằng database; khách chưa đăng nhập có user_id rỗng.
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            // Chỉ tạo cột và index để tra cứu nhanh, chưa khai báo ràng buộc khóa ngoại.
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Khi rollback, down() gỡ lại phần cấu trúc mà migration này đã tạo.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
