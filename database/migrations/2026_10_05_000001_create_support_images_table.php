<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ảnh trong chat hỗ trợ lưu thẳng vào cơ sở dữ liệu để không mất khi máy chủ khởi động lại / triển khai bản mới.
     */
    public function up(): void
    {
        Schema::create('support_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('support_message_id')->nullable()->constrained('support_messages')->nullOnDelete();
            $table->string('token', 64)->unique();
            $table->string('mime', 50);
            $table->unsignedInteger('size');
            $table->binary('data');
            $table->timestamps();
        });

        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement('ALTER TABLE support_images MODIFY data LONGBLOB NOT NULL');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('support_images');
    }
};
