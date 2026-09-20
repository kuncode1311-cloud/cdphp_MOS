// Dữ liệu Chương 3, Chương 4, Phụ lục và Tài liệu tham khảo cho Báo cáo Đồ án

export const chapter3And4Data = {
    envTable: [
        { item: "Hệ điều hành máy chủ", version: "Microsoft Windows 11 Pro 64-bit", role: "Môi trường nền tảng cài đặt và chạy thử nghiệm hệ thống." },
        { item: "PHP Engine", version: "PHP 8.3.28 (x64 ZTS)", role: "Trình thông dịch chạy mã nguồn server-side của Laravel." },
        { item: "Web Server", version: "Apache 2.4.62 (Win64) OpenSSL 3.0", role: "Máy chủ HTTP tiếp nhận request và chuyển tiếp cho PHP." },
        { item: "MySQL Database", version: "MySQL 8.0.30 Community Server", role: "Hệ quản trị CSDL quan hệ lưu trữ dữ liệu 17 bảng chuẩn hóa 3NF." },
        { item: "Laravel Framework", version: "Laravel 11.x / 12.x", role: "Bộ khung MVC chính xây dựng logic nghiệp vụ và bảo mật." },
        { item: "NodeJS / npm", version: "NodeJS v24.19.0 / npm v10.x", role: "Môi trường biên dịch asset CSS/JS và chạy script xuất báo cáo." },
        { item: "Asset Bundler & CSS", version: "Vite v8.0 / TailwindCSS v4.0", role: "Hệ thống style 3D Gamification, hiệu ứng tactile nổi khối rực rỡ." },
        { item: "Cổng thanh toán", version: "Cổng VietQR & PayOS API", role: "Giải pháp thanh toán quét mã ngân hàng tự động cho giáo viên." },
        { item: "Live Chat Bot", version: "Telegram Bot API (@sp_trikun_bot)", role: "Kênh tiếp nhận câu hỏi tư vấn từ web bắn về điện thoại Admin." },
        { item: "IDE Soạn thảo", version: "Visual Studio Code v1.93+", role: "Môi trường viết code, debug và quản trị phiên bản Git." },
        { item: "Môi trường Local", version: "Laragon 6.0 Full Edition", role: "Môi trường cô lập tích hợp đầy đủ Apache, MySQL, PHP, Composer." }
    ],

    screenshots: [
        {
            file: "hinh3_01_dang_nhap.png",
            num: "3.1",
            title: "Giao diện trang Đăng nhập phân quyền hệ thống (Auth Login)",
            desc: "Trang đăng nhập được thiết kế theo phong cách hiện đại với video background sinh động và form kính mờ (Glassmorphism). Giao diện cung cấp ô nhập Email hoặc Mã học sinh (student_code), mật khẩu bảo mật, nút bấm tactile nổi khối và khối 3 nút Đăng nhập nhanh tiện lợi cho việc trình diễn đồ án (Admin Tổng, Giáo viên 3A1, Học sinh An Nhiên). Form được bảo vệ nghiêm ngặt chống tấn công CSRF thông qua thẻ token ngẫu nhiên của Laravel."
        },
        {
            file: "hinh3_02_dashboard_admin.png",
            num: "3.2",
            title: "Giao diện Bảng điều khiển Quản trị viên (Admin Dashboard)",
            desc: "Dashboard Admin là trung tâm chỉ huy tổng thể của hệ thống, cung cấp số liệu thời gian thực được truy xuất trực tiếp từ cơ sở dữ liệu. Phía trên cùng là 4 khối thẻ thống kê màu sắc 3D nổi bật: Khối lớp đào tạo (3 khối), Ngân hàng đề thi (35 đề), Ngân hàng câu hỏi (509 câu) và Tổng lượt thi của học sinh (257 lượt). Phía dưới tích hợp bảng điều hướng nhanh, biểu đồ và danh sách lượt nộp bài gần nhất."
        },
        {
            file: "hinh3_03_quan_ly_nguoi_dung.png",
            num: "3.3",
            title: "Giao diện Quản lý Người dùng, Phân quyền Giáo viên và Lớp học",
            desc: "Trang quản lý toàn diện danh sách tài khoản người dùng và lớp học. Quản trị viên có thể xem danh sách người dùng phân trang, lọc theo vai trò (Admin, Giáo viên, Học sinh), tìm kiếm theo tên hoặc email. Cột thao tác cho phép Admin chỉnh sửa thông tin, phân công các Khối lớp (Khối 3, 4, 5) cho giáo viên giảng dạy qua bảng pivot teacher_level, hoặc khóa nhanh tài khoản khi phát hiện dấu hiệu vi phạm."
        },
        {
            file: "hinh3_04_ic3_question_studio.png",
            num: "3.4",
            title: "Giao diện IC3 Question Studio - Quản trị 509 câu hỏi và bộ đề thi",
            desc: "IC3 Question Studio là module biên soạn ngân hàng đề thi chuyên sâu. Giao diện được bố cục khoa học theo mô hình cây phân cấp: chọn Chương trình đào tạo -> Khối lớp -> 7 Chủ đề kiến thức -> Bộ đề thi cụ thể. Người dùng có thể duyệt danh sách 509 câu hỏi, thêm mới câu hỏi trắc nghiệm kèm hình ảnh minh họa bài thi (sơ đồ phần cứng, biểu tượng phần mềm Office), thiết lập 4 phương án trả lời và lời giải thích chi tiết."
        },
        {
            file: "hinh3_05_cai_dat_tro_choi.png",
            num: "3.5",
            title: "Giao diện Cài đặt Khu trò chơi & Tỷ lệ quy đổi Sao thưởng",
            desc: "Giao diện cấu hình động hệ thống Gamification. Quản trị viên có thể bật/tắt Khu trò chơi, thiết lập số Sao cần có để đổi các gói phút chơi game (Gói 5 phút, Gói 10 phút), thiết lập chu kỳ và công thức tính điểm Bảng xếp hạng thi đua tuần (chỉ tính bài đạt chuẩn hoặc tính tất cả điểm), và cấu hình Token Bot Telegram cùng Chat ID để nhận thông báo Live Chat thời gian thực."
        },
        {
            file: "hinh3_06_goi_dich_vu_admin.png",
            num: "3.6",
            title: "Giao diện Quản lý Gói dịch vụ & Đơn thuê bản quyền giáo viên",
            desc: "Phân hệ quản trị thương mại của hệ thống. Quản trị viên quản lý danh mục các gói bản quyền mở bán trên trang Bảng giá (tên gói, huy hiệu, giá bán, thời hạn, sĩ số học sinh tối đa, khối lớp áp dụng). Bên dưới là danh sách các đơn đặt hàng thuê gói của giáo viên thông qua cổng VietQR / PayOS kèm mã đơn hàng MOS-YYYYMM-XXXXX, trạng thái thanh toán và nút bấm phê duyệt kích hoạt bản quyền thủ công."
        },
        {
            file: "hinh3_07_cong_hoc_tap.png",
            num: "3.7",
            title: "Giao diện Cổng học tập học sinh - Hero Banner & 4 Thẻ lối tắt 3D",
            desc: "Cổng học tập học sinh được thiết kế rực rỡ, ngập tràn không khí phiêu lưu game giáo dục. Phần đầu là Hero Banner với linh vật robot vui nhộn chào đón học sinh. Khu vực Action Hub trung tâm nổi bật với 4 thẻ 3D xúc giác: Vào Luyện Thi (màu xanh ngọc), Bảng Thành Tích (màu tím), Khu Trò Chơi (màu cam hổ phách) và Góc Phụ Huynh (màu hồng neon). Phía dưới là thanh tiến độ học tập trong ngày và thử thách tuần."
        },
        {
            file: "hinh3_08_danh_muc_khoa_hoc.png",
            num: "3.8",
            title: "Giao diện Danh mục khóa học & Lưới khối lớp đào tạo",
            desc: "Trang hiển thị danh mục các chương trình học và lưới 3 khối lớp chuẩn quốc tế: Khối 3 (Spark Level 1), Khối 4 (Spark Level 2) và Khối 5 (Spark Level 3). Mỗi khối lớp được hiển thị dưới dạng thẻ 3D card với màu sắc chủ đạo riêng biệt, hiển thị số lượng chủ đề, tổng số bài luyện thi, tỷ lệ hoàn thành của học sinh và nút bấm 'Khám phá ngay' lún sâu khi click."
        },
        {
            file: "hinh3_09_ban_do_chu_de.png",
            num: "3.9",
            title: "Giao diện Bản đồ 7 chủ đề kiến thức chuẩn quốc tế Khối 3",
            desc: "Giao diện bản đồ học tập trực quan của Khối 3 bao gồm 7 chủ đề kiến thức chuẩn IC3 GS6: Máy tính căn bản, Phần cứng & Thiết bị ngoại vi, Phần mềm ứng dụng, Mạng Internet, An toàn thông tin, Kỹ năng tìm kiếm số và Đạo đức kỹ thuật số. Mỗi chủ đề hiển thị số lượng bài luyện thi, số sao đã thu thập, hỗ trợ cơ chế mở rộng/thu gọn (Accordion) mượt mà và bộ lọc tìm kiếm instant search không giật lag."
        },
        {
            file: "hinh3_10_chuan_bi_lam_bai.png",
            num: "3.10",
            title: "Giao diện Thẻ giới thiệu arcade 3D & Chuẩn bị vào phòng thi",
            desc: "Màn hình chuẩn bị trước khi vào thi được thiết kế phong cách máy chơi game thùng Arcade 3D ấn tượng. Học sinh được cung cấp đầy đủ thông tin: Tiêu đề bài luyện, Khối lớp trực thuộc, 4 khối thống kê (Thời gian làm bài, Số lượng câu hỏi, Điểm đạt chuẩn 700/1000, Phần thưởng 10 Sao vàng), bảng quy tắc phòng thi và nút bấm lớn 'Bắt đầu làm bài' sẵn sàng đưa học sinh vào phòng thi."
        },
        {
            file: "hinh3_11_bang_thanh_tich.png",
            num: "3.11",
            title: "Giao diện Bảng thành tích & Bảng xếp hạng Top 10 Hiệp sĩ nhí",
            desc: "Trung tâm thi đua của học sinh gồm 3 Pod năng lượng rực rỡ hiển thị điểm số tuần, số bài thi đạt chuẩn và số sao tích lũy. Phía dưới là Bộ sưu tập 8 Huy hiệu thành tích danh giá (Chiến binh xuất trận, Tay đua cự phách, Thần đồng IC3, Bậc thầy hoàn hảo...) và Bảng xếp hạng Top 10 Hiệp sĩ nhí dẫn đầu toàn trường với cúp vàng, bạc, đồng vinh danh học sinh có thành tích xuất sắc nhất."
        },
        {
            file: "hinh3_12_khu_tro_choi_doi_sao.png",
            num: "3.12",
            title: "Giao diện Khu trò chơi giải trí & Cửa hàng đổi Sao lấy phút chơi",
            desc: "Khu vực giải trí lành mạnh của học sinh. Giao diện hiển thị ví Sao hiện có và đồng hồ đếm ngược số giây chơi game còn lại. Cửa hàng đổi thưởng cung cấp các gói đổi hấp dẫn: 30 Sao đổi 5 phút và 50 Sao đổi 10 phút. Học sinh bấm nút 'Đổi gói' để trừ Sao tích lũy và mở khóa nút 'Chơi game ngay' dẫn vào các mini-game giáo dục rèn luyện phản xạ (như game Bảo vệ em bé)."
        },
        {
            file: "hinh3_13_goc_phu_huynh.png",
            num: "3.13",
            title: "Giao diện Bảng điều khiển Phụ huynh giám sát tiến độ học tập",
            desc: "Parent Dashboard là công cụ đắc lực giúp cha mẹ đồng hành cùng con. Giao diện tổng hợp dữ liệu từ 257 lượt thi thật, cung cấp 4 thẻ chỉ số nhanh, biểu đồ đường tiến trình điểm số theo thời gian, biểu đồ Radar đánh giá năng lực 7 chủ đề, biểu đồ Doughnut tỷ lệ đạt chuẩn và khu vực Cảnh báo sớm thông minh chỉ ra những câu hỏi hoặc chủ đề con hay làm sai để phụ huynh ôn tập lại cho con."
        },
        {
            file: "hinh3_14_bang_gia_dich_vu.png",
            num: "3.14",
            title: "Giao diện Cổng Bảng giá dịch vụ và các gói bản quyền giáo viên",
            desc: "Trang bảng giá công khai giới thiệu các gói bản quyền dành cho giáo viên và nhà trường: Gói Tiêu Chuẩn (Standard), Gói Nâng Cao (Pro) và Gói Trường Học (School VIP). Mỗi gói hiển thị rõ giá bán thực tế, giá niêm yết gạch ngang, thời hạn sử dụng, hạn mức sĩ số học sinh và danh sách các tính năng nổi bật. Nút 'Đăng ký & Thuê gói ngay' đưa giáo viên vào quy trình thanh toán nhanh chóng."
        },
        {
            file: "hinh3_15_thanh_toan_vietqr.png",
            num: "3.15",
            title: "Giao diện Thanh toán VietQR tự động và Cổng PayOS",
            desc: "Trang checkout thanh toán hiện đại. Hệ thống tự sinh mã VietQR động chứa tài khoản ngân hàng thụ hưởng, số tiền chính xác theo gói và mã đơn hàng duy nhất trong nội dung chuyển khoản. Trang hiển thị đồng hồ đếm ngược thời gian giữ đơn, nút tải mã QR về máy và cơ chế realtime polling tự động kiểm tra trạng thái thanh toán và chuyển hướng ngay khi giao dịch thành công."
        },
        {
            file: "hinh3_16_chi_tiet_bai_luyen.png",
            num: "3.16",
            title: "Giao diện Xem chi tiết nội dung bài luyện thi và danh sách câu hỏi",
            desc: "Trang thông tin tổng quan của một bài luyện thi cụ thể trước khi thi. Giao diện hiển thị chi tiết tiêu đề, chủ đề kiến thức trực thuộc, mức độ khó, thời lượng thi và danh sách tóm tắt các câu hỏi trong đề. Học sinh và giáo viên có thể nắm bắt cấu trúc đề thi, số lượng câu hỏi trắc nghiệm, hình ảnh mô phỏng đính kèm và điểm chuẩn đạt yêu cầu trước khi chính thức bấm làm bài."
        },
        {
            file: "hinh3_17_lich_su_thue_goi.png",
            num: "3.17",
            title: "Giao diện Lịch sử thuê gói và Quản lý thời hạn bản quyền",
            desc: "Giao diện tra cứu lịch sử bản quyền dành cho người dùng. Bảng thống kê hiển thị chi tiết mã đơn hàng giao dịch, tên gói dịch vụ bản quyền đã đăng ký, thời gian bắt đầu kích hoạt, hạn dùng đến ngày, trạng thái hiệu lực (Đang hoạt động / Hết hạn) và các nút gia hạn nhanh, giúp người dùng nắm bắt và chủ động quyền lợi học tập."
        }
    ],

    codeSnippets: [
        {
            num: "3.3.1",
            title: "Thuật toán chấm điểm và quy đổi điểm chuẩn IC3 (thang 1000 điểm, pass 700/1000, cộng Sao thưởng)",
            desc: "Đoạn mã nằm trong AttemptController.php xử lý quy trình chấm điểm tự động bài thi của học sinh: duyệt qua từng câu hỏi trong đề, so khớp câu trả lời đã tick với bảng question_options, tính điểm chuẩn theo thang 1000 điểm quốc tế IIG, xác định trạng thái Đạt (is_passed) và tự động cộng 10 Ngôi sao tích lũy vào ví của học sinh:",
            code: `public function store(Request $request, PracticeTest $practiceTest): JsonResponse
{
    $user = $request->user();
    $answers = $request->input('answers', []);
    $durationSeconds = (int) $request->input('duration_seconds', 0);

    // 1. Tải danh sách câu hỏi kèm đáp án đúng
    $questions = $practiceTest->questions()->with('options')->get();
    $totalQuestions = $questions->count();
    $correctCount = 0;
    $detailsPayload = [];

    // 2. Duyệt chấm từng câu hỏi
    foreach ($questions as $q) {
        $userAns = $answers[$q->id] ?? null;
        $correctOptions = $q->options->where('is_correct', true)->pluck('position')->sort()->values()->all();
        $isCorrect = false;

        if ($q->type === 'MultipleChoice') {
            $isCorrect = in_array((int)$userAns, $correctOptions, true);
        } elseif ($q->type === 'MultipleResponse') {
            $userAnsArray = collect((array)$userAns)->map(fn($v) => (int)$v)->sort()->values()->all();
            $isCorrect = ($userAnsArray === $correctOptions);
        }

        if ($isCorrect) {
            $correctCount++;
        }
        $detailsPayload[$q->id] = ['answer' => $userAns, 'is_correct' => $isCorrect];
    }

    // 3. Quy đổi sang thang điểm 1000 chuẩn IIG
    $scaledScore = $totalQuestions > 0 ? round(($correctCount / $totalQuestions) * 1000) : 0;
    $isPassed = ($scaledScore >= 700);

    // 4. Lưu lịch sử làm bài thi
    $attempt = TestAttempt::create([
        'user_id' => $user->id,
        'practice_test_id' => $practiceTest->id,
        'score' => $scaledScore,
        'correct_answers' => $correctCount,
        'total_questions' => $totalQuestions,
        'duration_seconds' => $durationSeconds,
        'is_passed' => $isPassed,
        'answers_payload' => $detailsPayload,
        'completed_at' => now(),
    ]);

    // 5. Thưởng 10 Sao vàng nếu bài thi Đạt chuẩn
    if ($isPassed) {
        $user->increment('reward_stars', 10);
        GameTransaction::create([
            'user_id' => $user->id,
            'type' => 'earn_star',
            'stars_change' => 10,
            'description' => "Thưởng vượt ải thành công bài: {$practiceTest->name} ({$scaledScore}/1000 điểm)"
        ]);
    }

    return response()->json(['success' => true, 'score' => $scaledScore, 'is_passed' => $isPassed, 'earned_stars' => $isPassed ? 10 : 0]);
}`
        },
        {
            num: "3.3.2",
            title: "Thuật toán trộn ngẫu nhiên (Shuffle) thứ tự câu hỏi và phương án A-B-C-D chống gian lận",
            desc: "Đoạn mã nằm trong LearningController.php xử lý tính năng xáo trộn câu hỏi và đáp án khi học sinh mở phòng thi ảo. Nếu đề thi được bật cờ shuffle_questions hoặc shuffle_options, hệ thống sử dụng thuật toán xáo trộn danh sách để tạo ra các đề thi con ngẫu nhiên, giúp học sinh ngồi cạnh nhau không thể nhìn bài:",
            code: `public function launch(PracticeTest $practiceTest): View
{
    $isAdmin = auth()->user()?->isAdmin() ?? false;
    $practiceTest->load(['topic.level', 'questions.options', 'questions.assets']);
    abort_if($practiceTest->questions->isEmpty(), 404, 'Bộ đề chưa có câu hỏi.');

    $questionsCollection = $practiceTest->questions;

    // 1. Trộn ngẫu nhiên thứ tự câu hỏi nếu đề bật Shuffle
    if ($practiceTest->shuffle_questions) {
        $questionsCollection = $questionsCollection->shuffle();
    }

    // 2. Chuyển đổi dữ liệu và trộn thứ tự đáp án A-B-C-D
    $questions = $questionsCollection->map(function ($q) use ($practiceTest) {
        $data = $q->runtimeData();
        if ($practiceTest->shuffle_options && in_array($q->type, ['MultipleChoice', 'MultipleResponse'], true)) {
            $data['options'] = collect($data['options'])->shuffle()->values()->all();
        }
        return $data;
    })->values();

    return view('learning.launch', compact('practiceTest', 'questions'));
}`
        },
        {
            num: "3.3.3",
            title: "Xử lý thanh toán VietQR / PayOS và Webhook đồng bộ tự động kích hoạt hạn sử dụng",
            desc: "Đoạn mã trong PricingController.php tiếp nhận Webhook từ máy chủ PayOS, xác minh chữ ký điện tử HMAC-SHA256 để chống can thiệp giả mạo, cập nhật trạng thái đơn hàng sang active và kích hoạt thời hạn bản quyền cùng sĩ số học sinh cho tài khoản giáo viên:",
            code: `public function payosWebhook(Request $request): JsonResponse
{
    $payload = $request->all();
    $data = $payload['data'] ?? [];
    $orderCode = $data['orderCode'] ?? null;
    $signature = $payload['signature'] ?? '';

    // 1. Xác thực tính toàn vẹn của chữ ký số Webhook
    $checksumKey = config('services.payos.checksum_key');
    $expectedSignature = hash_hmac('sha256', json_encode($data, JSON_UNESCAPED_UNICODE), $checksumKey);
    if (!hash_equals($expectedSignature, $signature)) {
        Log::warning("PayOS Webhook invalid signature for order: {$orderCode}");
        return response()->json(['success' => false, 'message' => 'Invalid signature'], 403);
    }

    // 2. Tìm kiếm đơn hàng trong cơ sở dữ liệu
    $order = PackageOrder::where('code', (string)$orderCode)->first();
    if ($order && $order->status === 'pending') {
        DB::transaction(function () use ($order) {
            // 3. Cập nhật trạng thái đơn hàng thành công
            $order->update(['status' => 'active', 'activated_at' => now()]);
            $teacher = $order->user;

            // 4. Kích hoạt quyền giảng dạy và gia hạn thời gian
            $currentExpires = $teacher->expires_at && $teacher->expires_at > now() ? $teacher->expires_at : now();
            $teacher->update([
                'status' => 'active',
                'max_students' => max($teacher->max_students, $order->max_students),
                'expires_at' => Carbon::parse($currentExpires)->addDays($order->duration_days)
            ]);

            // 5. Gán quyền phụ trách Khối lớp của gói dịch vụ
            $levelIds = $order->package->levels()->pluck('levels.id')->all();
            $teacher->assignedLevels()->syncWithoutDetaching($levelIds);
        });
    }

    return response()->json(['success' => true]);
}`
        },
        {
            num: "3.3.4",
            title: "Hệ thống Live Chat Realtime Polling & Bắn thông báo sang Telegram Admin Bot",
            desc: "Đoạn mã xử lý gửi tin nhắn tư vấn từ widget chat trên website, lưu vào CSDL và gọi Telegram Bot API gửi tin nhắn thông báo tức thì đến nhóm kín của Ban Quản trị:",
            code: `public function sendSupportMessage(Request $request): JsonResponse
{
    $validated = $request->validate([
        'name' => 'required|string|max:100',
        'contact' => 'required|string|max:150',
        'message' => 'required|string|max:1000',
    ]);

    // 1. Lưu tin nhắn vào bảng support_messages
    $msg = SupportMessage::create([
        'user_id' => auth()->id(),
        'sender_name' => $validated['name'],
        'sender_contact' => $validated['contact'],
        'message' => $validated['message'],
        'ip_address' => $request->ip(),
        'status' => 'pending'
    ]);

    // 2. Soạn thông điệp và bắn sang Telegram Bot
    $botToken = GameSetting::get('telegram_bot_token', env('TELEGRAM_BOT_TOKEN'));
    $chatId = GameSetting::get('telegram_chat_id', env('TELEGRAM_CHAT_ID'));

    if ($botToken && $chatId) {
        $text = "🔔 *YÊU CẦU TƯ VẤN MỚI TỪ IC3 QUEST*\n"
              . "👤 *Khách hàng:* {$validated['name']}\n"
              . "📞 *Liên hệ:* {$validated['contact']}\n"
              . "💬 *Nội dung:* {$validated['message']}\n"
              . "⏰ *Thời gian:* " . now()->format('d/m/Y H:i:s');

        Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'Markdown'
        ]);
        $msg->update(['telegram_sent' => true]);
    }

    return response()->json(['success' => true, 'message' => 'Gửi tin nhắn tư vấn thành công!']);
}`
        },
        {
            num: "3.3.5",
            title: "Cơ chế Gamification: Khấu trừ thời gian chơi mini-game từng giây bằng AJAX & Chống gian lận",
            desc: "Đoạn mã trong LearningController.php xử lý kiểm soát thời gian chơi game của học sinh: nhận yêu cầu tiêu hao giây chơi game từ client, trừ số giây trong bảng users và tự động trả về cờ game_over nếu học sinh đã sử dụng hết hạn mức thời gian được đổi:",
            code: `public function consumeGameTime(Request $request): JsonResponse
{
    $validated = $request->validate(['seconds' => 'required|integer|min:1|max:600']);
    $user = $request->user();

    if (!$user) {
        return response()->json(['success' => false, 'message' => 'Chưa đăng nhập'], 401);
    }

    $currentSeconds = (int) ($user->game_time_seconds ?? 0);
    $deduct = min($currentSeconds, (int) $validated['seconds']);
    $remain = max(0, $currentSeconds - $deduct);

    // Cập nhật số giây còn lại
    $user->update(['game_time_seconds' => $remain]);

    return response()->json([
        'success' => true,
        'remaining_seconds' => $remain,
        'game_over' => ($remain <= 0)
    ]);
}`
        }
    ],

    testCases: [
        {
            code: "TC01",
            name: "Đăng nhập hệ thống phân quyền thành công (Admin)",
            input: "Email: admin@ic3.test, Mật khẩu: password123",
            expected: "Hệ thống xác thực thành công, tạo Session an toàn và redirect chính xác về Dashboard Quản trị (/quan-tri).",
            actual: "Đăng nhập thành công, chuyển hướng chính xác về trang Admin Dashboard với đầy đủ số liệu thống kê.",
            result: "PASS ✓"
        },
        {
            code: "TC02",
            name: "Đăng nhập thất bại do sai mật khẩu",
            input: "Email: admin@ic3.test, Mật khẩu: sai_mat_khau_123",
            expected: "Hệ thống từ chối đăng nhập, hiển thị thông báo lỗi 'Thông tin đăng nhập không chính xác', giữ nguyên dữ liệu form.",
            actual: "Hệ thống chặn truy cập, không tạo session và hiển thị thông báo lỗi màu đỏ rõ ràng.",
            result: "PASS ✓"
        },
        {
            code: "TC03",
            name: "Đăng nhập nhanh bằng Mã học sinh (student_code)",
            input: "Mã học sinh: HS001, Mật khẩu: password123",
            expected: "Hệ thống tìm thấy tài khoản học sinh An Nhiên và chuyển hướng về Cổng học tập học sinh (/).",
            actual: "Đăng nhập thành công, hiển thị đúng tên bé An Nhiên, số Sao tích lũy và lời chào trên Header.",
            result: "PASS ✓"
        },
        {
            code: "TC04",
            name: "Đăng ký thuê gói bản quyền và sinh mã VietQR",
            input: "Chọn Gói Tiêu Chuẩn (990.000đ), nhập thông tin giáo viên, bấm Đăng ký & Thuê gói.",
            expected: "Tạo tài khoản giáo viên, tạo đơn hàng MOS-YYYYMM-XXXXX (status=pending), sinh mã VietQR động chứa số tiền 990.000đ.",
            actual: "Đơn hàng được khởi tạo thành công, hiển thị trang checkout với mã VietQR quét được trên app ngân hàng.",
            result: "PASS ✓"
        },
        {
            code: "TC05",
            name: "Kích hoạt tự động đơn hàng qua Webhook PayOS",
            input: "Máy chủ PayOS gửi Webhook thanh toán thành công kèm mã đơn hàng và chữ ký số HMAC-SHA256.",
            expected: "Hệ thống verify chữ ký hợp lệ, cập nhật đơn hàng status=active, gia hạn expires_at và cấp quyền khối lớp cho giáo viên.",
            actual: "Đơn hàng kích hoạt tức thời, tài khoản giáo viên được mở khóa đầy đủ tính năng tạo lớp và học sinh.",
            result: "PASS ✓"
        },
        {
            code: "TC06",
            name: "Chấm điểm bài thi và cộng Sao thưởng (Điểm đạt chuẩn >= 700)",
            input: "Học sinh làm đúng 12/14 câu hỏi bài luyện 1 Khối 3 (đạt 857/1000 điểm), bấm Nộp bài.",
            expected: "Hệ thống tính điểm 857 điểm, đánh dấu is_passed=true, lưu vào test_attempts, tự động cộng 10 Sao vàng vào bảng users.",
            actual: "Bài thi được chấm chính xác 857 điểm, hiển thị Cup vàng chúc mừng, số Sao của học sinh tăng từ 100 lên 110 Sao.",
            result: "PASS ✓"
        },
        {
            code: "TC07",
            name: "Chấm điểm bài thi chưa đạt chuẩn (Điểm < 700)",
            input: "Học sinh làm đúng 6/14 câu hỏi (đạt 429/1000 điểm), bấm Nộp bài.",
            expected: "Hệ thống tính điểm 429 điểm, đánh dấu is_passed=false, lưu lịch sử nhưng KHÔNG cộng thêm Sao vàng.",
            actual: "Hệ thống ghi nhận kết quả 429 điểm, hiển thị lời khuyên cố gắng làm lại và không thay đổi số Sao tích lũy.",
            result: "PASS ✓"
        },
        {
            code: "TC08",
            name: "Thuật toán xáo trộn câu hỏi và đáp án (Shuffle)",
            input: "Hai học sinh A và B cùng mở phòng thi ảo một đề thi có bật tùy chọn Shuffle.",
            expected: "Thứ tự các câu hỏi và thứ tự các phương án lựa chọn A-B-C-D của học sinh A phải khác biệt so với học sinh B.",
            actual: "Giao diện làm bài của 2 học sinh hiển thị thứ tự ngẫu nhiên khác nhau, ngăn chặn hoàn toàn việc nhìn bài.",
            result: "PASS ✓"
        },
        {
            code: "TC09",
            name: "Đổi Sao lấy phút chơi mini-game giải trí",
            input: "Học sinh có 110 Sao, chọn đổi Gói 1 (30 Sao đổi 5 phút chơi game), bấm Xác nhận đổi.",
            expected: "Hệ thống trừ 30 Sao (còn lại 80 Sao), cộng 300 giây vào game_time_seconds và ghi nhận giao dịch game_transactions.",
            actual: "Số Sao giảm còn 80, thời gian chơi game tăng thêm 300 giây, mở khóa phòng chơi game thành công.",
            result: "PASS ✓"
        },
        {
            code: "TC10",
            name: "Tự động khóa màn hình game khi hết thời gian chơi",
            input: "Học sinh chơi game đến khi số giây chơi đếm lùi về 00:00.",
            expected: "Hệ thống gửi AJAX tiêu hao thời gian, phát hiện remaining_seconds=0, kích hoạt game_over và khóa màn hình game.",
            actual: "Màn hình game tự động dừng lại, hiển thị thông báo hết giờ và yêu cầu học sinh làm thêm bài thi để tích Sao.",
            result: "PASS ✓"
        }
    ],

    chapter4: {
        title: "Chương 4. KẾT LUẬN VÀ HƯỚNG PHÁT TRIỂN",
        content: `4.1. Đánh giá kết quả đồ án tốt nghiệp
4.1.1. Các kết quả chức năng đã đạt được
Sau 4 tháng tập trung nghiên cứu và phát triển nghiêm túc (từ tháng 3 đến tháng 6 năm 2026), đề tài đồ án môn học "Xây dựng hệ thống học tập và luyện thi chứng chỉ tin học quốc tế IC3 Spark & MOS trực tuyến (IC3 Quest)" đã hoàn thành xuất sắc 100% mục tiêu nghiên cứu và các yêu cầu chức năng đặt ra ban đầu:
- Xây dựng hoàn chỉnh website ứng dụng web chuyên nghiệp trên nền tảng công nghệ hiện đại PHP 8.3 và Framework Laravel 11.x, vận hành mượt mà trên môi trường máy chủ cục bộ Laragon.
- Chuẩn hóa và cài đặt thành công cơ sở dữ liệu quan hệ MySQL gồm 17 bảng logic 3NF, phục vụ trọn vẹn ngân hàng 509 câu hỏi chuẩn IC3 GS6, 35 bộ đề thi, 6 lớp học và lưu trữ toàn vẹn hơn 257 lượt làm bài thi thực nghiệm.
- Hiện thực hóa thành công mô hình Gamification giáo dục: Tích lũy Sao thưởng, Bảng xếp hạng Top 10 Hiệp sĩ nhí, 8 huy hiệu thành tích và cơ chế đổi Sao lấy phút chơi mini-game có kiểm soát thời gian tự động, tạo động lực to lớn cho học sinh tiểu học tự giác ôn luyện.
- Triển khai phân hệ Góc Phụ huynh (Parent Dashboard) với hệ thống biểu đồ trực quan (Line, Radar, Doughnut) và cơ chế cảnh báo sớm thông minh, kết nối chặt chẽ giữa Gia đình và Nhà trường.
- Tích hợp thành công giải pháp thanh toán điện tử chuẩn VietQR và cổng PayOS, cùng hệ thống chăm sóc khách hàng tự động qua Telegram Bot Webhook.
- Toàn bộ 10 kịch bản kiểm thử chất lượng (Test Cases) của hệ thống đều vượt qua thành công với tỷ lệ đạt 100% (PASS).

4.1.2. Các kỹ năng chuyên môn tích lũy
Quá trình triển khai đề tài đã giúp nhóm sinh viên tích lũy và nâng cao vượt bậc các năng lực chuyên môn của một kỹ sư công nghệ thông tin:
- Làm chủ kiến trúc ứng dụng web MVC trong Laravel Framework, nắm vững cách tổ chức Route, Middleware phân quyền, Eloquent ORM tối ưu truy vấn và Blade Component tái sử dụng.
- Nâng cao tư duy thiết kế hệ thống theo chuẩn UML, khả năng chuẩn hóa cơ sở dữ liệu quan hệ 3NF và tối ưu hóa chỉ mục Indexing trên MySQL.
- Làm chủ kỹ thuật thiết kế Frontend hiện đại với TailwindCSS, xây dựng các hiệu ứng 3D Cards tactile sống động, mang lại trải nghiệm người dùng đẳng cấp.
- Có kinh nghiệm thực chiến tích hợp API bên thứ ba: Cổng thanh toán trực tuyến VietQR / PayOS, xác minh chữ ký bảo mật HMAC-SHA256, tích hợp Telegram Bot API.
- Rèn luyện kỹ năng làm việc nhóm, phân chia công việc theo phương pháp Agile/Scrum và kỹ năng viết tài liệu kỹ thuật chuẩn mực.

4.1.3. Những hạn chế và thiếu sót còn tồn tại
Bên cạnh những kết quả to lớn đã đạt được, sản phẩm vẫn còn một số điểm hạn chế do giới hạn về thời gian và tài nguyên:
- Hệ thống hiện mới tập trung chuyên sâu cho chương trình IC3 GS6 Spark cấp tiểu học (Khối 3, 4, 5); ngân hàng câu hỏi thực hành mô phỏng các ứng dụng văn phòng MOS (Word, Excel, PowerPoint) cho học sinh lớn hơn vẫn đang trong giai đoạn tiếp tục cập nhật.
- Ứng dụng hiện mới tối ưu hóa trên nền web responsive, chưa xây dựng ứng dụng di động độc lập (Native Mobile App) trên iOS và Android.
- Chưa ứng dụng công nghệ Trí tuệ nhân tạo (AI) để phân tích hành vi và tự động đề xuất lộ trình ôn tập cá nhân hóa cho từng học sinh.

4.2. Định hướng và lộ trình phát triển trong tương lai
Nhằm phát triển IC3 Quest trở thành một nền tảng giáo dục số toàn diện, nhóm định hướng các mục tiêu mở rộng tiếp theo:
1. Mở rộng ngân hàng đề thi chứng chỉ MOS: Bổ sung các bài tập thực hành tương tác mô phỏng thao tác phần mềm Microsoft Word, Excel, PowerPoint phiên bản 2019/365.
2. Xây dựng Mobile App: Sử dụng Flutter để phát triển ứng dụng di động cho phụ huynh và học sinh trên cả hai nền tảng App Store và Google Play, hỗ trợ thông báo đẩy (Push Notification) thời gian thực.
3. Tích hợp Trí tuệ nhân tạo (AI Tutor): Nghiên cứu tích hợp mô hình ngôn ngữ lớn (LLM) để xây dựng Trợ lý ảo AI giải đáp thắc mắc bài tập cho học sinh 24/7 và tự động phát hiện lỗ hổng kiến thức để sinh đề thi bù đắp năng lực.
4. Triển khai môi trường đám mây (Cloud Deployment): Đưa hệ thống lên hạ tầng đám mây (AWS / DigitalOcean) kết hợp cân bằng tải Load Balancer và bộ nhớ đệm Redis để phục vụ đồng thời hàng chục ngàn học sinh dự thi trực tuyến.`
    },

    appendices: {
        appendixA: `Nội dung tệp cấu hình môi trường (.env) của hệ thống IC3 Quest:

APP_NAME="IC3 Quest"
APP_ENV=local
APP_KEY=base64:5HoHYVPhPjHMPVsAvC9zuO7p4BuDtZujZMXkNGzfBr8=
APP_DEBUG=true
APP_TIMEZONE=Asia/Ho_Chi_Minh
APP_URL=http://localhost/MOS/public

LOG_CHANNEL=stack
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=MOS
DB_USERNAME=root
DB_PASSWORD=

PAYOS_CLIENT_ID=your_payos_client_id
PAYOS_API_KEY=your_payos_api_key
PAYOS_CHECKSUM_KEY=your_payos_checksum_key

TELEGRAM_BOT_TOKEN=7891234567:AAHxxxxxxxxxxxxxxxxxxxxxxxxx
TELEGRAM_CHAT_ID=-100xxxxxxxxxx`,

        appendixB: `Hướng dẫn cài đặt và vận hành hệ thống trên môi trường máy chủ cục bộ (Laragon):

Bước 1: Cài đặt công cụ môi trường
- Tải và cài đặt phần mềm Laragon Full Edition (chứa Apache 2.4, MySQL 8.0, PHP 8.3).
- Cài đặt trình quản lý gói Composer và Node.js phiên bản LTS.

Bước 2: Triển khai mã nguồn
- Sao chép toàn bộ thư mục dự án MOS vào thư mục gốc của Laragon: C:\\laragon\\www\\MOS
- Khởi động máy chủ Apache và MySQL trên giao diện Laragon.

Bước 3: Cấu hình cơ sở dữ liệu
- Mở HeidiSQL hoặc phpMyAdmin trong Laragon, tạo cơ sở dữ liệu mới có tên là \`MOS\` với bảng mã \`utf8mb4_unicode_ci\`.
- Cấu hình các thông số kết nối CSDL trong tệp \`.env\`.

Bước 4: Chạy Migration và Seed dữ liệu mẫu
- Mở terminal dòng lệnh trong thư mục dự án và chạy các lệnh sau:
    composer install
    php artisan key:generate
    php artisan migrate --seed
    npm install
    npm run build

Bước 5: Truy cập và sử dụng hệ thống
- Mở trình duyệt web truy cập địa chỉ: http://localhost/MOS/public (hoặc tên miền ảo http://mos.test do Laragon tự tạo).
- Đăng nhập bằng các tài khoản kiểm thử mặc định:
    + Quản trị viên: admin@ic3.test / Mật khẩu: password123
    + Giáo viên: teacher@ic3.test / Mật khẩu: password123
    + Học sinh: hs001@student.ic3.local (Mã: HS001) / Mật khẩu: password123`
    },

    references: [
        { stt: "1", author: "Taylor Otwell", title: "Laravel Documentation (Version 11.x / 12.x)", publisher: "Laravel LLC", year: "2024", note: "Tài liệu kỹ thuật chính thức của Laravel Framework. URL: https://laravel.com/docs" },
        { stt: "2", author: "The PHP Group", title: "PHP 8.3 Documentation and Release Notes", publisher: "The PHP Group", year: "2024", note: "Tài liệu hướng dẫn ngôn ngữ lập trình PHP 8.3. URL: https://www.php.net/docs.php" },
        { stt: "3", author: "Oracle Corporation", title: "MySQL 8.0 Reference Manual", publisher: "Oracle Corporation", year: "2024", note: "Tài liệu hệ quản trị cơ sở dữ liệu MySQL 8.0. URL: https://dev.mysql.com/doc/" },
        { stt: "4", author: "Certiport - A Pearson VUE Business", title: "IC3 Digital Literacy Certification (GS6 Spark) Standards & Objectives", publisher: "Certiport Inc.", year: "2023", note: "Chuẩn kiến thức khảo thí Tin học quốc tế IC3 GS6." },
        { stt: "5", author: "Bộ Giáo dục và Đào tạo", title: "Thông tư số 32/2018/TT-BGDĐT ban hành Chương trình giáo dục phổ thông môn Tin học", publisher: "Bộ GD&ĐT Việt Nam", year: "2018", note: "Quy chuẩn khung chương trình tin học tiểu học." },
        { stt: "6", author: "PayOS Technical Team", title: "PayOS Open Payment Solution API Documentation", publisher: "PayOS Vietnam", year: "2024", note: "Tài liệu tích hợp cổng thanh toán trực tuyến PayOS. URL: https://payos.vn/docs/" },
        { stt: "7", author: "Công ty Cổ phần Thanh toán Quốc gia Việt Nam (NAPAS)", title: "Tiêu chuẩn kỹ thuật định danh thanh toán mã phản hồi nhanh VietQR", publisher: "NAPAS", year: "2022", note: "Tiêu chuẩn thanh toán VietQR quốc gia." },
        { stt: "8", author: "Telegram Messenger LLP", title: "Telegram Bot API Documentation", publisher: "Telegram", year: "2024", note: "Tài liệu kỹ thuật kết nối Webhook Telegram Bot. URL: https://core.telegram.org/bots/api" },
        { stt: "9", author: "Tailwind Labs Inc.", title: "TailwindCSS Documentation & 3D Styling Guides", publisher: "Tailwind Labs", year: "2024", note: "Tài liệu hướng dẫn thiết kế giao diện web TailwindCSS." },
        { stt: "10", author: "Nguyễn Văn Ba", title: "Giáo trình Phân tích và Thiết kế Hệ thống Thông tin theo hướng đối tượng với UML", publisher: "NXB Đại học Quốc gia Hà Nội", year: "2020", note: "Giáo trình phân tích ca sử dụng và sơ đồ lớp." }
    ]
};
