<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations: Loại bỏ cột difficulty (độ khó) khỏi bảng practice_tests
     */
    public function up(): void
    {
        if (Schema::hasColumn('practice_tests', 'difficulty')) {
            Schema::table('practice_tests', function (Blueprint $table) {
                $table->dropColumn('difficulty');
            });
        }
    }

    /**
     * Reverse the migrations: Khôi phục cột difficulty nếu cần
     */
    public function down(): void
    {
        if (!Schema::hasColumn('practice_tests', 'difficulty')) {
            Schema::table('practice_tests', function (Blueprint $table) {
                $table->enum('difficulty', ['Cơ bản', 'Trung bình', 'Nâng cao'])->default('Cơ bản')->after('max_score');
            });
        }
    }
};
