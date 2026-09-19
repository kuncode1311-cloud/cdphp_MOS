<?php

namespace App\Services;

use App\Models\PracticeTest;
use App\Models\Question;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Class QuestionService
 *
 * Tầng Service (Domain Logic) trong mô hình MVC/OOP.
 * Đóng gói nghiệp vụ tạo, sửa, xóa và đồng bộ dữ liệu câu hỏi nội bộ.
 * Giúp Controller giữ đúng vai trò điều hướng (Clean Controller), không bị phình to.
 */
class QuestionService
{
    protected QuestionAssetService $assetService;

    public function __construct(QuestionAssetService $assetService)
    {
        $this->assetService = $assetService;
    }

    /**
     * Tạo mới một câu hỏi kèm theo các lựa chọn (options) và tài nguyên (nếu có)
     * Toàn bộ thao tác được bọc trong DB Transaction để đảm bảo tính toàn vẹn dữ liệu.
     *
     * @param  PracticeTest  $test  Bộ đề chứa câu hỏi
     * @param  array  $data  Dữ liệu câu hỏi (title, type, points, is_published...)
     * @param  array  $options  Danh sách các đáp án lựa chọn
     * @param  UploadedFile|null  $assetFile  File ảnh/media tải lên (nếu có)
     * @param  string  $assetKind  Loại media ('image', 'audio', 'video', 'document')
     */
    public function createQuestion(
        PracticeTest $test,
        array $data,
        array $options = [],
        ?UploadedFile $assetFile = null,
        string $assetKind = 'image'
    ): Question {
        // Database cùng lưu hoặc cùng rollback; file upload không được rollback theo.
        return DB::transaction(function () use ($test, $data, $options, $assetFile, $assetKind) {
            // 1. Tự động tính thứ tự tiếp theo nếu chưa có
            $position = $data['position'] ?? ((int) $test->questions()->max('position') + 1);

            // 3. Tạo bản ghi Question
            /** @var Question $question */
            $question = $test->questions()->create([
                'title' => $data['title'] ?? '',
                'type' => $data['type'] ?? 'MultipleChoice',
                'configuration' => $data['configuration'] ?? [],
                'position' => $position,
                'points' => $data['points'] ?? 1,
                'is_published' => (bool) ($data['is_published'] ?? true),
            ]);

            // 4. Lưu danh sách các lựa chọn (QuestionOptions)
            $this->syncOptions($question, $options);

            // 5. Lưu file đính kèm nếu người dùng tải lên
            if ($assetFile instanceof UploadedFile) {
                $this->assetService->uploadAsset($question, $assetFile, $assetKind);
            }

            return $question->load(['options', 'assets', 'practiceTest']);
        });
    }

    /**
     * Cập nhật toàn diện câu hỏi (Nội dung, loại câu, điểm, danh sách đáp án, file)
     */
    public function updateQuestion(
        Question $question,
        array $data,
        array $options = [],
        ?UploadedFile $assetFile = null,
        string $assetKind = 'image'
    ): Question {
        return DB::transaction(function () use ($question, $data, $options, $assetFile, $assetKind) {
            // 1. Cập nhật các trường cơ bản của câu hỏi
            $question->update([
                'title' => $data['title'] ?? $question->title,
                'type' => $data['type'] ?? $question->type,
                'position' => isset($data['position']) ? (int) $data['position'] : $question->position,
                'points' => isset($data['points']) ? (int) $data['points'] : $question->points,
                'is_published' => isset($data['is_published']) ? (bool) $data['is_published'] : $question->is_published,
            ]);

            // 2. Đồng bộ danh sách options thuộc schema nội bộ.
            $this->syncOptions($question, $options);

            // 3. Tải lên file mới nếu có
            if ($assetFile instanceof UploadedFile) {
                $this->assetService->uploadAsset($question, $assetFile, $assetKind);
            }

            return $question->fresh(['options', 'assets', 'practiceTest']);
        });
    }

    /**
     * Xóa câu hỏi và toàn bộ dữ liệu phụ thuộc (options, file assets)
     */
    public function deleteQuestion(Question $question): bool
    {
        return DB::transaction(function () use ($question) {
            // Xóa toàn bộ file vật lý của câu hỏi
            foreach ($question->assets as $asset) {
                $this->assetService->deleteAsset($asset);
            }

            return (bool) $question->delete();
        });
    }

    /**
     * Đồng bộ danh sách lựa chọn và metadata nghiệp vụ nội bộ.
     *
     * @param  array  $options  Mảng các options: [['content' => '...', 'is_correct' => true/false, ...]]
     */
    public function syncOptions(Question $question, array $options): void
    {
        // 1. Xóa các options cũ và chèn lại danh sách mới
        $question->options()->delete();

        $type = $question->type;
        $mctChoices = $type === 'MultipleChoiceText'
            ? collect($options)->pluck('right')->filter()->unique()->values()->all()
            : [];

        foreach ($options as $index => $opt) {
            $imageFile = $opt['image_file'] ?? null;
            $imagePath = $opt['image_path'] ?? null;

            // Nếu người dùng upload file ảnh mới cho option này
            if ($imageFile instanceof UploadedFile) {
                $storedPath = $imageFile->store('options', 'public');
                $imagePath = Storage::url($storedPath);
            }

            if ($type === 'Matching') {
                $left = trim($opt['left'] ?? '');
                $right = trim($opt['right'] ?? '');
                $rawContent = trim($opt['content'] ?? '');

                if ($left === '' && $right === '' && $rawContent !== '') {
                    if (str_contains($rawContent, ':::')) {
                        [$left, $right] = explode(':::', $rawContent, 2);
                        $left = trim($left);
                        $right = trim($right);
                    } else {
                        $left = $rawContent;
                    }
                }

                if ($left === '' && $right === '') {
                    continue;
                }

                $combinedContent = "{$left} ::: {$right}";

                $question->options()->create([
                    'content' => $combinedContent,
                    'image_path' => $imagePath,
                    'is_correct' => true,
                    'position' => $index,
                    'metadata' => [
                        'left' => $left,
                        'right' => $right,
                    ],
                ]);
            } elseif ($type === 'Sequence') {
                $content = trim($opt['content'] ?? '');
                if ($content === '') {
                    continue;
                }

                $question->options()->create([
                    'content' => $content,
                    'image_path' => $imagePath,
                    'is_correct' => true,
                    'position' => $index,
                    'metadata' => [
                        'correct_position' => $index,
                    ],
                ]);
            } elseif ($type === 'Hotspot') {
                $content = trim($opt['content'] ?? ('Vùng '.($index + 1)));
                $isCorrect = filter_var($opt['is_correct'] ?? false, FILTER_VALIDATE_BOOLEAN);
                $rect = $opt['rect'] ?? ['x' => 0, 'y' => 0, 'w' => 1000, 'h' => 1000];

                $question->options()->create([
                    'content' => $content,
                    'image_path' => $imagePath,
                    'is_correct' => $isCorrect,
                    'position' => $index,
                    'metadata' => [
                        'rect' => $rect,
                    ],
                ]);

            } elseif ($type === 'MultipleChoiceText') {
                $left = trim($opt['left'] ?? $opt['content'] ?? '');
                $right = trim($opt['right'] ?? '');
                if ($left === '') {
                    continue;
                }
                $availableOptions = $opt['available_options'] ?? $mctChoices;
                $correctIndex = array_search($right, $availableOptions, true);

                $question->options()->create([
                    'content' => $left,
                    'image_path' => $imagePath,
                    'is_correct' => true,
                    'position' => $index,
                    'metadata' => [
                        'left' => $left,
                        'right' => $right,
                        'available_options' => $availableOptions,
                        'correct_index' => $correctIndex === false ? null : $correctIndex,
                    ],
                ]);
            } else {
                // Single Choice / Multiple Response
                $content = trim($opt['content'] ?? '');
                if ($content === '' && empty($imagePath)) {
                    continue; // Bỏ qua đáp án rỗng
                }

                $isCorrect = filter_var($opt['is_correct'] ?? false, FILTER_VALIDATE_BOOLEAN);

                $question->options()->create([
                    'content' => $content,
                    'image_path' => $imagePath,
                    'is_correct' => $isCorrect,
                    'position' => $index,
                    'metadata' => [],
                ]);
            }
        }
    }
}
