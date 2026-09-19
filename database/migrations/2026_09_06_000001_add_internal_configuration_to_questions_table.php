<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Cấu hình nghiệp vụ do MOS sở hữu. Cột này không được lưu JSON iSpring
     * hay mã/URL của nguồn import; chỉ lưu các thông số cần để hiển thị và chấm.
     */
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->json('configuration')->nullable()->after('title');
        });
    }

    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn('configuration');
        });
    }
};
