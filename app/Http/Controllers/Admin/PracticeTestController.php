<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SavePracticeTestRequest;
use App\Models\PracticeTest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

/**
 * Class PracticeTestController
 *
 * Quản lý CRUD Bộ đề luyện tập (PracticeTest).
 */
class PracticeTestController extends Controller
{
    /**
     * Tạo bộ đề mới
     */
    public function store(SavePracticeTestRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // Tự động tạo slug nếu chưa có
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']).'-'.Str::lower(Str::random(4));
        }

        $test = PracticeTest::create($data);

        return redirect()->route('admin.questions.studio', [
            'grade' => $test->topic->level->grade,
            'topic' => $test->topic_id,
            'test' => $test->id,
        ])->with('ok', "Đã thêm bộ đề '{$test->name}' thành công!");
    }

    /**
     * Cập nhật thông tin bộ đề
     */
    public function update(SavePracticeTestRequest $request, PracticeTest $practiceTest): RedirectResponse
    {
        $data = $request->validated();
        $practiceTest->update($data);

        return redirect()->route('admin.questions.studio', [
            'grade' => $practiceTest->topic->level->grade,
            'topic' => $practiceTest->topic_id,
            'test' => $practiceTest->id,
        ])->with('ok', "Đã cập nhật bộ đề '{$practiceTest->name}'!");
    }

    /**
     * Xóa bộ đề và toàn bộ câu hỏi bên trong
     */
    public function destroy(PracticeTest $practiceTest): RedirectResponse
    {
        $topic = $practiceTest->topic;
        $practiceTest->delete();

        return redirect()->route('admin.questions.studio', [
            'grade' => $topic->level->grade,
            'topic' => $topic->id,
        ])->with('ok', 'Đã xóa bộ đề và toàn bộ câu hỏi liên quan.');
    }
}
