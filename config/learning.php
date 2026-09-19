<?php

/**
 * Cấu hình chuẩn mực học tập IC3 GS6
 *
 * ĐÂY LÀ SOURCE OF TRUTH cho toàn bộ logic phân tích học tập.
 *
 * PHÂN BIỆT HAI KHÁI NIỆM:
 *  - Game Rule (pass_score = max_score = 1000): Lưu trong DB, dùng trong phòng thi.
 *    Học sinh phải đạt 1000/1000 mới "vượt màn" trong game.
 *  - Learning Milestone (700/1000): Chuẩn IIG IC3 GS6 dùng trong báo cáo phụ huynh.
 *    Là ngưỡng xác định học sinh đạt năng lực tối thiểu theo chuẩn quốc tế.
 *
 * Service ParentLearningAnalyticsService dùng Learning Milestone cho tất cả phân tích.
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Ngưỡng điểm số (Scoring Thresholds)
    |--------------------------------------------------------------------------
    */

    // Mốc đạt chuẩn năng lực IC3 (Learning Milestone — khác với Game Rule 1000)
    'pass_score' => 700,

    // Mốc xuất sắc
    'excellent_score' => 900,

    // Điểm tối đa thang điểm
    'max_score' => 1000,

    // Ngưỡng "Cần hỗ trợ khẩn cấp" — điểm dưới mức này sau nhiều lần thử
    'critical_score_threshold' => 400,

    /*
    |--------------------------------------------------------------------------
    | Ngưỡng hành vi học tập (Behavior Thresholds)
    |--------------------------------------------------------------------------
    */

    // Số lượt làm tối thiểu để xác nhận "Chủ đề thế mạnh" (không phải may mắn)
    'min_attempts_for_strength' => 3,

    // Số lượt làm tối thiểu để xác định "Đang tiến bộ"
    'min_attempts_for_trend' => 2,

    // Số giây dưới ngưỡng này bị coi là "làm bài quá nhanh"
    'fast_attempt_threshold_seconds' => 60,

    // Tỷ lệ câu đúng dưới ngưỡng này kèm với "làm nhanh" → ghi nhận cảnh báo thời gian
    'min_accuracy_for_fast_attempt' => 0.4,

    /*
    |--------------------------------------------------------------------------
    | Cấu hình giao diện (UI Config)
    |--------------------------------------------------------------------------
    */

    // Số bài tối đa hiển thị trên line chart (tránh chart quá dày đặc)
    'line_chart_max_attempts' => 15,

    // Số bài tối đa hiển thị trong danh sách efficiency
    'efficiency_max_items' => 6,

    // Số ngày hiển thị trong calendar heatmap
    'calendar_heatmap_days' => 30,

    /*
    |--------------------------------------------------------------------------
    | Múi giờ hiển thị (Display Timezone)
    |--------------------------------------------------------------------------
    |
    | App timezone = UTC (config/app.php line 68).
    | Người dùng ở Việt Nam (UTC+7).
    |
    | Dùng timezone này để:
    | - Tính streak ngày học (tránh lệch ngày khi học lúc 00:00-06:59 giờ VN)
    | - Hiển thị calendar heatmap đúng theo giờ Việt Nam
    | - Tính "hôm nay / hôm qua" cho lastPracticeText
    |
    */
    'display_timezone' => 'Asia/Ho_Chi_Minh',

];
