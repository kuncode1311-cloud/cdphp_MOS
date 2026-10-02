<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tài liệu API (Scramble) chỉ dành cho Quản trị viên tổng và phải liệt kê các route chính.
 */
class ApiDocsTest extends TestCase
{
    use RefreshDatabase;

    public function test_chi_admin_tong_xem_duoc_tai_lieu_api(): void
    {
        $this->seed();
        $this->get('/docs/api.json')->assertForbidden();
        $this->actingAs(User::where('role', 'teacher')->firstOrFail())->get('/docs/api.json')->assertForbidden();
        $this->actingAs(User::where('role', 'admin')->firstOrFail())->get('/docs/api')->assertOk();
    }

    public function test_tai_lieu_liet_ke_live_chat_goi_dien_va_webhook(): void
    {
        $this->seed();
        $paths = $this->actingAs(User::where('role', 'admin')->firstOrFail())
            ->getJson('/docs/api.json')->assertOk()->json('paths');

        foreach (['/ho-tro/gui-tin-nhan', '/quan-tri/cuoc-goi', '/quan-tri/tin-nhan/{supportMessage}/anh', '/stringee/answer'] as $path) {
            $this->assertArrayHasKey($path, $paths, "Thiếu {$path} trong tài liệu API");
        }
    }
}
