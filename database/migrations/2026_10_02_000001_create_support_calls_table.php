<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tạo bảng nhật ký cuộc gọi hỗ trợ khách hàng (kèm file ghi âm để minh bạch).
     */
    public function up(): void
    {
        Schema::create('support_calls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('support_message_id')->nullable()->constrained('support_messages')->nullOnDelete();
            $table->foreignId('admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('stringee_call_id', 120)->nullable()->index()->comment('Mã cuộc gọi do Stringee cấp');
            $table->string('from_number', 30)->nullable()->comment('Số hiển thị khi gọi (số Stringee)');
            $table->string('to_number', 30)->comment('Số điện thoại khách, dạng 84xxxxxxxxx');
            $table->string('status', 30)->default('calling')->comment('calling, answered, ended, missed, failed');
            $table->string('end_reason', 120)->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('answered_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->unsignedInteger('duration')->default(0)->comment('Thời lượng đàm thoại (giây)');
            $table->string('recording_url', 500)->nullable()->comment('Link ghi âm gốc từ Stringee');
            $table->string('recording_path', 300)->nullable()->comment('File ghi âm đã lưu về máy chủ');
            $table->text('note')->nullable()->comment('Ghi chú nội dung cuộc gọi');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_calls');
    }
};
