<?php

namespace Tests\Feature;

use App\Models\PracticeTest;
use App\Models\StudentMistake;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Sổ tay câu sai: câu sai gần nhất lên đầu, mỗi lần hiện 30 câu và nút "Xem thêm" tải tiếp.
 */
class MistakeNotebookLoadMoreTest extends TestCase
{
    use RefreshDatabase;

    private function studentWithMistakes(int $count): User
    {
        $this->seed();
        $student = User::where('student_code', 'HS001')->firstOrFail();
        $test = PracticeTest::where('slug', 'k3-cd1-bai-1')->firstOrFail();

        for ($i = 1; $i <= $count; $i++) {
            $q = $test->questions()->create([
                'type' => 'MultipleChoice', 'title' => "Câu sai số {$i}", 'configuration' => [],
                'position' => $i, 'points' => 1, 'is_published' => true,
            ]);
            StudentMistake::create([
                'user_id' => $student->id, 'question_id' => $q->id, 'practice_test_id' => $test->id,
                'wrong_count' => $i === 1 ? 5 : 1, 'correct_count' => 0, 'last_student_answer' => [0],
                'status' => 'unresolved', 'last_wrong_at' => now()->subMinutes(1000 - $i), // số càng lớn càng gần đây
            ]);
        }

        return $student;
    }

    public function test_hien_30_cau_gan_nhat_truoc_va_co_nut_xem_them(): void
    {
        $student = $this->studentWithMistakes(65);

        $html = $this->actingAs($student)->get(route('mistakes.index'))->assertOk()->getContent();

        $this->assertSame(30, substr_count($html, 'class="q-title-text"'));
        // Câu sai gần nhất (số 65) đứng đầu; câu số 1 dù sai nhiều lần hơn nhưng cũ nhất nên nằm ở trang sau
        $this->assertLessThan(strpos($html, 'Câu sai số 36<'), strpos($html, 'Câu sai số 65<'));
        $this->assertStringNotContainsString('Câu sai số 35<', $html);
        $this->assertStringNotContainsString('Câu sai số 1<', $html);
        $this->assertStringContainsString('id="btn-load-more-mistakes"', $html);
        $this->assertStringNotContainsString('pagination.previous', $html);
    }

    public function test_xem_them_tra_ve_30_cau_tiep_theo_va_trang_cuoi_het_nut(): void
    {
        $student = $this->studentWithMistakes(65);

        $page2 = $this->actingAs($student)->getJson(route('mistakes.index', ['page' => 2]))->assertOk()->json();
        $this->assertSame(30, substr_count($page2['html'], 'class="q-title-text"'));
        $this->assertNotNull($page2['next_url']);

        $page3 = $this->actingAs($student)->getJson(route('mistakes.index', ['page' => 3]))->assertOk()->json();
        $this->assertSame(5, substr_count($page3['html'], 'class="q-title-text"'));
        $this->assertNull($page3['next_url']);
    }

    public function test_it_hon_30_cau_thi_khong_hien_nut_xem_them(): void
    {
        $student = $this->studentWithMistakes(10);

        $this->actingAs($student)->get(route('mistakes.index'))->assertOk()->assertDontSee('id="btn-load-more-mistakes"', false);
    }
}
