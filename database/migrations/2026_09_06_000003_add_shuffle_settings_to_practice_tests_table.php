<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('practice_tests', function (Blueprint $table) {
            $table->boolean('shuffle_questions')->default(false)->after('is_published');
            $table->boolean('shuffle_options')->default(false)->after('shuffle_questions');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('practice_tests', function (Blueprint $table) {
            $table->dropColumn(['shuffle_questions', 'shuffle_options']);
        });
    }
};
