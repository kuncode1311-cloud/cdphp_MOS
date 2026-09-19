<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Schema::table sửa bảng đã có; launch_path lưu đường dẫn bài luyện gốc.
        Schema::table('practice_tests', function (Blueprint $table) {
            $table->string('launch_path')->nullable()->after('slug');
        });
    }

    public function down(): void
    {
        Schema::table('practice_tests', fn (Blueprint $table) => $table->dropColumn('launch_path'));
    }
};
