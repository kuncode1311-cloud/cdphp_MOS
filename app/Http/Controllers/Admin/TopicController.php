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

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $topic = Topic::create($data);

        return redirect()->route('admin.questions.studio', [
            'grade' => $topic->level->grade,
            'topic' => $topic->id,
        ])->with('ok', "Đã thêm chủ đề '{$topic->name}' thành công!");
    }

    /**
     * Cập nhật chủ đề
     */
    public function update(SaveTopicRequest $request, Topic $topic): RedirectResponse
    {
        $data = $request->validated();
        $topic->update($data);

        return redirect()->route('admin.questions.studio', [
            'grade' => $topic->level->grade,
            'topic' => $topic->id,
        ])->with('ok', "Đã cập nhật chủ đề '{$topic->name}'!");
    }

    /**
     * Xóa chủ đề và các bài luyện tập con
     */
    public function destroy(Topic $topic): RedirectResponse
    {
        $level = $topic->level;
        $topic->delete();

        return redirect()->route('admin.questions.studio', [
            'grade' => $level->grade,
        ])->with('ok', 'Đã xóa chủ đề và các dữ liệu liên quan.');
    }
}
