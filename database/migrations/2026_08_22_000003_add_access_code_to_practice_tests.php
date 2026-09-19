<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Thêm chỗ lưu mã truy cập; model PracticeTest phụ trách mã hóa giá trị này.
        Schema::table('practice_tests', fn (Blueprint $table) => $table->text('access_code')->nullable()->after('launch_path'));
    }

    public function down(): void
    {
        Schema::table('practice_tests', fn (Blueprint $table) => $table->dropColumn('access_code'));
    }
};
