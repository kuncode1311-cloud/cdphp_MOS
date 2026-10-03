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
        $this->get('/docs/api')->assertRedirect(route('login'));
        $this->get('/docs/api.json')->assertForbidden();
        $this->actingAs(User::where('role', 'teacher')->firstOrFail())->get('/docs/api.json')->assertForbidden();
        $this->actingAs(User::where('role', 'admin')->firstOrFail())->get('/docs/api')->assertOk()->assertSee('swagger-ui', false);
    }

    public function test_tai_lieu_liet_ke_live_chat_goi_dien_va_webhook(): void
    {
        $this->seed();
        $paths = $this->actingAs(User::where('role', 'admin')->firstOrFail())
            ->getJson('/docs/api.json')->assertOk()->json('paths');

        // Toàn bộ route của hệ thống đều có trong tài liệu để demo
        $this->assertGreaterThan(80, count($paths), 'Tài liệu API đang thiếu nhiều route');
        $this->assertArrayHasKey('/dang-nhap', $paths);

        foreach (['/ho-tro/gui-tin-nhan', '/quan-tri/cuoc-goi', '/quan-tri/tin-nhan/{supportMessage}/anh', '/api/stringee/answer'] as $path) {
            $this->assertArrayHasKey($path, $paths, "Thiếu {$path} trong tài liệu API");
        }
    }

    public function test_duong_dan_cu_docs_swagger_chuyen_ve_docs_api(): void
    {
        $this->get('/docs/swagger')->assertRedirect('/docs/api');
    }
}
