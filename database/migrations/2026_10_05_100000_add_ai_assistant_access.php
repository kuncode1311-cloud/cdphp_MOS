<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Gói Trợ lý AI: thời hạn quyền dùng AI của tài khoản (độc lập với hạn học tập),
     * và cờ đánh dấu gói nào là gói Trợ lý AI khi thanh toán xong.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('ai_assistant_until')->nullable()->after('expires_at')->comment('Hết hạn quyền dùng Trợ lý AI');
        });

        Schema::table('packages', function (Blueprint $table) {
            $table->boolean('grants_ai_assistant')->default(false)->after('is_active')->comment('Gói này cấp quyền Trợ lý AI thay vì hạn học tập');
        });
    }

    public function down(): void
    {
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn('ai_assistant_until'));
        Schema::table('packages', fn (Blueprint $table) => $table->dropColumn('grants_ai_assistant'));
    }
};
