<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bản sao bền vững của ảnh/tệp câu hỏi trong cơ sở dữ liệu.
     * Ổ đĩa của máy chủ Railway bị xóa sau mỗi lần triển khai nên tệp tải lên / ảnh AI tạo sẽ mất nếu chỉ lưu trên đĩa.
     */
    public function up(): void
    {
        Schema::create('stored_files', function (Blueprint $table) {
            $table->id();
            $table->string('path')->unique();
            $table->string('mime', 100)->nullable();
            $table->unsignedInteger('size')->default(0);
            $table->binary('data');
            $table->timestamps();
        });

        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement('ALTER TABLE stored_files MODIFY data LONGBLOB NOT NULL');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('stored_files');
    }
};
