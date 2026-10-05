<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Classroom;
use App\Models\Level;
use App\Models\PracticeTest;
use App\Models\Program;
use App\Models\Question;
use App\Models\QuestionAsset;
use App\Models\QuestionOption;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Controller Quản trị Dữ liệu & Nghiệp vụ Tổng quát (Admin Management)
 *
 * Chức năng: Quản lý toàn bộ cấu trúc hệ thống gồm:
 * 1. Cấu trúc IC3: Chương trình (Programs), Khối lớp (Levels), Chủ đề (Topics).
 * 2. Bộ đề & Câu hỏi: Tạo, sửa, xóa Bộ đề (PracticeTests), Câu hỏi (Questions), Lựa chọn đáp án (Options) và Tệp đa phương tiện (Assets).
 * 3. Người dùng & Lớp học: Tạo, sửa, phân quyền tài khoản (Users) và Lớp học (Classrooms).
 */
class AdminManagementController extends Controller
{
    /**
     * Chuyển hướng hợp nhất về Cổng Quản trị tập trung (1 Sidebar duy nhất)
     */
    public function index(): RedirectResponse
    {
        $section = request('section');
        if ($section === 'bank') {
            return redirect()->route('admin.questions.studio');
        } elseif ($section === 'people') {
            return redirect()->to(route('admin.dashboard').'#nguoi-dung');
        } elseif ($section === 'classes') {
            return redirect()->to(route('admin.dashboard').'#lop-hoc');
        } else {
            return redirect()->to(route('admin.dashboard').'#cau-truc');
        }
    }

    // =========================================================================
    // 🗂️ QUẢN LÝ CHƯƠNG TRÌNH ĐÀO TẠO (PROGRAMS - VÍ DỤ: IC3 GS6 SPARK)
    // =========================================================================

    /**
     * Thêm mới một Chương trình đào tạo
     */
    public function storeProgram(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403, 'Chức năng này chỉ dành cho Quản trị viên Tổng.');

        $data = $request->validate([
            'name' => 'required|max:150',
            'slug' => 'nullable|max:160|unique:programs,slug',
            'description' => 'nullable',
            'accent' => 'nullable|max:20',
        ]);
        $baseSlug = ! empty($data['slug']) ? Str::slug($data['slug']) : Str::slug($data['name']);
        $slug = $baseSlug;
        $counter = 1;
        while (Program::where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }
        $data['slug'] = $slug;
        Program::create($data);

        return $this->ok('Đã thêm chương trình.');
    }

    /**
     * Cập nhật thông tin Chương trình đào tạo
     */
    public function updateProgram(Request $request, Program $program): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403, 'Chức năng này chỉ dành cho Quản trị viên Tổng.');

        $data = $request->validate([
            'name' => 'required|max:150',
            'slug' => ['nullable', 'max:160', Rule::unique('programs', 'slug')->ignore($program)],
            'description' => 'nullable',
            'accent' => 'nullable|max:20',
        ]);
        if (empty($data['slug'])) {
            unset($data['slug']);
        }
        $program->update($data);

        return $this->ok('Đã cập nhật chương trình.');
    }

    /**
     * Xóa Chương trình đào tạo (tự động xóa các Khối, Chủ đề, Đề thi bên trong)
     */
    public function destroyProgram(Program $program): RedirectResponse
    {
        abort_unless(request()->user()?->isAdmin(), 403, 'Chức năng này chỉ dành cho Quản trị viên Tổng.');

        $program->delete();

        return $this->ok('Đã xóa chương trình và dữ liệu con.');
    }

    // =========================================================================
    // 🏫 QUẢN LÝ KHỐI LỚP / CẤP ĐỘ (LEVELS - VÍ DỤ: KHỐI 3, KHỐI 4, KHỐI 5)
    // =========================================================================

    /**
     * Thêm mới Khối lớp / Cấp độ
     */
    public function storeLevel(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403, 'Chức năng này chỉ dành cho Quản trị viên Tổng.');

        $data = $request->validate([
            'program_id' => 'required|exists:programs,id',
            'name' => 'required|max:150',
            'slug' => 'nullable|max:160',
            'grade' => 'required|integer|between:1,12',
            'position' => 'nullable|integer|min:0',
        ]);
        $baseSlug = ! empty($data['slug']) ? Str::slug($data['slug']) : Str::slug($data['name']);
        $slug = $baseSlug;
        $counter = 1;
        while (Level::where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }
        $data['slug'] = $slug;
        Level::create($data);

        return $this->ok('Đã thêm khối lớp mới thành công.');
    }

    /**
     * Cập nhật thông tin Khối lớp
     */
    public function updateLevel(Request $request, Level $level): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403, 'Chức năng này chỉ dành cho Quản trị viên Tổng.');

        $data = $request->validate([
            'program_id' => 'required|exists:programs,id',
            'name' => 'required|max:150',
            'slug' => 'nullable|max:160',
            'grade' => 'required|integer|between:1,12',
            'position' => 'nullable|integer|min:0',
        ]);
        if (empty($data['slug'])) {
            unset($data['slug']);
        }
        $level->update($data);

        return $this->ok('Đã cập nhật khối.');
    }

    /**
     * Xóa Khối lớp
     */
    public function destroyLevel(Level $level): RedirectResponse
    {
        abort_unless(request()->user()?->isAdmin(), 403, 'Chức năng này chỉ dành cho Quản trị viên Tổng.');

        $level->delete();

        return $this->ok('Đã xóa khối và dữ liệu con.');
    }

    // =========================================================================
    // 📖 QUẢN LÝ CHỦ ĐỀ BÀI HỌC (TOPICS)
    // =========================================================================

    /**
     * Thêm mới Chủ đề bài học
     */
    public function storeTopic(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'level_id' => 'required|exists:levels,id',
            'name' => 'required|max:150',
            'slug' => 'nullable|max:160',
            'description' => 'nullable',
            'icon' => 'nullable|max:50',
            'position' => 'nullable|integer|min:0',
        ]);

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

        if (!isset($data['position']) || $data['position'] === null) {
            $maxPos = Topic::where('level_id', $data['level_id'])->max('position');
            $data['position'] = ($maxPos !== null) ? ((int) $maxPos + 1) : 1;
        }

        $data['icon'] = !empty($data['icon']) ? $data['icon'] : 'sparkles';

        try {
            Topic::create($data);
            return $this->ok('Đã thêm chủ đề.');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Lỗi khi thêm chủ đề AdminManagement: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Có lỗi khi tạo chủ đề: ' . $e->getMessage());
        }
    }

    /**
     * Cập nhật thông tin Chủ đề
     */
    public function updateTopic(Request $request, Topic $topic): RedirectResponse
    {
        $data = $request->validate([
            'level_id' => 'required|exists:levels,id',
            'name' => 'required|max:150',
            'slug' => 'required|max:160',
            'description' => 'nullable',
            'icon' => 'nullable|max:50',
            'position' => 'nullable|integer|min:1',
        ]);

        $baseSlug = !empty($data['slug']) ? Str::slug($data['slug']) : Str::slug($data['name']);
        $slug = $baseSlug ?: 'chu-de';
        $counter = 1;
        while (Topic::where('level_id', $topic->level_id)->where('slug', $slug)->where('id', '!=', $topic->id)->exists()) {
            $counter++;
            $slug = "{$baseSlug}-{$counter}";
        }
        $data['slug'] = $slug;

        if (empty($data['icon'])) {
            $data['icon'] = $topic->icon ?: 'sparkles';
        }

        try {
            $topic->update($data);
            return $this->ok('Đã cập nhật chủ đề.');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Lỗi khi cập nhật chủ đề AdminManagement: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Có lỗi khi cập nhật chủ đề: ' . $e->getMessage());
        }
    }

    /**
     * Xóa Chủ đề bài học
     */
    public function destroyTopic(Topic $topic): RedirectResponse
    {
        try {
            $topic->delete();
            return $this->ok('Đã xóa chủ đề và dữ liệu con.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Có lỗi khi xóa chủ đề: ' . $e->getMessage());
        }
    }

    // =========================================================================
    // 📝 QUẢN LÝ BỘ ĐỀ THI / BÀI LUYỆN (PRACTICE TESTS)
    // =========================================================================

    /**
     * Tạo mới một Bộ đề thi luyện tập
     */
    public function storeTest(Request $request): RedirectResponse
    {
        $data = $this->testData($request);
        $topic = Topic::findOrFail($data['topic_id']);

        $data['level_id'] = $topic->level_id;
        $data['is_mock'] = false;

        $baseSlug = !empty($data['slug']) ? Str::slug($data['slug']) : (Str::slug($data['name']) ?: 'bai-luyen');
        $slug = $baseSlug;
        $counter = 1;
        while (PracticeTest::where('topic_id', $topic->id)->where('slug', $slug)->exists()) {
            $counter++;
            $slug = "{$baseSlug}-{$counter}";
        }
        $data['slug'] = $slug;

        if (!isset($data['position']) || $data['position'] === null) {
            $maxPos = PracticeTest::where('topic_id', $topic->id)->max('position');
            $data['position'] = ($maxPos !== null) ? ((int) $maxPos + 1) : 0;
        }

        $data['duration_minutes'] = isset($data['duration_minutes']) && $data['duration_minutes'] !== null ? (int) $data['duration_minutes'] : 20;
        $data['pass_score'] = isset($data['pass_score']) && $data['pass_score'] !== null ? (int) $data['pass_score'] : 700;
        $data['max_score'] = isset($data['max_score']) && $data['max_score'] !== null ? (int) $data['max_score'] : 1000;
        $data['question_count'] = 0;

        try {
            PracticeTest::create($data);
            return $this->ok('Đã thêm bộ đề.');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Lỗi khi thêm bộ đề AdminManagement: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Có lỗi khi tạo bộ đề: ' . $e->getMessage());
        }
    }

    /**
     * Cập nhật thông tin Bộ đề thi
     */
    public function updateTest(Request $request, PracticeTest $practiceTest): RedirectResponse
    {
        $data = $this->testData($request, $practiceTest);
        $topicId = $data['topic_id'] ?? $practiceTest->topic_id;

        $baseSlug = !empty($data['slug']) ? Str::slug($data['slug']) : Str::slug($data['name']);
        $slug = $baseSlug ?: 'bai-luyen';
        $counter = 1;
        while (PracticeTest::where('topic_id', $topicId)->where('slug', $slug)->where('id', '!=', $practiceTest->id)->exists()) {
            $counter++;
            $slug = "{$baseSlug}-{$counter}";
        }
        $data['slug'] = $slug;

        try {
            $practiceTest->update($data);
            return $this->ok('Đã cập nhật bộ đề.');
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', 'Có lỗi khi cập nhật bộ đề: ' . $e->getMessage());
        }
    }

    /**
     * Xóa Bộ đề thi
     */
    public function destroyTest(PracticeTest $practiceTest): RedirectResponse
    {
        try {
            $practiceTest->delete();
            return $this->ok('Đã xóa bộ đề và câu hỏi.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Có lỗi khi xóa bộ đề: ' . $e->getMessage());
        }
    }

    // =========================================================================
    // 🎯 QUẢN LÝ CÂU HỎI (QUESTIONS)
    // =========================================================================

    /**
     * Thêm nhanh câu hỏi vào bộ đề
     */
    public function storeQuestion(Request $request): RedirectResponse
    {
        $data = $this->questionData($request);
        $test = PracticeTest::findOrFail($data['practice_test_id']);

        $reqPos = $request->integer('position', (int) $test->questions()->max('position') + 1);
        while ($test->questions()->where('position', $reqPos)->exists()) {
            $reqPos++;
        }
        $data['position'] = $reqPos;

        try {
            $test->questions()->create($data);
            $test->update(['question_count' => $test->questions()->count()]);

            return $this->ok('Đã thêm câu hỏi.');
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', 'Có lỗi khi thêm câu hỏi: ' . $e->getMessage());
        }
    }

    /**
     * Hiển thị giao diện chỉnh sửa câu hỏi chi tiết
     */
    public function editQuestion(Question $question): View
    {
        return view('admin.question-edit', compact('question'))
            ->with('question', $question->load(['practiceTest.topic.level', 'options', 'assets']));
    }

    /**
     * Cập nhật nội dung câu hỏi
     */
    public function updateQuestion(Request $request, Question $question): RedirectResponse
    {
        $data = $this->questionData($request, false);
        $question->update($data);

        return back()->with('ok', 'Đã cập nhật câu hỏi.');
    }

    /**
     * Xóa câu hỏi khỏi bộ đề
     */
    public function destroyQuestion(Question $question): RedirectResponse
    {
        $test = $question->practiceTest;
        $question->delete();
        $test->update(['question_count' => $test->questions()->count()]);

        return $this->ok('Đã xóa câu hỏi.');
    }

    // =========================================================================
    // 🔘 QUẢN LÝ CÁC PHƯƠNG ÁN / ĐÁP ÁN LỰA CHỌN (OPTIONS)
    // =========================================================================

    /**
     * Thêm phương án lựa chọn cho câu hỏi
     */
    public function storeOption(Request $request, Question $question): RedirectResponse
    {
        $data = $request->validate([
            'content' => 'nullable',
            'image_path' => 'nullable|max:500',
            'is_correct' => 'nullable|boolean',
            'position' => 'nullable|integer|min:0',
        ]);
        $data['is_correct'] = $request->boolean('is_correct');
        $question->options()->create($data);

        return back()->with('ok', 'Đã thêm lựa chọn.');
    }

    /**
     * Cập nhật phương án lựa chọn
     */
    public function updateOption(Request $request, QuestionOption $option): RedirectResponse
    {
        $data = $request->validate([
            'content' => 'nullable',
            'image_path' => 'nullable|max:500',
            'is_correct' => 'nullable|boolean',
            'position' => 'nullable|integer|min:0',
        ]);
        $data['is_correct'] = $request->boolean('is_correct');
        $option->update($data);

        return back()->with('ok', 'Đã cập nhật lựa chọn.');
    }

    /**
     * Xóa phương án lựa chọn
     */
    public function destroyOption(QuestionOption $option): RedirectResponse
    {
        $option->delete();

        return back()->with('ok', 'Đã xóa lựa chọn.');
    }

    // =========================================================================
    // 🖼️ QUẢN LÝ TÀI NGUYÊN ĐA PHƯƠNG TIỆN (QUESTION ASSETS: HÌNH ẢNH / ÂM THANH)
    // =========================================================================

    /**
     * Tải lên file hình ảnh/âm thanh/tài liệu cho câu hỏi
     */
    public function storeAsset(Request $request, Question $question): RedirectResponse
    {
        $request->validate([
            'file' => 'required|file|max:20480',
            'kind' => 'required|in:image,audio,video,document',
        ]);
        $file = $request->file('file');
        $path = $file->store('question-assets', 'public');
        \App\Models\StoredFile::remember($path);
        $question->assets()->create([
            'kind' => $request->kind,
            'path' => Storage::url($path),
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
        ]);

        return back()->with('ok', 'Đã tải tài nguyên lên.');
    }

    /**
     * Xóa file tài nguyên đính kèm khỏi ổ cứng và cơ sở dữ liệu
     */
    public function destroyAsset(QuestionAsset $asset): RedirectResponse
    {
        if (Str::startsWith($asset->path, '/storage/')) {
            Storage::disk('public')->delete(Str::after($asset->path, '/storage/'));
            \App\Models\StoredFile::forget($asset->path);
        }
        $asset->delete();

        return back()->with('ok', 'Đã xóa tài nguyên.');
    }

    // =========================================================================
    // 👨‍🎓 QUẢN LÝ TÀI KHOẢN NGƯỜI DÙNG (USERS: HỌC SINH & ADMIN)
    // =========================================================================

    /**
     * Tạo mới tài khoản (Học sinh hoặc Quản trị viên)
     */
    public function storeUser(Request $request): RedirectResponse
    {
        $data = $this->userData($request);
        User::create($data);

        return $this->ok('Đã thêm người dùng.');
    }

    /**
     * Cập nhật thông tin tài khoản
     */
    public function updateUser(Request $request, User $user): RedirectResponse
    {
        $data = $this->userData($request, $user);
        if (! $data['password']) {
            unset($data['password']);
        }
        $user->update($data);

        return $this->ok('Đã cập nhật người dùng.');
    }

    /**
     * Xóa tài khoản người dùng
     */
    public function destroyUser(Request $request, User $user): RedirectResponse
    {
        abort_if($request->user()->is($user), 422, 'Không thể tự xóa tài khoản đang đăng nhập.');
        $user->delete();

        return $this->ok('Đã xóa người dùng.');
    }

    // =========================================================================
    // 🏫 QUẢN LÝ LỚP HỌC (CLASSROOMS)
    // =========================================================================

    /**
     * Cập nhật thông tin lớp học
     */
    public function updateClassroom(Request $request, Classroom $classroom): RedirectResponse
    {
        $classroom->update($request->validate([
            'name' => 'required|max:50',
            'grade' => 'required|integer|between:1,12',
            'school_year' => 'required|max:20',
            'teacher_id' => 'nullable|exists:users,id',
        ]));

        return $this->ok('Đã cập nhật lớp.');
    }

    /**
     * Xóa lớp học
     */
    public function destroyClassroom(Classroom $classroom): RedirectResponse
    {
        $classroom->delete();

        return $this->ok('Đã xóa lớp.');
    }

    // =========================================================================
    // 🛠️ CÁC HÀM TIỆN ÍCH NỘI BỘ (HELPER METHODS)
    // =========================================================================

    /**
     * Validate dữ liệu đầu vào cho Bộ đề thi
     */
    private function testData(Request $request, ?PracticeTest $test = null): array
    {
        return $request->validate([
            'topic_id' => 'required|exists:topics,id',
            'name' => 'required|max:150',
            'slug' => ['nullable', 'max:180', Rule::unique('practice_tests')->ignore($test)],
            'access_code' => 'nullable|max:100',
            'duration_minutes' => 'nullable|integer|min:0',
            'pass_score' => 'nullable|integer|between:0,1000',
            'max_score' => 'nullable|integer|min:1',
            'position' => 'nullable|integer|min:0',
            'is_published' => 'nullable|boolean',
        ]) + ['is_published' => $request->boolean('is_published')];
    }

    /**
     * Validate dữ liệu đầu vào cho Câu hỏi
     */
    private function questionData(Request $request, bool $withTest = true): array
    {
        $rules = [
            'type' => 'required|max:60',
            'title' => 'nullable',
            'position' => 'nullable|integer|min:0',
            'points' => 'nullable|integer|min:1',
            'is_published' => 'nullable|boolean',
        ];
        if ($withTest) {
            $rules['practice_test_id'] = 'required|exists:practice_tests,id';
        }
        $data = $request->validate($rules);
        $data['is_published'] = $request->boolean('is_published');

        return $data;
    }

    /**
     * Validate dữ liệu người dùng
     */
    private function userData(Request $request, ?User $user = null): array
    {
        return $request->validate([
            'name' => 'required|max:100',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user)],
            'student_code' => ['nullable', 'max:30', Rule::unique('users')->ignore($user)],
            'password' => [$user ? 'nullable' : 'required', 'nullable', 'min:6'],
            'role' => ['required', Rule::enum(UserRole::class)],
            'classroom_id' => 'nullable|exists:classrooms,id',
        ]);
    }

    /**
     * Phản hồi thông báo thành công về trang trước
     */
    private function ok(string $message): RedirectResponse
    {
        return back()->with('ok', $message);
    }
}
