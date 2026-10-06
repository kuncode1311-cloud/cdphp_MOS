<?php

namespace App\Console\Commands;

use App\Models\SupportMessage;
use Illuminate\Console\Command;

/**
 * Xóa lịch sử chat hỗ trợ của thành viên quá 12 tháng (đúng cam kết hiển thị trong khung chat).
 * Khách vãng lai không được lưu nên không có dữ liệu để xóa.
 */
class PruneSupportChats extends Command
{
    protected $signature = 'support:prune-chats {--months=12 : Số tháng giữ lịch sử}';

    protected $description = 'Xóa đoạn chat hỗ trợ quá hạn lưu trữ';

    public function handle(): int
    {
        $months = max(1, (int) $this->option('months'));
        $cutoff = now()->subMonths($months);

        $deleted = 0;
        // Xóa từng bản ghi để trigger "deleting" dọn ảnh và cuộc gọi liên quan
        SupportMessage::where('updated_at', '<', $cutoff)->chunkById(200, function ($messages) use (&$deleted) {
            foreach ($messages as $message) {
                $message->delete();
                $deleted++;
            }
        });

        $this->info("Đã xóa {$deleted} đoạn chat hỗ trợ cũ hơn {$months} tháng.");

        return self::SUCCESS;
    }
}
