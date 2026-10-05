<?php

namespace Tests\Feature;

use App\Models\StoredFile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Ảnh câu hỏi (kể cả ảnh AI tạo) phải sống sót qua lần triển khai lại: có bản sao trong CSDL và tự phục hồi ra đĩa.
 */
class PersistentQuestionImageTest extends TestCase
{
    use RefreshDatabase;

    public function test_anh_bi_mat_khoi_dia_van_mo_lai_duoc_tu_ban_sao_trong_csdl(): void
    {
        Storage::fake('public');
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==');
        Storage::disk('public')->put('question-assets/ai-abc-123.png', $png);
        StoredFile::remember('/storage/question-assets/ai-abc-123.png');
        $this->assertDatabaseCount('stored_files', 1);

        // Mô phỏng triển khai lại: đĩa bị xóa sạch
        Storage::disk('public')->delete('question-assets/ai-abc-123.png');
        Storage::disk('public')->assertMissing('question-assets/ai-abc-123.png');

        $this->get('/storage/question-assets/ai-abc-123.png')->assertOk()->assertHeader('Content-Type', 'image/png');
        Storage::disk('public')->assertExists('question-assets/ai-abc-123.png');
    }

    public function test_khong_co_ban_sao_thi_404_va_chan_duong_dan_la(): void
    {
        Storage::fake('public');

        $this->get('/storage/question-assets/khong-co.png')->assertNotFound();
        $this->get('/storage/private/secret.png')->assertNotFound();
    }

    public function test_xoa_co_chu_dich_thi_xoa_luon_ban_sao(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('question-assets/ai-x.png', 'x-noi-dung-anh-thu-nghiem');
        StoredFile::remember('question-assets/ai-x.png');
        $this->assertDatabaseCount('stored_files', 1);

        StoredFile::forget('/storage/question-assets/ai-x.png');

        $this->assertDatabaseCount('stored_files', 0);
    }
}