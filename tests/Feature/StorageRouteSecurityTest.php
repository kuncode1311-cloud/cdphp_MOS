<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Route /storage chỉ được phục vụ file trong storage/app/public, không cho đọc file ngoài thư mục.
 */
class StorageRouteSecurityTest extends TestCase
{
    public function test_chan_duong_dan_vuot_thu_muc_va_van_phuc_vu_file_hop_le(): void
    {
        $dir = storage_path('app/public');
        @mkdir($dir, 0777, true);
        file_put_contents($dir . '/kiem-tra-bao-mat.txt', 'ok');

        try {
            $this->get('/storage/kiem-tra-bao-mat.txt')->assertOk();
            $this->get('/storage/..%2F..%2F..%2F.env.example')->assertNotFound();
            $this->get('/storage/..%2F..%2F..%2Fcomposer.json')->assertNotFound();
            $this->get('/storage/khong-ton-tai.png')->assertNotFound();
        } finally {
            @unlink($dir . '/kiem-tra-bao-mat.txt');
        }
    }
}
