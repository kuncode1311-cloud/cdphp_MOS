<?php

namespace App\Console\Commands;

use App\Models\PackageOrder;
use App\Services\PayosService;
use App\Services\SubscriptionService;
use Illuminate\Console\Command;

/**
 * Lưới an toàn cho đơn thanh toán online PayOS: nếu khách đã trả tiền nhưng đóng tab trước khi
 * webhook PayOS kịp gọi về (hoặc webhook cấu hình sai), lệnh này chủ động hỏi thẳng PayOS theo
 * chu kỳ để không bỏ sót đơn đã thanh toán. Chỉ xét đơn còn trong hạn 10 phút của mã QR.
 */
class ReconcilePendingPayosOrders extends Command
{
    protected $signature = 'payos:reconcile-pending';

    protected $description = 'Đối chiếu trực tiếp với PayOS các đơn online đang chờ thanh toán, phòng khi webhook lỗi';

    public function handle(SubscriptionService $subscriptionService, PayosService $payosService): int
    {
        $activated = 0;

        PackageOrder::query()->onlinePending()
            ->where('payos_expires_at', '>', now())
            ->each(function (PackageOrder $order) use ($subscriptionService, $payosService, &$activated) {
                if ($subscriptionService->reconcilePayosOrder($order, $payosService)) {
                    $activated++;
                }
            });

        $this->info("Đã kích hoạt {$activated} đơn PayOS qua đối chiếu dự phòng.");

        return self::SUCCESS;
    }
}
