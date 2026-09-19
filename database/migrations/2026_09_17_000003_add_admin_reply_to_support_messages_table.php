<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Thêm cột admin_reply và replied_at để lưu trữ phản hồi trực tiếp của admin
     */
    public function up(): void
    {
        Schema::table('support_messages', function (Blueprint $table) {
            $table->text('admin_reply')->nullable()->after('message')->comment('Nội dung phản hồi từ Admin');
            $table->timestamp('replied_at')->nullable()->after('admin_reply')->comment('Thời điểm Admin gửi phản hồi');
        });
    }

    /**
     * Rollback migrations
     */
    public function down(): void
    {
        Schema::table('support_messages', function (Blueprint $table) {
            $table->dropColumn(['admin_reply', 'replied_at']);
        });
    }
};
