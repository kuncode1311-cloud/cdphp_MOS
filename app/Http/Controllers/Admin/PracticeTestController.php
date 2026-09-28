<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SavePracticeTestRequest;
use App\Models\PracticeTest;
use App\Models\Topic;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
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

        $topic = Topic::with('level')->findOrFail($data['topic_id']);

        // 1. Tự động liên kết khối lớp (level_id) từ chủ đề
        $data['level_id'] = $topic->level_id;
        $data['is_mock'] = false;

        // 2. Tự động tạo slug chuẩn và duy nhất trong chủ đề
        $baseSlug = !empty($data['slug']) ? Str::slug($data['slug']) : (Str::slug($data['name']) ?: 'bai-luyen');
        $slug = $baseSlug;
        $counter = 1;
        while (PracticeTest::where('topic_id', $topic->id)->where('slug', $slug)->exists()) {
            $counter++;
            $slug = "{$baseSlug}-{$counter}";
        }
        $data['slug'] = $slug;

        // 3. Tự động gán các trường mặc định không được phép NULL trong CSDL
        if (!isset($data['position']) || $data['position'] === null) {
            $maxPos = PracticeTest::where('topic_id', $topic->id)->max('position');
            $data['position'] = ($maxPos !== null) ? ((int) $maxPos + 1) : 0;
        }

        if (!isset($data['duration_minutes']) || $data['duration_minutes'] === null) {
            $data['duration_minutes'] = 20;
        } else {
            $data['duration_minutes'] = (int) $data['duration_minutes'];
        }

        if (!isset($data['pass_score']) || $data['pass_score'] === null) {
            $data['pass_score'] = 700;
        } else {
            $data['pass_score'] = (int) $data['pass_score'];
        }

        if (!isset($data['max_score']) || $data['max_score'] === null) {
            $data['max_score'] = 1000;
        } else {
            $data['max_score'] = (int) $data['max_score'];
        }

        $data['question_count'] = 0;
        $data['is_published'] = $request->boolean('is_published', true);
        $data['shuffle_questions'] = $request->boolean('shuffle_questions', false);
        $data['shuffle_options'] = $request->boolean('shuffle_options', false);

        try {
            $test = PracticeTest::create($data);
            $grade = $topic->level?->grade ?? 3;

            return redirect()->route('admin.questions.studio', [
                'grade' => $grade,
                'topic' => $test->topic_id,
                'test' => $test->id,
            ])->with('ok', "Đã thêm bộ đề '{$test->name}' thành công!");
        } catch (\Throwable $e) {
            Log::error('Lỗi khi tạo bộ đề mới: ' . $e->getMessage(), [
                'data' => $data,
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->withInput()->with('error', 'Có lỗi khi tạo bộ đề: ' . $e->getMessage());
        }
    }

    /**
     * Cập nhật thông tin bộ đề
     */
    public function update(SavePracticeTestRequest $request, PracticeTest $practiceTest): RedirectResponse
    {
        $data = $request->validated();
        $topicId = $data['topic_id'] ?? $practiceTest->topic_id;

        // 1. Kiểm tra và sinh slug duy nhất (trừ chính bộ đề này)
        $baseSlug = !empty($data['slug']) ? Str::slug($data['slug']) : Str::slug($data['name']);
        if (empty($baseSlug)) {
            $baseSlug = 'bai-luyen';
        }

        $slug = $baseSlug;
        $counter = 1;
        while (PracticeTest::where('topic_id', $topicId)->where('slug', $slug)->where('id', '!=', $practiceTest->id)->exists()) {
            $counter++;
            $slug = "{$baseSlug}-{$counter}";
        }
        $data['slug'] = $slug;

        // 2. Bảo toàn các trường số
        if (isset($data['duration_minutes']) && $data['duration_minutes'] !== null) {
            $data['duration_minutes'] = (int) $data['duration_minutes'];
        }
        if (isset($data['pass_score']) && $data['pass_score'] !== null) {
            $data['pass_score'] = (int) $data['pass_score'];
        }
        if (isset($data['max_score']) && $data['max_score'] !== null) {
            $data['max_score'] = (int) $data['max_score'];
        }
        if (isset($data['position']) && $data['position'] !== null) {
            $data['position'] = (int) $data['position'];
        }

        try {
            $practiceTest->update($data);
            $practiceTest->load('topic.level');
            $grade = $practiceTest->topic?->level?->grade ?? 3;

            return redirect()->route('admin.questions.studio', [
                'grade' => $grade,
                'topic' => $practiceTest->topic_id,
                'test' => $practiceTest->id,
            ])->with('ok', "Đã cập nhật bộ đề '{$practiceTest->name}'!");
        } catch (\Throwable $e) {
            Log::error('Lỗi khi cập nhật bộ đề: ' . $e->getMessage(), [
                'test_id' => $practiceTest->id,
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->withInput()->with('error', 'Có lỗi khi cập nhật bộ đề: ' . $e->getMessage());
        }
    }

    /**
     * Xóa bộ đề và toàn bộ câu hỏi bên trong
     */
    public function destroy(PracticeTest $practiceTest): RedirectResponse
    {
        try {
            $practiceTest->load('topic.level');
            $grade = $practiceTest->topic?->level?->grade ?? 3;
            $topicId = $practiceTest->topic_id;
            $name = $practiceTest->name;

            $practiceTest->delete();

            return redirect()->route('admin.questions.studio', [
                'grade' => $grade,
                'topic' => $topicId,
            ])->with('ok', "Đã xóa bộ đề '{$name}' và toàn bộ câu hỏi liên quan.");
        } catch (\Throwable $e) {
            Log::error('Lỗi khi xóa bộ đề: ' . $e->getMessage());

            return back()->with('error', 'Có lỗi khi xóa bộ đề: ' . $e->getMessage());
        }
    }
}
