<?php

namespace Tests\Feature;

use App\Models\Level;
use App\Models\PracticeTest;
use App\Models\Program;
use App\Models\Question;
use App\Models\QuestionAsset;
use App\Models\QuestionOption;
use App\Models\StudentMistake;
use App\Models\TestAttempt;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Làm câu hỏi ngay trong cuộc trò chuyện bằng giọng nói: câu hỏi thật từ ngân hàng đề, đúng khối được học,
 * không lộ đáp án, hiểu nhiều cách nói đáp án, chấm theo cơ sở dữ liệu, chỉ luyện tập (không cộng sao).
 */
class VoiceQuizTest extends TestCase
{
    use RefreshDatabase;

    private Level $level;
    private Level $otherLevel;
    private Topic $topicA;
    private Topic $topicB;
    private User $student;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        config(['services.question_ai.base_url' => 'https://router.test/v1', 'services.question_ai.model' => 'm', 'services.question_ai.api_key' => 'k']);

        $program = Program::create(['name' => 'IC3 GS6', 'slug' => 'ic3-gs6-thu']);
        $this->level = Level::create(['program_id' => $program->id, 'name' => 'Khối 3', 'slug' => 'khoi-3-vq', 'grade' => 3, 'position' => 1]);
        $this->otherLevel = Level::create(['program_id' => $program->id, 'name' => 'Khối 5', 'slug' => 'khoi-5-vq', 'grade' => 5, 'position' => 2]);
        $this->topicA = Topic::create(['level_id' => $this->level->id, 'name' => 'Căn bản về công nghệ', 'slug' => 'can-ban-vq', 'position' => 1]);
        $this->topicB = Topic::create(['level_id' => $this->level->id, 'name' => 'Công dân số', 'slug' => 'cong-dan-so-vq', 'position' => 2]);

        $this->student = User::factory()->create([
            'role' => 'student', 'created_by' => null, 'status' => 'active', 'expires_at' => now()->addDays(30),
            'max_students' => 1, 'ai_assistant_until' => now()->addDays(10),
        ]);
        $this->student->accessibleLevels()->sync([$this->level->id]);
    }

    private function test(Topic $topic, string $slug, bool $published = true): PracticeTest
    {
        return PracticeTest::create([
            'topic_id' => $topic->id, 'name' => 'Bài ' . $slug, 'slug' => $slug, 'duration_minutes' => 10,
            'pass_score' => 700, 'max_score' => 1000, 'is_published' => $published, 'question_count' => 1,
        ]);
    }

    /** @param  array<int, string>  $options  Nội dung các đáp án theo thứ tự A, B, C...; @param array<int,int> $correct vị trí đúng */
    private function question(PracticeTest $test, string $title, array $options, array $correct, string $type = 'MultipleChoice'): Question
    {
        static $position = 0;
        $q = Question::create(['practice_test_id' => $test->id, 'title' => $title, 'type' => $type, 'points' => 1, 'is_published' => true, 'position' => ++$position]);
        foreach ($options as $i => $content) {
            QuestionOption::create(['question_id' => $q->id, 'content' => $content, 'position' => $i, 'is_correct' => in_array($i, $correct, true)]);
        }

        return $q;
    }

    private function fakeAi(string ...$replies): void
    {
        $seq = Http::sequence();
        foreach ($replies as $r) {
            $seq->push(['choices' => [['message' => ['content' => $r]]]]);
        }
        $seq->whenEmpty(Http::response(['choices' => [['message' => ['content' => 'Giỏi lắm em!']]]]));
        Http::fake(['router.test/*' => $seq]);
    }

    private function start(string $hint = '')
    {
        return $this->actingAs($this->student)->postJson(route('voice.quiz.start'), ['hint' => $hint]);
    }

    /** "A", "a và c": giống em bấm nút chọn (gửi thẳng chữ cái); câu nói khác: nhờ AI hiểu em chọn gì */
    private function answer(string $text)
    {
        $body = ['text' => $text];
        if (preg_match('/^[A-Fa-f](\s*(,|và)\s*[A-Fa-f])*$/u', trim($text))) {
            preg_match_all('/[A-Fa-f]/', $text, $m);
            $body['keys'] = array_map('strtoupper', $m[0]);
        }

        return $this->actingAs($this->student)->postJson(route('voice.quiz.answer'), $body);
    }

    public function test_start_returns_real_question_without_revealing_the_answer(): void
    {
        $q = $this->question($this->test($this->topicA, 'a1'), 'Thiết bị nào có bàn di chuột?', ['Laptop', 'Desktop', 'Tablet', 'Điện thoại'], [0]);

        $res = $this->start()->assertOk()->assertJson(['ok' => true]);

        $this->assertSame($q->id, $res->json('quiz.question.id'));
        $this->assertSame(['A', 'B', 'C', 'D'], array_column($res->json('quiz.question.options'), 'key'));
        $this->assertStringContainsString('Đáp án A: Laptop', $res->json('quiz.spoken'));
        $this->assertStringNotContainsString('correct', strtolower($res->getContent()));
        $this->assertStringNotContainsString('is_correct', $res->getContent());
    }

    public function test_hint_selects_matching_topic(): void
    {
        $this->question($this->test($this->topicA, 'a1'), 'Câu về công nghệ?', ['x1', 'x2'], [0]);
        $b = $this->question($this->test($this->topicB, 'b1'), 'Câu về công dân số?', ['y1', 'y2'], [1]);

        $this->start('Công dân số')->assertOk()->assertJsonPath('quiz.question.id', $b->id);
        $this->start('chủ đề công dân số')->assertOk()->assertJsonPath('quiz.question.id', $b->id);
    }

    public function test_only_levels_the_student_may_learn_are_used(): void
    {
        $topicOther = Topic::create(['level_id' => $this->otherLevel->id, 'name' => 'Chủ đề khối 5', 'slug' => 'k5-vq', 'position' => 1]);
        $this->question($this->test($topicOther, 'k5'), 'Câu khối 5?', ['a', 'b'], [0]);

        $this->start()->assertStatus(404)->assertJson(['ok' => false]);
    }

    public function test_unpublished_audio_and_unsupported_types_are_skipped(): void
    {
        $this->question($this->test($this->topicA, 'an', false), 'Bài ẩn?', ['a', 'b'], [0]);
        $withAudio = $this->question($this->test($this->topicA, 'am'), 'Nghe và chọn?', ['a', 'b'], [0]);
        QuestionAsset::create(['question_id' => $withAudio->id, 'kind' => 'audio', 'path' => 'x.mp3']);
        $this->question($this->test($this->topicA, 'seq'), 'Sắp xếp?', ['a', 'b'], [0], 'Sequence');
        $this->question($this->test($this->topicA, 'hot'), 'Bấm vào hình?', ['a', 'b'], [0], 'Hotspot');

        $this->start()->assertStatus(404);
    }

    public function test_question_with_an_image_is_shown_with_the_picture(): void
    {
        $q = $this->question($this->test($this->topicA, 'hinh'), 'Hình nào là Laptop?', ['Hình một', 'Hình hai'], [0]);
        QuestionAsset::create(['question_id' => $q->id, 'kind' => 'image', 'path' => '/storage/question-assets/imported/q4-1.png']);

        $res = $this->start()->assertOk();

        $this->assertSame(['/storage/question-assets/imported/q4-1.png'], $res->json('quiz.question.images'));
        $this->assertStringContainsString('Em nhìn hình trên màn hình nhé', $res->json('quiz.spoken'));
    }

    public function test_options_that_are_only_pictures_are_supported(): void
    {
        $q = Question::create(['practice_test_id' => $this->test($this->topicA, 'anh')->id, 'title' => 'Hình nào là Laptop?', 'type' => 'MultipleChoice', 'points' => 1, 'is_published' => true, 'position' => 900]);
        QuestionOption::create(['question_id' => $q->id, 'content' => '', 'image_path' => '/storage/a.png', 'position' => 0, 'is_correct' => true]);
        QuestionOption::create(['question_id' => $q->id, 'content' => '', 'image_path' => '/storage/b.png', 'position' => 1, 'is_correct' => false]);

        $res = $this->start()->assertOk();

        $this->assertSame('/storage/a.png', $res->json('quiz.question.options.0.image'));
        $this->assertStringContainsString('hình A trên màn hình', $res->json('quiz.spoken'));
    }

    private function classifyQuestion(): Question
    {
        $q = $this->question($this->test($this->topicA, 'pl'), 'Mỗi tùy chọn là phần cứng hay phần mềm?', ['Máy in', 'Email', 'Chuột'], []);
        $meta = [
            ['correct_index' => 0, 'available_options' => ['Phần cứng', 'Phần mềm']],
            ['correct_index' => 1, 'available_options' => ['Phần cứng', 'Phần mềm']],
            ['correct_index' => 0, 'available_options' => ['Phần cứng', 'Phần mềm']],
        ];
        foreach ($q->options as $i => $option) {
            $option->update(['metadata' => $meta[$i]]);
        }
        $q->update(['type' => 'MultipleChoiceText']);

        return $q;
    }

    private function matchingQuestion(): Question
    {
        $q = $this->question($this->test($this->topicA, 'gn'), 'Ghép nối đúng?', ['x', 'y', 'z'], []);
        $pairs = [['Hướng dẫn cho máy tính', 'Chương trình'], ['Bộ phận chạm được', 'Phần cứng'], ['Thiết bị cầm tay', 'Điện thoại']];
        foreach ($q->options as $i => $option) {
            $option->update(['content' => $pairs[$i][0], 'metadata' => ['left' => $pairs[$i][0], 'right' => $pairs[$i][1]]]);
        }
        $q->update(['type' => 'Matching']);

        return $q;
    }

    public function test_classify_question_is_shown_on_screen_without_answers_and_graded_from_screen_choices(): void
    {
        $this->classifyQuestion();
        $this->fakeAi('Chính xác! Máy in và chuột là phần cứng, email là phần mềm.');

        $res = $this->start()->assertOk();
        $this->assertSame('MultipleChoiceText', $res->json('quiz.question.type'));
        $this->assertCount(3, $res->json('quiz.question.items'));
        $this->assertSame(['Phần cứng', 'Phần mềm'], $res->json('quiz.question.items.0.choices'));
        $this->assertStringNotContainsString('correct_index', $res->getContent());
        $this->assertStringContainsString('trực tiếp trên màn hình', $res->json('quiz.spoken'));

        $this->postJson(route('voice.quiz.answer'), ['text' => 'Trả lời trên màn hình', 'answers' => [0, 1, 0]])
            ->assertOk()
            ->assertJson(['status' => 'graded', 'correct' => true, 'wrong_items' => []]);
    }

    public function test_classify_wrong_choices_report_which_rows_are_wrong(): void
    {
        $this->classifyQuestion();
        $this->fakeAi('Chưa đúng rồi em.');
        $this->start();

        $res = $this->actingAs($this->student)->postJson(route('voice.quiz.answer'), ['text' => 'Trả lời', 'answers' => [1, 1, 1]])->assertOk();

        $res->assertJson(['status' => 'graded', 'correct' => false, 'wrong_items' => [0, 2], 'solution' => [0, 1, 0]]);
    }

    public function test_matching_is_graded_and_right_column_is_not_in_same_order_as_left(): void
    {
        $this->matchingQuestion();
        $this->fakeAi('Đúng rồi em!');

        $res = $this->start()->assertOk();
        $this->assertSame('Matching', $res->json('quiz.question.type'));
        $left = $res->json('quiz.question.left');
        $right = $res->json('quiz.question.right');
        $this->assertCount(3, $left);
        $this->assertNotSame(array_column($left, 'id'), array_column($right, 'id'));

        $this->actingAs($this->student)->postJson(route('voice.quiz.answer'), ['text' => 'ok', 'answers' => [0, 1, 2]])
            ->assertOk()
            ->assertJson(['status' => 'graded', 'correct' => true]);
    }

    public function test_matching_wrong_pairs_are_marked(): void
    {
        $this->matchingQuestion();
        $this->fakeAi('Chưa đúng.');
        $this->start();

        $this->actingAs($this->student)->postJson(route('voice.quiz.answer'), ['text' => 'ok', 'answers' => [1, 0, 2]])
            ->assertOk()
            ->assertJson(['status' => 'graded', 'correct' => false, 'wrong_items' => [0, 1]]);
    }

    public function test_on_screen_question_needs_complete_valid_answers(): void
    {
        $this->classifyQuestion();
        $this->start();

        // Thiếu dòng, ngoài phạm vi, hoặc nói miệng thay vì làm trên màn hình: nhắc em làm trên màn hình, câu vẫn đang chờ
        $this->actingAs($this->student)->postJson(route('voice.quiz.answer'), ['text' => 'a', 'answers' => [0, 1]])->assertOk()->assertJson(['status' => 'unclear']);
        $this->actingAs($this->student)->postJson(route('voice.quiz.answer'), ['text' => 'a', 'answers' => [0, 1, 5]])->assertOk()->assertJson(['status' => 'unclear']);
        $this->actingAs($this->student)->postJson(route('voice.quiz.answer'), ['text' => 'đáp án A'])->assertOk()->assertJson(['status' => 'unclear']);

        $this->fakeAi('Đúng!');
        $this->actingAs($this->student)->postJson(route('voice.quiz.answer'), ['text' => 'ok', 'answers' => [0, 1, 0]])->assertOk()->assertJson(['status' => 'graded', 'correct' => true]);
    }

    public function test_ai_is_told_the_current_question_but_never_the_correct_answer_while_student_is_doing_it(): void
    {
        $this->question($this->test($this->topicA, 'a1'), 'Thiết bị nào có bàn di chuột?', ['Laptop', 'Desktop', 'Tablet'], [0]);
        $this->start();
        $this->fakeAi('Em thử nghĩ xem thiết bị nào mang theo được nhé.');

        $this->actingAs($this->student)->postJson(route('voice.reply'), ['text' => 'cô gợi ý giúp em với'])->assertOk();

        Http::assertSent(function ($request) {
            $system = $request['messages'][0]['content'] ?? '';

            return str_contains($system, 'CÂU HỎI EM ĐANG LÀM')
                && str_contains($system, 'Dạng câu: chọn MỘT đáp án')
                && str_contains($system, 'Thiết bị nào có bàn di chuột?')
                && str_contains($system, 'Đáp án A: Laptop')
                && str_contains($system, 'KHÔNG đoán hay lộ đáp án')
                && ! str_contains($system, 'is_correct');
        });
    }

    public function test_topic_question_goes_to_ai_with_only_real_published_topics(): void
    {
        $this->question($this->test($this->topicA, 'a1'), 'Câu A?', ['x', 'y'], [0]);
        $this->question($this->test($this->topicB, 'b1'), 'Câu B?', ['x', 'y'], [0]);
        // Chủ đề chưa có bài luyện nào và chủ đề chỉ có bài chưa xuất bản: không được đưa cho AI
        Topic::create(['level_id' => $this->level->id, 'name' => 'Chủ đề rỗng', 'slug' => 'rong-vq', 'position' => 3]);
        $hidden = Topic::create(['level_id' => $this->level->id, 'name' => 'Chủ đề ẩn', 'slug' => 'an-vq', 'position' => 4]);
        $this->test($hidden, 'an-vq-bai', false);
        $this->fakeAi("**Khối 3 có 2 chủ đề:**\n- Căn bản về công nghệ\n- Công dân số");

        $res = $this->actingAs($this->student)->postJson(route('voice.reply'), ['text' => 'Khối 3 có những chủ đề nào?'])->assertOk();

        Http::assertSentCount(1);
        $prompt = $this->sentPrompt();
        $this->assertStringContainsString('"chu_de_theo_khoi":{"Khối 3":["1. Căn bản về công nghệ","2. Công dân số"]}', $prompt);
        $this->assertStringContainsString('liệt kê ĐỦ và ĐÚNG tên', $prompt);
        $this->assertStringNotContainsString('Chủ đề rỗng', $prompt);
        $this->assertStringNotContainsString('Chủ đề ẩn', $prompt);
        // Gạch đầu dòng của AI được giữ để hiện đẹp
        $this->assertStringContainsString("- Căn bản về công nghệ\n- Công dân số", $res->json('display'));
    }

    public function test_topics_of_grades_the_student_cannot_learn_are_never_sent_to_ai(): void
    {
        $this->question($this->test($this->topicA, 'a1'), 'Câu?', ['x', 'y'], [0]);
        $locked = Topic::create(['level_id' => $this->otherLevel->id, 'name' => 'Chủ đề khối năm', 'slug' => 'k5-vq', 'position' => 1]);
        $this->question($this->test($locked, 'k5a'), 'Câu khối 5?', ['x', 'y'], [0]);
        $this->fakeAi('Em chưa được học khối 5 nhé.');

        $this->actingAs($this->student)->postJson(route('voice.reply'), ['text' => 'khối 5 có những chủ đề nào'])->assertOk();

        $this->assertStringNotContainsString('Chủ đề khối năm', $this->sentPrompt());
    }

    public function test_asking_to_practice_a_topic_starts_quiz_of_that_topic(): void
    {
        $this->question($this->test($this->topicA, 'a1'), 'Câu chủ đề A?', ['x', 'y'], [0]);
        $this->question($this->test($this->topicB, 'b1'), 'Câu chủ đề B?', ['x', 'y'], [0]);
        $this->fakeAi("Được chứ em!\n[[LUYEN: Công dân số]]");

        $res = $this->actingAs($this->student)->postJson(route('voice.reply'), ['text' => 'đố em vài câu công dân số'])->assertOk();

        Http::assertSentCount(1);
        $this->assertSame('Câu chủ đề B?', $res->json('quiz.question.text'));
    }

    public function test_stopping_the_quiz_makes_the_server_forget_the_question(): void
    {
        $this->question($this->test($this->topicA, 'a1'), 'Thiết bị nào có bàn di chuột?', ['Laptop', 'Desktop'], [0]);
        $this->start()->assertOk();
        $this->assertTrue(app(\App\Services\VoiceQuizService::class)->hasPending($this->student));

        $this->actingAs($this->student)->postJson(route('voice.quiz.cancel'))->assertOk()->assertJson(['ok' => true]);

        $this->assertFalse(app(\App\Services\VoiceQuizService::class)->hasPending($this->student));
        // Sau khi dừng, AI không còn được nhắc tới câu hỏi cũ và câu trả lời không còn đáp án chờ chấm
        $this->fakeAi('Dạ cô nghe đây.');
        $this->actingAs($this->student)->postJson(route('voice.reply'), ['text' => 'máy tính là gì vậy cô'])->assertOk();
        Http::assertSent(fn ($request) => ! str_contains($request['messages'][0]['content'] ?? '', 'EM ĐANG LÀM CÂU HỎI NÀY'));
        $this->answer('A')->assertJson(['status' => 'no_question']);
    }

    public function test_cancel_requires_ai_package(): void
    {
        $this->student->forceFill(['ai_assistant_until' => null])->save();

        $this->actingAs($this->student)->postJson(route('voice.quiz.cancel'))->assertStatus(403);
    }

    public function test_ai_offering_practice_does_not_start_a_quiz_until_student_agrees(): void
    {
        $this->question($this->test($this->topicA, 'a1'), 'Câu?', ['x', 'y'], [0]);
        // AI báo cáo kết quả rồi chỉ ĐỀ NGHỊ ôn, nhưng lỡ gắn dấu hiệu ra câu hỏi
        $this->fakeAi("Hôm nay em làm được hai mươi bài. Em có muốn ôn lại câu sai không nào?
[[LUYEN: cau sai]]");

        $res = $this->actingAs($this->student)->postJson(route('voice.reply'), ['text' => 'báo cáo kết quả học tập hôm nay'])->assertOk();

        $this->assertNull($res->json('quiz'));
        $this->assertStringNotContainsString('[[', $res->json('text'));
        $this->assertFalse(app(\App\Services\VoiceQuizService::class)->hasPending($this->student));
    }

    public function test_ai_is_told_not_to_start_quiz_for_results_questions(): void
    {
        $this->fakeAi('Em luyện tập rất tốt.');

        $this->actingAs($this->student)->postJson(route('voice.reply'), ['text' => 'kết quả luyện tập của em tuần này thế nào'])->assertOk();

        $this->assertStringContainsString('Người nói đang hỏi kết quả, thống kê thì KHÔNG gắn', $this->sentPrompt());
    }

    public function test_stats_card_and_quiz_never_come_together(): void
    {
        $this->question($this->test($this->topicA, 'a1'), 'Câu?', ['x', 'y'], [0]);
        $this->attempt($this->test($this->topicA, 'k1'), 900, '2026-09-20 08:00:00');
        Http::fake(['router.test/*' => Http::sequence()
            ->push($this->toolCall('hien_the_thong_ke', []))
            ->push(['choices' => [['message' => ['content' => "Em làm tốt lắm.\n[[LUYEN: bat ky]]"]]]])]);

        $res = $this->actingAs($this->student)->postJson(route('voice.reply'), ['text' => 'cho em xem kết quả'])->assertOk();

        $this->assertSame('stats', $res->json('card.type'));
        $this->assertNull($res->json('quiz'));
    }

    public function test_student_agreeing_to_the_offer_starts_the_quiz_with_the_ai_topic(): void
    {
        $b = $this->question($this->test($this->topicB, 'b1'), 'Câu công dân số?', ['p', 'q'], [0]);
        $this->fakeAi("Được chứ em!
[[LUYEN: Công dân số]]");

        $res = $this->actingAs($this->student)->postJson(route('voice.reply'), [
            'text' => 'ok cô',
            'history' => [['role' => 'assistant', 'text' => 'Em có muốn làm thử một câu hỏi về chủ đề Công dân số không?']],
        ])->assertOk();

        $this->assertSame($b->id, $res->json('quiz.question.id'));
    }

    public function test_asking_to_switch_tab_opens_the_page_and_does_not_start_a_quiz(): void
    {
        $this->question($this->test($this->topicA, 'a1'), 'Câu?', ['x', 'y'], [0]);
        // AI hiểu em nhờ mở trang nhưng lỡ gắn thêm dấu hiệu ra câu hỏi: mở trang, không ra câu hỏi
        $this->fakeAi("Cô mở trang cho em nhé.
[[MO_NGAY: trang_hoc]]
[[LUYEN: bat ky]]");

        $res = $this->actingAs($this->student)->postJson(route('voice.reply'), ['text' => 'muốn chuyển qua Tab làm các bài luyện tập'])->assertOk();

        $this->assertNull($res->json('quiz'));
        $this->assertSame(route('programs'), $res->json('action.url'));
        $this->assertTrue($res->json('action.auto'));
    }

    public function test_reply_has_nicely_formatted_display_text_and_clean_spoken_text(): void
    {
        $this->fakeAi("Em đã làm **ba bài** rồi.

Các chủ đề em đang học:
- Căn bản về công nghệ
- Công dân số
- Quản lý thông tin");

        $res = $this->actingAs($this->student)->postJson(route('voice.reply'), ['text' => 'em học những gì'])->assertOk();

        $this->assertStringContainsString("- Căn bản về công nghệ
- Công dân số", $res->json('display'));
        $this->assertStringContainsString('**ba bài**', $res->json('display'));
        $this->assertStringNotContainsString('**', $res->json('text'));
        $this->assertStringNotContainsString("
", $res->json('text'));
    }

    public function test_wrong_answer_feedback_shows_correct_answer_from_database_in_a_structured_way(): void
    {
        $this->question($this->test($this->topicA, 'a1'), 'Thiết bị nào có bàn di chuột?', ['Laptop', 'Desktop', 'Tablet'], [0]);
        $this->fakeAi('Laptop có bàn di chuột tích hợp để trỏ và nhấp.');
        $this->start();

        $res = $this->answer('B')->assertOk();

        $display = $res->json('feedback_display');
        $this->assertStringContainsString('**❌ Chưa đúng.**', $display);
        $this->assertStringContainsString("**Đáp án đúng:**
- **A.** Laptop", $display);
        $this->assertStringContainsString('**Vì sao:** Laptop có bàn di chuột', $display);
        $this->assertStringContainsString('Đáp án đúng là A: Laptop.', $res->json('feedback'));
    }

    public function test_topic_tool_only_lists_grades_the_student_can_learn(): void
    {
        $this->question($this->test($this->topicA, 'a1'), 'Câu?', ['x', 'y'], [0]);
        $locked = Topic::create(['level_id' => $this->otherLevel->id, 'name' => 'Chủ đề khối năm', 'slug' => 'k5-vq', 'position' => 1]);
        $this->question($this->test($locked, 'k5a'), 'Câu khối 5?', ['x', 'y'], [0]);

        $out = app(\App\Services\VoiceToolbox::class)->run($this->student, 'chu_de_theo_khoi', []);

        $this->assertSame(['Khối 3'], array_column($out['khoi'], 'khoi'));
    }

    private function attempt(PracticeTest $test, int $score, string $when, ?User $who = null): void
    {
        TestAttempt::create([
            'user_id' => ($who ?? $this->student)->id, 'practice_test_id' => $test->id, 'score' => $score,
            'correct_answers' => (int) round($score / 100), 'total_questions' => 10, 'completed_at' => $when,
        ]);
    }

    public function test_results_question_gets_a_stats_card_with_exact_numbers_from_database(): void
    {
        $t1 = $this->test($this->topicA, 'k1');
        $t2 = $this->test($this->topicA, 'k2');
        $t3 = $this->test($this->topicB, 'k3');
        $this->attempt($t1, 900, '2026-09-20 08:00:00');
        $this->attempt($t2, 800, '2026-09-22 08:00:00');
        $this->attempt($t3, 600, '2026-09-25 08:00:00');
        $this->student->forceFill(['reward_stars' => 1234])->save();
        $q = $this->question($t1, 'Câu sai?', ['a', 'b'], [0]);
        StudentMistake::create(['user_id' => $this->student->id, 'question_id' => $q->id, 'practice_test_id' => $t1->id, 'wrong_count' => 2, 'correct_count' => 0, 'status' => 'unresolved']);
        // AI tự quyết định hiện thẻ (gọi hàm hien_the_thong_ke); số liệu trong thẻ do máy chủ lấy từ database
        Http::fake(['router.test/*' => Http::sequence()
            ->push($this->toolCall('hien_the_thong_ke', []))
            ->push(['choices' => [['message' => ['content' => 'Em làm rất tốt, cố gắng ôn lại phần còn yếu nhé.']]]])]);

        $res = $this->actingAs($this->student)->postJson(route('voice.reply'), ['text' => 'báo cáo kết quả học tập của em'])->assertOk();

        $card = $res->json('card');
        $this->assertSame('stats', $card['type']);
        $tiles = collect($card['tiles'])->pluck('value', 'label');
        $this->assertSame(3, $tiles['Bài đã làm']);
        $this->assertSame(2, $tiles['Bài đạt']);
        $this->assertSame(767, $tiles['Điểm trung bình']);
        $this->assertSame(900, $tiles['Điểm cao nhất']);
        $this->assertSame(600, $tiles['Điểm thấp nhất']);
        $this->assertSame(1234, $tiles['Sao thưởng']);
        $this->assertSame(1, $tiles['Câu sai cần ôn']);
        // Biểu đồ: cũ → mới, đánh dấu đạt/chưa đạt đúng
        $this->assertSame([900, 800, 600], array_column($card['bars'], 'value'));
        $this->assertSame([true, true, false], array_column($card['bars'], 'pass'));
        $this->assertSame(['20/09', '22/09', '25/09'], array_column($card['bars'], 'label'));
        $this->assertSame(1000, $card['max']);
        $this->assertSame('Căn bản về công nghệ', $card['weak'][0]['label']);
    }

    public function test_ai_decides_to_show_the_stats_card_and_only_comments(): void
    {
        $this->attempt($this->test($this->topicA, 'k1'), 950, '2026-09-20 08:00:00');
        Http::fake(['router.test/*' => Http::sequence()
            ->push($this->toolCall('hien_the_thong_ke', []))
            ->push(['choices' => [['message' => ['content' => 'Em làm rất tốt, cố gắng ôn lại phần còn yếu nhé.']]]])]);

        $res = $this->actingAs($this->student)->postJson(route('voice.reply'), ['text' => 'cho em coi tình hình học hành dạo này'])->assertOk();

        $this->assertSame('stats', $res->json('card.type'));
        $this->assertNull($res->json('quiz'));
        Http::assertSentCount(2);
        // Kết quả hàm gửi lại cho AI là bản tóm tắt để nhận xét
        $tool = '';
        Http::assertSent(function ($request) use (&$tool) {
            $tool = collect($request['messages'])->firstWhere('role', 'tool')['content'] ?? $tool;

            return true;
        });
        $this->assertStringContainsString('the_da_hien_tren_man_hinh', $tool);
    }

    public function test_no_stats_card_for_unrelated_requests(): void
    {
        $this->attempt($this->test($this->topicA, 'k1'), 900, '2026-09-20 08:00:00');
        $this->question($this->test($this->topicB, 'b1'), 'Câu?', ['x', 'y'], [0]);
        $this->fakeAi('Dạ.');

        foreach (['chào cô', 'cô ra câu hỏi cho em', 'chuyển qua trang thành tích giúp em', 'con bò ăn gì'] as $text) {
            $res = $this->actingAs($this->student)->postJson(route('voice.reply'), ['text' => $text])->assertOk();
            $this->assertNull($res->json('card'), "Không được hiện thẻ cho: {$text}");
        }
    }

    public function test_student_with_no_attempts_still_gets_a_card_without_fake_numbers(): void
    {
        Http::fake(['router.test/*' => Http::sequence()
            ->push($this->toolCall('hien_the_thong_ke', []))
            ->push(['choices' => [['message' => ['content' => 'Em chưa làm bài nào, mình bắt đầu nhé.']]]])]);

        $res = $this->actingAs($this->student)->postJson(route('voice.reply'), ['text' => 'kết quả của em thế nào'])->assertOk();

        $labels = array_column($res->json('card.tiles'), 'label');
        $this->assertNotContains('Bài đã làm', $labels);
        $this->assertNotContains('Điểm trung bình', $labels);
        $this->assertSame([], $res->json('card.bars'));
    }

    public function test_teacher_does_not_get_the_student_stats_card(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher', 'status' => 'active', 'expires_at' => now()->addDays(30), 'ai_assistant_until' => now()->addDays(5)]);
        $this->fakeAi('Dạ.');

        $this->actingAs($teacher)->postJson(route('voice.reply'), ['text' => 'báo cáo kết quả'])->assertOk()->assertJson(['card' => null]);
    }

    public function test_ai_remembers_the_last_sixteen_turns(): void
    {
        $history = [];
        for ($i = 1; $i <= 20; $i++) {
            $history[] = ['role' => $i % 2 ? 'user' : 'assistant', 'text' => "lượt số {$i}"];
        }
        $this->fakeAi('Dạ.');

        $this->actingAs($this->student)->postJson(route('voice.reply'), ['text' => 'câu mới', 'history' => $history])->assertOk();

        Http::assertSent(function ($request) {
            $texts = array_filter(array_column($request['messages'], 'content'), 'is_string');

            // Nhớ 16 lượt gần nhất (lượt 5 tới 20) + câu hiện tại; lượt 1 tới 4 bị bỏ
            return in_array('lượt số 20', $texts, true)
                && in_array('lượt số 5', $texts, true)
                && ! in_array('lượt số 4', $texts, true)
                && in_array('câu mới', $texts, true);
        });
    }

    public function test_answer_by_letter_is_graded_from_database(): void
    {
        $this->question($this->test($this->topicA, 'a1'), 'Thiết bị nào?', ['Laptop', 'Desktop', 'Tablet', 'Phone'], [0]);
        $this->start();
        // Lời nói "đáp án A": AI hiểu em chọn A, máy chủ chấm theo database
        Http::fake(['router.test/*' => Http::sequence()
            ->push(['choices' => [['message' => ['content' => '{"chon":["A"]}']]]])
            ->push(['choices' => [['message' => ['content' => 'Chính xác! Laptop có bàn di chuột tích hợp. Giỏi lắm!']]]])]);

        $res = $this->answer('đáp án A')->assertOk();

        $res->assertJson(['status' => 'graded', 'correct' => true, 'correct_keys' => ['A'], 'picked_keys' => ['A']]);
        $this->assertStringContainsString('Laptop', $res->json('feedback'));
        $this->assertStringContainsString('Em làm tiếp một câu nữa nhé?', $res->json('spoken'));
    }

    public function test_wrong_answer_returns_correct_key_and_fallback_feedback_when_ai_is_down(): void
    {
        $this->question($this->test($this->topicA, 'a1'), 'Thiết bị nào?', ['Laptop', 'Desktop', 'Tablet', 'Phone'], [0]);
        Http::fake(['router.test/*' => Http::response('loi', 500), 'generativelanguage.googleapis.com/*' => Http::response('loi', 500)]);
        $this->start();

        $res = $this->answer('b')->assertOk();

        $res->assertJson(['status' => 'graded', 'correct' => false, 'correct_keys' => ['A'], 'picked_keys' => ['B']]);
        $this->assertStringContainsString('Đáp án đúng là A: Laptop', $res->json('feedback'));
    }

    public function test_ai_understands_answer_said_as_option_content(): void
    {
        $this->question($this->test($this->topicA, 'a1'), 'Thiết bị nào?', ['Máy tính xách tay Laptop', 'Máy tính để bàn Desktop', 'Máy tính bảng Tablet', 'Điện thoại thông minh'], [2]);
        $this->start();
        Http::fake(['router.test/*' => Http::sequence()
            ->push(['choices' => [['message' => ['content' => '{"chon":["C"]}']]]])
            ->push(['choices' => [['message' => ['content' => 'Đúng rồi!']]]])]);

        $this->answer('em chọn máy tính bảng tablet')->assertOk()->assertJson(['status' => 'graded', 'correct' => true, 'picked_keys' => ['C']]);
    }

    public function test_ai_understands_answer_said_as_ordinal(): void
    {
        $this->question($this->test($this->topicA, 'a1'), 'Câu?', ['một', 'hai', 'ba', 'bốn'], [1]);
        $this->start();
        Http::fake(['router.test/*' => Http::sequence()
            ->push(['choices' => [['message' => ['content' => '{"chon":["B"]}']]]])
            ->push(['choices' => [['message' => ['content' => 'Đúng!']]]])]);

        $this->answer('cái thứ hai ạ')->assertOk()->assertJson(['status' => 'graded', 'picked_keys' => ['B'], 'correct' => true]);
    }

    public function test_multiple_response_needs_exact_set(): void
    {
        $this->question($this->test($this->topicA, 'm1'), 'Chọn các thiết bị nhập?', ['Bàn phím', 'Màn hình', 'Chuột', 'Loa'], [0, 2], 'MultipleResponse');
        $this->fakeAi('Đúng rồi!');
        $this->start()->assertJsonPath('quiz.question.multi', true);

        $this->answer('A và C')->assertOk()->assertJson(['status' => 'graded', 'correct' => true, 'correct_keys' => ['A', 'C']]);
    }

    public function test_multiple_response_missing_one_is_wrong(): void
    {
        $this->question($this->test($this->topicA, 'm1'), 'Chọn các thiết bị nhập?', ['Bàn phím', 'Màn hình', 'Chuột', 'Loa'], [0, 2], 'MultipleResponse');
        $this->start();
        Http::fake(['router.test/*' => Http::sequence()
            ->push(['choices' => [['message' => ['content' => '{"chon":["A"]}']]]])
            ->push(['choices' => [['message' => ['content' => 'Gần đúng rồi!']]]])]);

        $this->answer('chỉ A thôi')->assertOk()->assertJson(['status' => 'graded', 'correct' => false]);
    }

    public function test_single_choice_with_two_letters_asks_again(): void
    {
        $this->question($this->test($this->topicA, 'a1'), 'Câu?', ['a1x', 'b1x', 'c1x'], [0]);
        $this->start();

        $this->answer('A và B')->assertOk()->assertJson(['status' => 'unclear']);
        // Câu vẫn đang chờ: nói lại đúng thì chấm được
        $this->fakeAi('Đúng!');
        $this->answer('A')->assertOk()->assertJson(['status' => 'graded', 'correct' => true]);
    }

    public function test_unclear_answer_keeps_question_pending_and_ai_can_map_free_speech(): void
    {
        $this->question($this->test($this->topicA, 'a1'), 'Câu?', ['quả táo', 'quả cam', 'quả chuối'], [1]);
        // 1: AI hiểu giúp lời nói tự do -> B; 2: lời giải thích
        $this->fakeAi('{"chon":["B"]}', 'Đúng rồi em!');
        $this->start();

        $this->answer('em nghĩ là cái ở giữa')->assertOk()->assertJson(['status' => 'graded', 'picked_keys' => ['B'], 'correct' => true]);
    }

    public function test_when_ai_cannot_understand_the_answer_it_asks_to_repeat(): void
    {
        $this->question($this->test($this->topicA, 'a1'), 'Câu?', ['quả táo', 'quả cam', 'quả chuối'], [1]);
        $this->fakeAi('{"chon":[]}');
        $this->start();

        $res = $this->answer('hmm cô ơi khó quá')->assertOk()->assertJson(['status' => 'unclear']);
        $this->assertStringContainsString('chưa nghe rõ', $res->json('spoken'));
    }

    public function test_cannot_grade_without_a_question_issued_to_this_student(): void
    {
        $this->question($this->test($this->topicA, 'a1'), 'Câu?', ['a', 'b'], [0]);

        $this->answer('A')->assertOk()->assertJson(['status' => 'no_question']);
    }

    public function test_question_is_graded_once_then_cleared(): void
    {
        $this->question($this->test($this->topicA, 'a1'), 'Câu?', ['Laptop', 'Desktop'], [0]);
        $this->fakeAi('Đúng!');
        $this->start();

        $this->answer('A')->assertJson(['status' => 'graded']);
        $this->answer('B')->assertJson(['status' => 'no_question']);
    }

    public function test_students_cannot_see_each_others_pending_question(): void
    {
        $this->question($this->test($this->topicA, 'a1'), 'Câu?', ['Laptop', 'Desktop'], [0]);
        $this->start();

        $other = User::factory()->create(['role' => 'student', 'created_by' => null, 'status' => 'active', 'expires_at' => now()->addDays(5), 'max_students' => 1, 'ai_assistant_until' => now()->addDays(5)]);
        $other->accessibleLevels()->sync([$this->level->id]);

        $this->actingAs($other)->postJson(route('voice.quiz.answer'), ['text' => 'A'])->assertOk()->assertJson(['status' => 'no_question']);
    }

    public function test_review_wrong_questions_mode(): void
    {
        $test = $this->test($this->topicA, 'a1');
        $this->question($test, 'Câu bình thường?', ['a', 'b'], [0]);
        $wrong = $this->question($test, 'Câu em hay sai?', ['x', 'y'], [1]);
        StudentMistake::create(['user_id' => $this->student->id, 'question_id' => $wrong->id, 'practice_test_id' => $test->id, 'wrong_count' => 3, 'correct_count' => 0, 'status' => 'unresolved']);

        for ($i = 0; $i < 4; $i++) {
            Cache::forget('voice_quiz_seen:' . $this->student->id);
            $this->start('ôn lại câu sai')->assertOk()->assertJsonPath('quiz.question.id', $wrong->id)->assertJsonPath('quiz.source', 'cau_sai');
        }
    }

    public function test_does_not_repeat_questions_until_all_seen(): void
    {
        $test = $this->test($this->topicA, 'a1');
        $q1 = $this->question($test, 'Câu 1?', ['a', 'b'], [0]);
        $q2 = $this->question($test, 'Câu 2?', ['a', 'b'], [0]);

        $first = $this->start()->json('quiz.question.id');
        $second = $this->start()->json('quiz.question.id');

        $this->assertEqualsCanonicalizing([$q1->id, $q2->id], [$first, $second]);
    }

    public function test_quiz_does_not_touch_stars_or_attempts(): void
    {
        $this->question($this->test($this->topicA, 'a1'), 'Câu?', ['Laptop', 'Desktop'], [0]);
        $this->fakeAi('Đúng!');
        $before = (int) $this->student->fresh()->reward_stars;
        $this->start();
        $this->answer('A')->assertJson(['status' => 'graded', 'correct' => true]);

        $this->assertSame($before, (int) $this->student->fresh()->reward_stars);
        $this->assertSame(0, $this->student->attempts()->count());
        $this->assertSame(0, StudentMistake::count());
    }

    public function test_quiz_requires_ai_package(): void
    {
        $this->student->forceFill(['ai_assistant_until' => null])->save();

        $this->start()->assertStatus(403);
        $this->answer('A')->assertStatus(403);
    }

    // ----- AI ra hiệu làm câu hỏi trong lúc trò chuyện -----

    public function test_chat_reply_with_marker_returns_quiz_and_hides_marker(): void
    {
        $b = $this->question($this->test($this->topicB, 'b1'), 'Câu công dân số?', ['p', 'q'], [0]);
        $this->fakeAi("Được chứ em! Cô ra câu hỏi nhé.\n[[LUYEN: Công dân số]]");

        $res = $this->actingAs($this->student)->postJson(route('voice.reply'), ['text' => 'ra câu hỏi công dân số đi cô'])->assertOk();

        $this->assertStringNotContainsString('LUYEN', $res->json('text'));
        $this->assertStringNotContainsString('[[', $res->json('text'));
        $this->assertSame($b->id, $res->json('quiz.question.id'));
    }

    public function test_chat_reply_without_quiz_request_has_no_quiz(): void
    {
        $this->question($this->test($this->topicA, 'a1'), 'Câu?', ['a', 'b'], [0]);
        $this->fakeAi('Em đã làm ba bài rồi đó.');

        $this->actingAs($this->student)->postJson(route('voice.reply'), ['text' => 'Em làm được mấy bài rồi?'])
            ->assertOk()
            ->assertJson(['quiz' => null]);
    }

    public function test_ai_marker_starts_a_real_quiz_from_the_bank(): void
    {
        $this->question($this->test($this->topicA, 'a1'), 'Câu về công nghệ?', ['a', 'b'], [0]);
        $this->fakeAi("Cô trò mình bắt đầu làm câu hỏi nhé!\n[[LUYEN: bat ky]]");

        $res = $this->actingAs($this->student)->postJson(route('voice.reply'), ['text' => 'cô thử trình độ em xem'])->assertOk();

        $this->assertSame('Câu về công nghệ?', $res->json('quiz.question.text'));
    }

    public function test_without_ai_marker_no_quiz_is_started(): void
    {
        $this->question($this->test($this->topicA, 'a1'), 'Câu về công nghệ?', ['a', 'b'], [0]);
        $this->fakeAi('Cô trò mình bắt đầu làm câu hỏi nhé!');

        $res = $this->actingAs($this->student)->postJson(route('voice.reply'), ['text' => 'Cô ra câu hỏi cho em làm đi'])->assertOk();

        // Không còn đoán bằng từ khóa: chỉ ra câu hỏi khi AI hiểu và gắn dấu hiệu
        $this->assertNull($res->json('quiz'));
    }

    public function test_student_agreeing_after_offer_starts_quiz_through_ai(): void
    {
        $this->question($this->test($this->topicA, 'a1'), 'Câu về công nghệ?', ['a', 'b'], [0]);
        $this->fakeAi("Tuyệt vời, cô trò mình bắt đầu ngay nhé!\n[[LUYEN: Căn bản về công nghệ]]");

        $res = $this->actingAs($this->student)->postJson(route('voice.reply'), [
            'text' => 'ok',
            'history' => [['role' => 'user', 'text' => 'chào cô'], ['role' => 'assistant', 'text' => 'Em có muốn làm thử một câu hỏi về chủ đề Căn bản về công nghệ không?']],
        ])->assertOk();

        $this->assertNotNull($res->json('quiz'));
    }

    public function test_ok_without_a_quiz_offer_does_not_start_quiz(): void
    {
        $this->question($this->test($this->topicA, 'a1'), 'Câu?', ['a', 'b'], [0]);
        $this->fakeAi('Dạ em cần gì cứ nói nhé.');

        $this->actingAs($this->student)->postJson(route('voice.reply'), [
            'text' => 'ok',
            'history' => [['role' => 'assistant', 'text' => 'Hôm nay trời đẹp nhỉ.']],
        ])->assertOk()->assertJson(['quiz' => null]);
    }

    public function test_marker_with_no_available_question_says_so_politely(): void
    {
        $this->fakeAi("Được chứ!\n[[LUYEN: bat ky]]");

        $res = $this->actingAs($this->student)->postJson(route('voice.reply'), ['text' => 'cho em làm câu hỏi'])->assertOk();

        $this->assertNull($res->json('quiz'));
        $this->assertStringContainsString('chưa tìm được câu hỏi phù hợp', $res->json('text'));
    }

    private function seedMistakeAndStars(): void
    {
        $test = $this->test($this->topicA, 'a1');
        $q = $this->question($test, 'Câu em hay sai về chuột?', ['a', 'b'], [0]);
        StudentMistake::create(['user_id' => $this->student->id, 'question_id' => $q->id, 'practice_test_id' => $test->id, 'wrong_count' => 2, 'correct_count' => 0, 'status' => 'unresolved']);
        $this->student->forceFill(['reward_stars' => 777, 'game_time_seconds' => 600])->save();
    }

    private function sentPrompt(): string
    {
        $prompt = '';
        Http::assertSent(function ($request) use (&$prompt) {
            $prompt = $request['messages'][0]['content'] ?? '';

            return true;
        });

        return $prompt;
    }

    public function test_stars_question_is_answered_by_ai_with_real_numbers(): void
    {
        $this->seedMistakeAndStars();
        $this->fakeAi('Em đang có 777 sao thưởng và 10 phút chơi game nhé.');

        $res = $this->actingAs($this->student)->postJson(route('voice.reply'), ['text' => 'Em có bao nhiêu sao?'])->assertOk();

        Http::assertSentCount(1);
        $this->assertStringContainsString('"so_sao_thuong_hien_co":777', $this->sentPrompt());
        $this->assertStringContainsString('777 sao thưởng', $res->json('text'));
    }

    public function test_greeting_and_navigation_are_understood_by_ai(): void
    {
        Http::fake(['router.test/*' => Http::sequence()
            ->push(['choices' => [['message' => ['content' => 'Chào em! Cô nghe đây.']]]])
            ->push(['choices' => [['message' => ['content' => "Cô mở trang thành tích cho em nhé.\n[[MO_NGAY: thanh_tich]]"]]]])]);

        $hi = $this->actingAs($this->student)->postJson(route('voice.reply'), ['text' => 'chào cô'])->assertOk();
        $go = $this->actingAs($this->student)->postJson(route('voice.reply'), ['text' => 'cho em qua coi thành tích với'])->assertOk();

        Http::assertSentCount(2);
        $this->assertStringContainsString('Chào em', $hi->json('text'));
        $this->assertSame(route('achievements'), $go->json('action.url'));
        $this->assertTrue($go->json('action.auto'));
    }

    public function test_ai_choice_marker_is_graded_by_server_from_database(): void
    {
        $this->question($this->test($this->topicA, 'a1'), 'Thiết bị nào có bàn di chuột?', ['Laptop', 'Desktop'], [0]);
        $this->start();
        Http::fake(['router.test/*' => Http::sequence()
            ->push(['choices' => [['message' => ['content' => "Cô chấm nhé!\n[[CHON: B]]"]]]])
            ->push(['choices' => [['message' => ['content' => 'Laptop có bàn di chuột tích hợp.']]]])]);

        $res = $this->actingAs($this->student)->postJson(route('voice.reply'), ['text' => 'em nghĩ là cái máy để bàn'])->assertOk();

        $res->assertJson(['graded' => ['status' => 'graded', 'correct' => false, 'picked_keys' => ['B'], 'correct_keys' => ['A']]]);
        $this->assertStringContainsString('Chưa đúng', $res->json('display'));
        $this->assertFalse(app(\App\Services\VoiceQuizService::class)->hasPending($this->student));
    }

    public function test_choice_marker_is_ignored_when_no_question_is_open(): void
    {
        $this->fakeAi("Cô chấm nhé!\n[[CHON: A]]");

        $res = $this->actingAs($this->student)->postJson(route('voice.reply'), ['text' => 'A'])->assertOk();

        $this->assertNull($res->json('graded'));
        $this->assertStringNotContainsString('CHON', $res->json('text'));
    }

    public function test_ai_quiz_controls_are_passed_to_the_screen(): void
    {
        $this->question($this->test($this->topicA, 'a1'), 'Câu?', ['x', 'y'], [0]);
        $this->start();

        $controls = ['[[DOC_LAI]]' => 'doc_lai', '[[DOI_CAU]]' => 'doi_cau', '[[DUNG]]' => 'dung'];
        $this->fakeAi(...array_map(fn ($marker) => "Được em.\n{$marker}", array_keys($controls)));
        foreach ($controls as $control) {
            $res = $this->actingAs($this->student)->postJson(route('voice.reply'), ['text' => 'em nói gì đó'])->assertOk();
            $this->assertSame($control, $res->json('control'));
            $this->assertStringNotContainsString('[[', $res->json('text'));
        }
    }

    public function test_prompt_has_quick_profile_and_ai_fetches_details_with_tools(): void
    {
        $this->seedMistakeAndStars();
        $this->fakeAi('Dạ.');

        $this->actingAs($this->student)->postJson(route('voice.reply'), ['text' => 'em hay sai chủ đề nào nhất'])->assertOk();
        $prompt = $this->sentPrompt();

        // Hồ sơ nhanh: câu sai theo chủ đề, sao; nội dung từng câu sai thì AI tự gọi hàm cau_sai khi cần
        $this->assertStringContainsString('"theo_chu_de":[{"chu_de":"Căn bản về công nghệ","so_cau":1}]', $prompt);
        $this->assertStringContainsString('"so_sao_thuong_hien_co":777', $prompt);
        $this->assertStringNotContainsString('Câu em hay sai về chuột', $prompt);
        $tools = [];
        Http::assertSent(function ($request) use (&$tools) {
            $tools = array_column(array_column($request['tools'] ?? [], 'function'), 'name');

            return true;
        });
        $this->assertContains('cau_sai', $tools);
        $this->assertContains('huong_dan', $tools);
    }

    public function test_general_question_prompt_is_small(): void
    {
        $this->seedMistakeAndStars();
        $this->fakeAi('Dạ.');

        $this->actingAs($this->student)->postJson(route('voice.reply'), ['text' => 'máy tính là gì vậy cô'])->assertOk();
        $prompt = $this->sentPrompt();

        $this->assertStringNotContainsString('Câu em hay sai về chuột', $prompt);
        $this->assertStringNotContainsString('TÀI LIỆU HƯỚNG DẪN', $prompt);
        $this->assertLessThan(5000, mb_strlen($prompt), 'Prompt câu hỏi chung phải gọn');
    }

    public function test_guard_drops_invented_numbers_and_fake_topics(): void
    {
        $this->fakeAi("Em làm bài rất chăm chỉ.\n- Em được 845 điểm bài gần nhất.\n- Em nên ôn chủ đề Lập trình robot nhé.\n- Cố lên em nhé!");

        $res = $this->actingAs($this->student)->postJson(route('voice.reply'), ['text' => 'em học thế nào rồi cô'])->assertOk();

        $display = $res->json('display');
        $this->assertStringNotContainsString('845', $display);
        $this->assertStringNotContainsString('Lập trình robot', $display);
        $this->assertStringContainsString('Em làm bài rất chăm chỉ.', $display);
        $this->assertStringContainsString('- Cố lên em nhé!', $display);
    }

    public function test_guard_keeps_real_topics_and_numbers_from_data(): void
    {
        $this->attempt($this->test($this->topicA, 'k1'), 950, '2026-09-20 08:00:00');
        $this->fakeAi('Bài gần nhất em được 950 điểm, ôn thêm chủ đề Căn bản về công nghệ nhé.');

        $res = $this->actingAs($this->student)->postJson(route('voice.reply'), ['text' => 'bài gần nhất em làm thế nào'])->assertOk();

        $this->assertStringContainsString('950 điểm', $res->json('text'));
        $this->assertStringContainsString('Căn bản về công nghệ', $res->json('text'));
    }

    public function test_guard_hides_answer_leak_while_question_is_open(): void
    {
        $this->question($this->test($this->topicA, 'a1'), 'Thiết bị nào có bàn di chuột?', ['Laptop', 'Desktop'], [0]);
        $this->start()->assertOk();
        $this->fakeAi('Em nghĩ xem thiết bị nào mang đi được. Đáp án đúng là A nhé.');

        $res = $this->actingAs($this->student)->postJson(route('voice.reply'), ['text' => 'gợi ý cho em với'])->assertOk();

        $this->assertStringNotContainsString('Đáp án đúng', $res->json('text'));
        $this->assertStringContainsString('mang đi được', $res->json('text'));
    }

    /** Phản hồi giả của 9Router: AI yêu cầu gọi một hàm */
    private function toolCall(string $name, array $args): array
    {
        return ['choices' => [['message' => ['role' => 'assistant', 'content' => null, 'tool_calls' => [[
            'id' => 'call_1', 'type' => 'function', 'function' => ['name' => $name, 'arguments' => json_encode($args, JSON_UNESCAPED_UNICODE)],
        ]]]]]];
    }

    public function test_ai_calls_a_tool_and_answers_with_the_returned_data(): void
    {
        // Bài cũ (ngoài 3 bài gần nhất trong hồ sơ nhanh) nên chỉ có được khi AI tự gọi hàm
        $old = $this->test($this->topicB, 'cds1');
        $this->attempt($old, 870, '2026-08-01 08:00:00');
        foreach (['k1', 'k2', 'k3'] as $i => $slug) {
            $this->attempt($this->test($this->topicA, $slug), 950, '2026-09-2' . $i . ' 08:00:00');
        }
        Http::fake(['router.test/*' => Http::sequence()
            ->push($this->toolCall('ket_qua_bai_lam', ['chu_de' => 'công dân số']))
            ->push(['choices' => [['message' => ['content' => 'Chủ đề Công dân số em được 870 điểm nhé.']]]])]);

        $res = $this->actingAs($this->student)->postJson(route('voice.reply'), ['text' => 'phần công dân số hồi trước em được nhiêu'])->assertOk();

        Http::assertSentCount(2);
        $this->assertStringContainsString('870 điểm', $res->json('text'), 'Số lấy từ hàm là số thật, bộ soát phải giữ lại');
        $second = [];
        Http::assertSent(function ($request) use (&$second) {
            $second = $request['messages'];

            return true;
        });
        $tool = collect($second)->firstWhere('role', 'tool');
        $this->assertNotNull($tool);
        $this->assertStringContainsString('870', $tool['content']);
        $this->assertStringContainsString('Công dân số', $tool['content']);
        $this->assertStringNotContainsString('"diem":950', $tool['content'], 'Hàm phải lọc đúng chủ đề');
    }

    public function test_tools_only_read_the_speakers_own_data(): void
    {
        $other = User::factory()->create(['role' => 'student', 'created_by' => null, 'status' => 'active', 'expires_at' => now()->addDays(30)]);
        $this->attempt($this->test($this->topicA, 'k1'), 650, '2026-09-20 08:00:00', $other);
        $this->attempt($this->test($this->topicA, 'k2'), 990, '2026-09-21 08:00:00');

        $out = app(\App\Services\VoiceToolbox::class)->run($this->student, 'ket_qua_bai_lam', ['tu_ngay' => '2026-01-01']);

        $this->assertSame(1, $out['tong_so_lan']);
        $this->assertSame(990, $out['danh_sach'][0]['diem']);
        $this->assertArrayHasKey('loi', app(\App\Services\VoiceToolbox::class)->run($this->student, 'hoc_sinh', ['ten' => $other->name]));
        $this->assertArrayHasKey('loi', app(\App\Services\VoiceToolbox::class)->run($this->student, 'xoa_du_lieu', []));
    }

    public function test_teacher_tool_only_finds_own_students(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher', 'status' => 'active', 'expires_at' => now()->addDays(30), 'max_students' => 10]);
        $mine = User::factory()->create(['role' => 'student', 'name' => 'Bé Na', 'created_by' => $teacher->id, 'status' => 'active']);
        $this->attempt($this->test($this->topicA, 'k1'), 880, '2026-09-20 08:00:00', $mine);
        $box = app(\App\Services\VoiceToolbox::class);

        $this->assertSame(880, $box->run($teacher, 'hoc_sinh', ['ten' => 'na'])['bai_gan_day'][0]['diem']);
        $this->assertArrayHasKey('loi', $box->run($teacher, 'hoc_sinh', ['ten' => $this->student->name]));
    }

    public function test_tool_filters_by_date_and_rejects_unknown_topic_with_real_names(): void
    {
        $this->attempt($this->test($this->topicA, 'k1'), 800, '2026-09-10 08:00:00');
        $this->attempt($this->test($this->topicA, 'k2'), 900, '2026-09-25 08:00:00');
        $box = app(\App\Services\VoiceToolbox::class);

        $week = $box->run($this->student, 'ket_qua_bai_lam', ['tu_ngay' => '2026-09-21', 'den_ngay' => '2026-09-27']);
        $this->assertSame(1, $week['tong_so_lan']);
        $this->assertSame(900, $week['diem_trung_binh']);

        $bad = $box->run($this->student, 'cau_sai', ['chu_de' => 'lập trình robot']);
        $this->assertArrayHasKey('loi', $bad);
        $this->assertContains('Căn bản về công nghệ', $bad['cac_chu_de_that']);
    }

    public function test_when_tool_calling_fails_voice_chat_still_answers(): void
    {
        // Lần gọi có hàm lỗi 500, hệ thống quay về cách trả lời thường
        Http::fake(['router.test/*' => Http::sequence()
            ->push('loi', 500)
            ->push(['choices' => [['message' => ['content' => 'Cô vẫn ở đây nè em.']]]])]);

        $res = $this->actingAs($this->student)->postJson(route('voice.reply'), ['text' => 'máy tính là gì vậy cô'])->assertOk();

        $this->assertStringContainsString('Cô vẫn ở đây', $res->json('text'));
    }

    public function test_ai_can_open_a_lesson_it_found_but_never_a_lesson_from_another_grade(): void
    {
        $mine = $this->test($this->topicA, 'k1');
        $otherTopic = Topic::create(['level_id' => $this->otherLevel->id, 'name' => 'Khối khác', 'slug' => 'khac-vq', 'position' => 3]);
        $locked = $this->test($otherTopic, 'k5');

        $this->fakeAi("Cô mở bài cho em nhé.\n[[MO: lam_{$mine->id}]]", "Cô mở bài nhé.\n[[MO: lam_{$locked->id}]]");
        $ok = $this->actingAs($this->student)->postJson(route('voice.reply'), ['text' => 'cô ơi bài đó ở đâu'])->assertOk();
        $no = $this->actingAs($this->student)->postJson(route('voice.reply'), ['text' => 'cô ơi bài kia ở đâu'])->assertOk();

        $this->assertSame(route('tests.launch', $mine), $ok->json('action.url'));
        $this->assertNull($no->json('action'));
    }

    public function test_quick_profile_always_has_key_numbers(): void
    {
        $this->seedMistakeAndStars();
        $this->fakeAi('Dạ.');

        $this->actingAs($this->student)->postJson(route('voice.reply'), ['text' => 'tài khoản em dùng tới khi nào'])->assertOk();
        $prompt = $this->sentPrompt();

        $this->assertStringContainsString('HỒ SƠ NGƯỜI ĐANG NÓI', $prompt);
        $this->assertStringContainsString('GỌI HÀM', $prompt);
        $this->assertStringContainsString('777', $prompt);
        $this->assertStringContainsString($this->student->expires_at->format('d/m/Y'), $prompt);
    }

    public function test_managed_student_sees_teacher_expiry_date(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher', 'status' => 'active', 'expires_at' => now()->addDays(200), 'max_students' => 10]);
        $this->student->forceFill(['created_by' => $teacher->id, 'expires_at' => null])->save();
        $this->fakeAi('Dạ.');

        $this->actingAs($this->student)->postJson(route('voice.reply'), ['text' => 'tài khoản em dùng tới khi nào'])->assertOk();

        $this->assertStringContainsString($teacher->expires_at->format('d/m/Y'), $this->sentPrompt());
    }

    public function test_ai_offering_with_a_question_does_not_start_quiz_even_with_practice_words(): void
    {
        $this->question($this->test($this->topicA, 'a1'), 'Câu?', ['x', 'y'], [0]);
        $this->fakeAi("Bài thi thử không khó đâu em. Em có muốn thử một câu không?\n[[LUYEN: bat ky]]");

        $res = $this->actingAs($this->student)->postJson(route('voice.reply'), ['text' => 'cô ơi bài thi thử khó không'])->assertOk();

        $this->assertNull($res->json('quiz'));
    }

    public function test_stats_question_with_a_time_range_goes_to_ai_not_the_all_time_card(): void
    {
        $this->attempt($this->test($this->topicA, 'k1'), 900, '2026-09-20 08:00:00');
        $this->fakeAi('Tháng 9 em làm 1 bài nhé.');

        $res = $this->actingAs($this->student)->postJson(route('voice.reply'), ['text' => 'tháng 9 em làm được bao nhiêu bài'])->assertOk();

        $this->assertNull($res->json('card'));
        Http::assertSentCount(1);
    }

    public function test_generic_words_without_ai_marker_do_not_start_a_quiz(): void
    {
        $this->question($this->test($this->topicA, 'a1'), 'Câu?', ['x', 'y'], [0]);
        $this->fakeAi('Lớp mình bạn nào cũng đã làm bài rồi nhé.');

        $res = $this->actingAs($this->student)->postJson(route('voice.reply'), ['text' => 'bạn nào chưa làm bài nào'])->assertOk();

        $this->assertNull($res->json('quiz'));
    }

    public function test_lessons_with_the_same_name_in_different_topics_are_not_merged(): void
    {
        $a = PracticeTest::create(['topic_id' => $this->topicA->id, 'name' => 'Bài luyện 2', 'slug' => 'bl2-a', 'duration_minutes' => 10, 'pass_score' => 700, 'max_score' => 1000, 'is_published' => true, 'question_count' => 1]);
        $b = PracticeTest::create(['topic_id' => $this->topicB->id, 'name' => 'Bài luyện 2', 'slug' => 'bl2-b', 'duration_minutes' => 10, 'pass_score' => 700, 'max_score' => 1000, 'is_published' => true, 'question_count' => 1]);
        $this->attempt($a, 800, '2026-09-20 08:00:00');
        $this->attempt($b, 1000, '2026-09-21 08:00:00');

        $out = app(\App\Services\VoiceToolbox::class)->run($this->student, 'ket_qua_bai_lam', ['ten_bai' => 'Bài luyện 2']);

        $this->assertStringContainsString('KHÁC NHAU', $out['luu_y']);
        $this->assertEqualsCanonicalizing(['Căn bản về công nghệ', 'Công dân số'], array_column($out['theo_tung_bai'], 'chu_de'));
    }

    public function test_teacher_class_data_has_lowest_and_average_scores(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher', 'status' => 'active', 'expires_at' => now()->addDays(30), 'max_students' => 10]);
        $kid = User::factory()->create(['role' => 'student', 'created_by' => $teacher->id, 'status' => 'active']);
        $this->attempt($this->test($this->topicA, 'k1'), 71, '2026-09-01 08:00:00', $kid);
        $this->attempt($this->test($this->topicA, 'k2'), 900, '2026-09-20 08:00:00', $kid);

        $row = app(\App\Services\AiAssistantService::class)->studentsOf($teacher, 'Asia/Ho_Chi_Minh')[0];

        $this->assertSame(71, $row['diem_thap_nhat']);
        $this->assertSame(900, $row['diem_cao_nhat']);
        $this->assertSame(486, $row['diem_trung_binh']);
        $this->assertSame(900, $row['diem_gan_nhat']);
    }

    public function test_guide_tool_tells_ai_the_document_is_for_the_text_support_chat(): void
    {
        $out = app(\App\Services\VoiceToolbox::class)->run($this->student, 'huong_dan', ['muc' => 'Đăng nhập']);

        $this->assertNotEmpty($out['noi_dung']);
        $this->assertStringContainsString('khung chat hỗ trợ ở góc màn hình', $out['ghi_chu']);
    }

    public function test_prompt_is_general_rules_without_case_specific_instructions(): void
    {
        $this->fakeAi('Dạ.');

        $this->actingAs($this->student)->postJson(route('voice.reply'), ['text' => 'cô ơi'])->assertOk();
        $prompt = $this->sentPrompt();

        $this->assertStringContainsString('NGUYÊN TẮC CHUNG', $prompt);
        $this->assertStringContainsString('làm theo ghi chú đó', $prompt);
        foreach (['laptop', 'khung chat', 'hien_the_thong_ke', 'Lê Minh Trí'] as $specific) {
            $this->assertStringNotContainsString($specific, $prompt, "Prompt không được có chỉ dẫn riêng cho trường hợp: {$specific}");
        }
    }

    public function test_question_about_the_creator_is_answered_from_assistant_info_tool(): void
    {
        Http::fake(['router.test/*' => Http::sequence()
            ->push($this->toolCall('thong_tin_tro_ly', []))
            ->push(['choices' => [['message' => ['content' => 'Người tạo ra cô là anh Lê Minh Trí, biệt danh Trí Kun đẹp zai cute phô mai que đó em!']]]])]);

        $res = $this->actingAs($this->student)->postJson(route('voice.reply'), ['text' => 'ai là chủ nhân của cô vậy'])->assertOk();

        $this->assertStringContainsString('Lê Minh Trí', $res->json('text'));
        $this->assertStringContainsString('Trí Kun đẹp zai cute phô mai que', $res->json('text'));
        $tool = '';
        Http::assertSent(function ($request) use (&$tool) {
            $tool = collect($request['messages'])->firstWhere('role', 'tool')['content'] ?? $tool;

            return true;
        });
        $this->assertStringContainsString('"nguoi_tao":"Lê Minh Trí"', $tool);
        $this->assertStringContainsString('"biet_danh_nguoi_tao":"Trí Kun đẹp zai cute phô mai que"', $tool);
    }

    public function test_topic_number_is_understood_by_ai_and_quiz_comes_from_that_topic(): void
    {
        $this->question($this->test($this->topicA, 'a1'), 'Câu chủ đề một?', ['x', 'y'], [0]);
        $this->question($this->test($this->topicB, 'b1'), 'Câu chủ đề hai?', ['x', 'y'], [0]);
        // Code không nhận ra mẫu câu này, AI hiểu "chủ đề 1" theo số thứ tự rồi ghi đúng tên vào dấu hiệu
        $this->fakeAi("Được chứ, cô ra câu hỏi chủ đề Căn bản về công nghệ nhé.\n[[LUYEN: Căn bản về công nghệ]]");

        $res = $this->actingAs($this->student)->postJson(route('voice.reply'), ['text' => 'cho tôi các câu hỏi về chủ đề 1'])->assertOk();

        Http::assertSentCount(1);
        $this->assertStringContainsString('SỐ THỨ TỰ', $this->sentPrompt());
        $this->assertSame('Câu chủ đề một?', $res->json('quiz.question.text'));
    }

    public function test_quiz_about_a_specific_content_picks_questions_that_mention_it(): void
    {
        $test = $this->test($this->topicA, 'a1');
        $this->question($test, 'Phần mềm nào dùng để soạn thảo văn bản?', ['Word', 'Paint'], [0]);
        $this->question($test, 'Thiết bị nào gập lại mang theo được?', ['Máy tính xách tay', 'Máy tính để bàn'], [0]);
        $this->question($test, 'Chuột dùng để làm gì?', ['Trỏ và nhấp', 'Gõ chữ'], [0]);
        $this->fakeAi(...array_fill(0, 3, "Được chứ em!\n[[LUYEN: Căn bản về công nghệ | laptop, máy tính xách tay]]"));

        // Lặp vài lần: lần nào cũng phải là câu có nhắc tới máy tính xách tay
        foreach (range(1, 3) as $_) {
            app(\App\Services\VoiceQuizService::class)->clear($this->student);
            $res = $this->actingAs($this->student)->postJson(route('voice.reply'), ['text' => 'tôi cần làm về máy tính xách tay'])->assertOk();
            $this->assertSame('Thiết bị nào gập lại mang theo được?', $res->json('quiz.question.text'));
            $this->assertTrue($res->json('quiz.matched'));
        }
        // "Đổi câu" giữ nguyên chủ đề và nội dung
        $this->assertSame('Căn bản về công nghệ | laptop, máy tính xách tay', $res->json('quiz.hint'));
    }

    public function test_content_not_in_a_topic_name_is_searched_in_questions(): void
    {
        $this->question($this->test($this->topicA, 'a1'), 'Chuột dùng để làm gì?', ['Trỏ và nhấp', 'Gõ chữ'], [0]);
        $this->question($this->test($this->topicB, 'b1'), 'Khi nhận thư lạ có đường link, em nên làm gì?', ['Không bấm vào', 'Bấm ngay'], [0]);

        $quiz = app(\App\Services\VoiceQuizService::class)->start($this->student, 'thư lạ');

        $this->assertSame('Khi nhận thư lạ có đường link, em nên làm gì?', $quiz['question']['text']);
    }

    public function test_when_no_question_mentions_the_content_student_is_told_honestly(): void
    {
        $this->question($this->test($this->topicA, 'a1'), 'Chuột dùng để làm gì?', ['Trỏ và nhấp', 'Gõ chữ'], [0]);
        $this->fakeAi("Được chứ em!\n[[LUYEN: Căn bản về công nghệ | máy in 3D]]");

        $res = $this->actingAs($this->student)->postJson(route('voice.reply'), ['text' => 'cho em câu về máy in 3D'])->assertOk();

        $this->assertSame('Chuột dùng để làm gì?', $res->json('quiz.question.text'));
        $this->assertFalse($res->json('quiz.matched'));
        $this->assertStringContainsString('chưa có câu nào nói đúng về «máy in 3D»', $res->json('text'));
    }
}
