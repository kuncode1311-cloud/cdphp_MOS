<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Chạy migrate để tạo cấu trúc quản lý Gói dịch vụ bản quyền và Đơn thuê gói.
     */
    public function up(): void
    {
        // 1. Bảng danh mục Gói dịch vụ / Bản quyền phần mềm
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('slug', 160)->unique();
            $table->string('badge', 50)->nullable()->comment('Nhãn nổi bật: Phổ biến nhất, Tiết kiệm, VIP');
            $table->text('description')->nullable();
            $table->unsignedInteger('price')->default(0)->comment('Giá bán thực tế (VNĐ), 0 = Miễn phí');
            $table->unsignedInteger('original_price')->nullable()->comment('Giá gốc gạch ngang');
            $table->unsignedSmallInteger('duration_days')->default(30)->comment('Thời hạn sử dụng tính theo ngày');
            $table->unsignedSmallInteger('max_students')->default(0)->comment('Hạn mức học sinh quản lý (0 = không giới hạn)');
            $table->json('features')->nullable()->comment('Danh sách đặc quyền / tính năng của gói');
            $table->boolean('is_active')->default(true)->comment('Trạng thái mở bán hiển thị');
            $table->unsignedSmallInteger('sort_order')->default(0)->comment('Thứ tự sắp xếp hiển thị');
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });

        // 2. Bảng pivot liên kết Gói dịch vụ với các Khối lớp được cấp quyền
        Schema::create('package_level', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained('packages')->cascadeOnDelete();
            $table->foreignId('level_id')->constrained('levels')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['package_id', 'level_id']);
        });

        // 3. Bảng lịch sử Đơn đăng ký thuê gói của Giáo viên
        Schema::create('package_orders', function (Blueprint $table) {
            $table->id();
            $table->string('code', 32)->unique()->comment('Mã đơn hàng: MOS-YYYYMM-XXXX');
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('package_id')->constrained('packages')->cascadeOnDelete();
            $table->string('package_name', 150)->comment('Lưu vết tên gói lúc đặt mua');
            $table->unsignedInteger('price')->default(0)->comment('Số tiền thanh toán thực tế');
            $table->unsignedSmallInteger('duration_days')->default(30)->comment('Số ngày gia hạn');
            $table->unsignedSmallInteger('max_students')->default(0)->comment('Số học sinh của gói');
            $table->string('status', 20)->default('pending')->comment('Trạng thái: pending, active, rejected');
            $table->string('payment_method', 30)->default('bank_transfer')->comment('Phương thức thanh toán');
            $table->text('notes')->nullable()->comment('Ghi chú của người mua hoặc phản hồi từ admin');
            $table->timestamp('activated_at')->nullable()->comment('Thời điểm admin kích hoạt gói');
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('created_at');
        });
    }

    /**
     * Rollback gỡ các bảng đã tạo.
     */
    public function down(): void
    {
        Schema::dropIfExists('package_orders');
        Schema::dropIfExists('package_level');
        Schema::dropIfExists('packages');
    }
};
