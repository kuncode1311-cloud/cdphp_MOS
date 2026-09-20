<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ImportDatabaseCommand extends Command
{
    /**
     * Tên và cú pháp của lệnh artisan
     *
     * @var string
     */
    protected $signature = 'mos:import-db {--file= : Đường dẫn file SQL cần nạp}';

    /**
     * Mô tả lệnh
     *
     * @var string
     */
    protected $description = 'Nạp toàn bộ cơ sở dữ liệu mẫu chuẩn MOS từ file database/mos.sql';

    /**
     * Thực thi lệnh
     */
    public function handle(): int
    {
        $filePath = $this->option('file') ?: database_path('mos.sql');

        if (!File::exists($filePath)) {
            $this->error("Không tìm thấy file CSDL tại: {$filePath}");
            return Command::FAILURE;
        }

        $this->info("Đang nạp cơ sở dữ liệu từ: {$filePath}...");

        try {
            $sql = File::get($filePath);

            // Tắt kiểm tra khóa ngoại để import mượt mà
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            DB::unprepared($sql);
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            $this->info("✓ Nạp cơ sở dữ liệu thành công! Đã sẵn sàng trải nghiệm và kiểm thử.");
            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("Lỗi khi nạp cơ sở dữ liệu: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
