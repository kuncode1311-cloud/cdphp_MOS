<?php

/**
 * Trò chuyện bằng giọng nói với Trợ lý AI.
 * Nghe: ưu tiên nhận dạng giọng nói có sẵn trong trình duyệt (miễn phí); máy không hỗ trợ thì gửi âm thanh ngắn lên máy chủ.
 * Nghĩ: Trợ lý AI (9Router, dự phòng Gemini). Đọc: giọng tiếng Việt có sẵn của trình duyệt.
 */
return [
    // Số lượt hỏi bằng giọng nói tối đa mỗi tài khoản mỗi ngày (chặn lạm dụng, tiết kiệm chi phí AI)
    'daily_turns' => (int) env('VOICE_DAILY_TURNS', 150),

    // Độ dài tối đa một câu nói được xử lý (ký tự) và dung lượng âm thanh gửi lên máy chủ (KB)
    'max_text_length' => 500,
    'max_audio_kb' => 2048,

    // Thông tin về chính trợ lý và hệ thống: AI tự gọi hàm thong_tin_tro_ly để đọc khi được hỏi (ai tạo ra, tên gì, làm được gì...)
    'about' => [
        'ten_tro_ly' => env('VOICE_ASSISTANT_NAME', 'Cô Trợ lý AI của IC3 Adventure'),
        'he_thong' => 'IC3 Adventure — nền tảng học và luyện thi chứng chỉ IC3 GS6 cho học sinh tiểu học',
        'nguoi_tao' => env('VOICE_CREATOR_NAME', 'Lê Minh Trí'),
        'biet_danh_nguoi_tao' => env('VOICE_CREATOR_NICKNAME', 'Trí Kun đẹp zai cute phô mai que'),
        'lam_duoc' => [
            'Trò chuyện bằng giọng nói tiếng Việt',
            'Tra cứu kết quả học tập, tiến bộ, câu sai của chính người dùng',
            'Ra câu hỏi luyện tập từ ngân hàng đề và chấm ngay',
            'Mở các trang trong hệ thống',
            'Hướng dẫn cách dùng hệ thống',
        ],
    ],
];
