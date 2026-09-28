<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SaveTopicRequest;
use App\Models\Topic;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

/**
 * Class TopicController
 *
 * Quản lý CRUD Chủ đề (Topic).
 */
class TopicController extends Controller
{
    /**
     * Tạo chủ đề mới
     */
    public function store(SaveTopicRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // 1. Tự động sinh slug duy nhất trong khối lớp (level_id)
        $baseSlug = !empty($data['slug']) ? Str::slug($data['slug']) : Str::slug($data['name']);
        if (empty($baseSlug)) {
            $baseSlug = 'chu-de';
        }

        $slug = $baseSlug;
        $counter = 1;
        while (Topic::where('level_id', $data['level_id'])->where('slug', $slug)->exists()) {
            $counter++;
            $slug = "{$baseSlug}-{$counter}";
        }
        $data['slug'] = $slug;

        // 2. Tự động tính thứ tự hiển thị (position) nếu chưa có hoặc null
        if (!isset($data['position']) || $data['position'] === null) {
            $maxPos = Topic::where('level_id', $data['level_id'])->max('position');
            $data['position'] = ($maxPos !== null) ? ((int) $maxPos + 1) : 0;
        }

        // 3. Đảm bảo icon luôn có giá trị hợp lệ, không bị null
        $data['icon'] = !empty($data['icon']) ? $data['icon'] : 'sparkles';

        try {
            $topic = Topic::create($data);
            $topic->load('level');
            $grade = $topic->level?->grade ?? 3;

            return redirect()->route('admin.questions.studio', [
                'grade' => $grade,
                'topic' => $topic->id,
            ])->with('ok', "Đã thêm chủ đề '{$topic->name}' thành công!");
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Lỗi khi thêm chủ đề: ' . $e->getMessage(), [
                'data' => $data,
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->withInput()->with('error', 'Có lỗi khi tạo chủ đề: ' . $e->getMessage());
        }
    }

    /**
     * Cập nhật chủ đề
     */
    public function update(SaveTopicRequest $request, Topic $topic): RedirectResponse
    {
        $data = $request->validated();

        // 1. Kiểm tra và sinh slug duy nhất (trừ chính chủ đề này)
        $baseSlug = !empty($data['slug']) ? Str::slug($data['slug']) : Str::slug($data['name']);
        if (empty($baseSlug)) {
            $baseSlug = 'chu-de';
        }

        $slug = $baseSlug;
        $counter = 1;
        while (Topic::where('level_id', $topic->level_id)->where('slug', $slug)->where('id', '!=', $topic->id)->exists()) {
            $counter++;
            $slug = "{$baseSlug}-{$counter}";
        }
        $data['slug'] = $slug;

        // 2. Giữ nguyên position và icon nếu không truyền hoặc null
        if (!isset($data['position']) || $data['position'] === null) {
            $data['position'] = $topic->position;
        }
        if (empty($data['icon'])) {
            $data['icon'] = $topic->icon ?: 'sparkles';
        }

        try {
            $topic->update($data);
            $topic->load('level');
            $grade = $topic->level?->grade ?? 3;

            return redirect()->route('admin.questions.studio', [
                'grade' => $grade,
                'topic' => $topic->id,
            ])->with('ok', "Đã cập nhật chủ đề '{$topic->name}'!");
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Lỗi khi cập nhật chủ đề: ' . $e->getMessage(), [
                'topic_id' => $topic->id,
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->withInput()->with('error', 'Có lỗi khi cập nhật chủ đề: ' . $e->getMessage());
        }
    }

    /**
     * Xóa chủ đề và các bài luyện tập con
     */
    public function destroy(Topic $topic): RedirectResponse
    {
        try {
            $topic->load('level');
            $grade = $topic->level?->grade ?? 3;
            $name = $topic->name;
            $topic->delete();

            return redirect()->route('admin.questions.studio', [
                'grade' => $grade,
            ])->with('ok', "Đã xóa chủ đề '{$name}' và toàn bộ bài luyện liên quan.");
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Lỗi khi xóa chủ đề: ' . $e->getMessage());

            return back()->with('error', 'Có lỗi khi xóa chủ đề: ' . $e->getMessage());
        }
    }
}
