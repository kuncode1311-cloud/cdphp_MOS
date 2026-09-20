// Báo cáo đặc tả chi tiết 17 bảng Cơ sở dữ liệu chuẩn MySQL cho Báo cáo Đồ án

export const databaseTables = [
    {
        num: "2.14",
        name: "users",
        meaning: "Tài khoản người dùng (Admin, Giáo viên, Học sinh)",
        columns: [
            { col: "id", type: "BIGINT UNSIGNED", key: "PK", null: "NO", def: "AUTO_INCREMENT", desc: "Mã định danh duy nhất của tài khoản người dùng" },
            { col: "name", type: "VARCHAR(255)", key: "None", null: "NO", def: "–", desc: "Họ và tên đầy đủ của người dùng" },
            { col: "email", type: "VARCHAR(255)", key: "UNIQUE", null: "NO", def: "–", desc: "Địa chỉ email đăng nhập hệ thống" },
            { col: "student_code", type: "VARCHAR(255)", key: "UNIQUE", null: "YES", def: "NULL", desc: "Mã số học sinh phục vụ tính năng đăng nhập nhanh (VD: HS001)" },
            { col: "password", type: "VARCHAR(255)", key: "None", null: "NO", def: "–", desc: "Mật khẩu tài khoản đã mã hóa một chiều bằng Bcrypt" },
            { col: "role", type: "VARCHAR(50)", key: "INDEX", null: "NO", def: "'student'", desc: "Vai trò phân quyền: 'admin' (Quản trị), 'teacher' (Giáo viên), 'student' (Học sinh)" },
            { col: "phone", type: "VARCHAR(20)", key: "None", null: "YES", def: "NULL", desc: "Số điện thoại liên hệ / Zalo của người dùng" },
            { col: "school_name", type: "VARCHAR(150)", key: "None", null: "YES", def: "NULL", desc: "Trường học hoặc đơn vị công tác (dành cho giáo viên/học sinh)" },
            { col: "classroom_id", type: "BIGINT UNSIGNED", key: "FK", null: "YES", def: "NULL", desc: "Khóa ngoại liên kết bảng classrooms (lớp học của học sinh)" },
            { col: "reward_stars", type: "INT UNSIGNED", key: "None", null: "NO", def: "0", desc: "Ví Sao tích lũy được từ các bài thi đạt chuẩn (để đổi giờ chơi game)" },
            { col: "game_time_seconds", type: "INT UNSIGNED", key: "None", null: "NO", def: "0", desc: "Thời gian chơi mini-game còn lại (đơn vị: giây)" },
            { col: "max_students", type: "INT UNSIGNED", key: "None", null: "NO", def: "0", desc: "Hạn mức sĩ số học sinh tối đa giáo viên được phép tạo theo gói bản quyền" },
            { col: "expires_at", type: "DATE / TIMESTAMP", key: "None", null: "YES", def: "NULL", desc: "Hạn sử dụng bản quyền giảng dạy của giáo viên" },
            { col: "status", type: "VARCHAR(30)", key: "None", null: "NO", def: "'active'", desc: "Trạng thái tài khoản: 'active' (Hoạt động), 'locked' (Khóa), 'pending' (Chờ duyệt)" },
            { col: "remember_token", type: "VARCHAR(100)", key: "None", null: "YES", def: "NULL", desc: "Mã token ghi nhớ phiên đăng nhập tự động" },
            { col: "created_at", type: "TIMESTAMP", key: "None", null: "YES", def: "NULL", desc: "Thời điểm tài khoản được khởi tạo" },
            { col: "updated_at", type: "TIMESTAMP", key: "None", null: "YES", def: "NULL", desc: "Thời điểm tài khoản cập nhật thông tin gần nhất" }
        ]
    },
    {
        num: "2.15",
        name: "packages",
        meaning: "Danh mục Gói dịch vụ & Bản quyền phần mềm dành cho Giáo viên",
        columns: [
            { col: "id", type: "BIGINT UNSIGNED", key: "PK", null: "NO", def: "AUTO_INCREMENT", desc: "Mã định danh gói dịch vụ bản quyền" },
            { col: "name", type: "VARCHAR(100)", key: "None", null: "NO", def: "–", desc: "Tên gói dịch vụ (Ví dụ: Gói Tiêu Chuẩn - Standard)" },
            { col: "slug", type: "VARCHAR(120)", key: "UNIQUE", null: "NO", def: "–", desc: "Đường dẫn URL thân thiện định danh gói (VD: goi-tieu-chuan-standard)" },
            { col: "badge", type: "VARCHAR(50)", key: "None", null: "YES", def: "NULL", desc: "Huy hiệu nhãn nổi bật (Phổ biến nhất, Trải nghiệm, Siêu tiết kiệm)" },
            { col: "description", type: "TEXT", key: "None", null: "YES", def: "NULL", desc: "Mô tả ngắn gọn về giá trị và lợi ích gói đem lại cho giáo viên" },
            { col: "price", type: "DECIMAL(12,2)", key: "None", null: "NO", def: "0.00", desc: "Giá bán thực tế của gói sau khi áp dụng ưu đãi (VNĐ)" },
            { col: "original_price", type: "DECIMAL(12,2)", key: "None", null: "YES", def: "NULL", desc: "Giá gốc niêm yết trước giảm giá (VNĐ)" },
            { col: "duration_days", type: "INT UNSIGNED", key: "None", null: "NO", def: "30", desc: "Thời hạn sử dụng bản quyền tính bằng số ngày (30, 90, 365 ngày)" },
            { col: "max_students", type: "INT UNSIGNED", key: "None", null: "NO", def: "35", desc: "Số lượng học sinh tối đa giáo viên được phép quản lý (0 = Không giới hạn)" },
            { col: "features", type: "JSON", key: "None", null: "YES", def: "NULL", desc: "Mảng JSON chứa danh sách các tính năng nổi bật của gói" },
            { col: "is_active", type: "BOOLEAN", key: "None", null: "NO", def: "1", desc: "Trạng thái mở bán: 1 (Đang bán), 0 (Ẩn/Tạm ngưng)" },
            { col: "sort_order", type: "INT", key: "None", null: "NO", def: "0", desc: "Thứ tự hiển thị ưu tiên trên trang Bảng giá" },
            { col: "created_at", type: "TIMESTAMP", key: "None", null: "YES", def: "NULL", desc: "Thời điểm tạo gói" },
            { col: "updated_at", type: "TIMESTAMP", key: "None", null: "YES", def: "NULL", desc: "Thời điểm cập nhật gói" }
        ]
    },
    {
        num: "2.16",
        name: "package_level",
        meaning: "Bảng trung gian liên kết Gói dịch vụ và Khối lớp được cấp quyền",
        columns: [
            { col: "package_id", type: "BIGINT UNSIGNED", key: "FK", null: "NO", def: "–", desc: "Khóa ngoại tham chiếu đến packages.id (Xóa xếp tầng CASCADE)" },
            { col: "level_id", type: "BIGINT UNSIGNED", key: "FK", null: "NO", def: "–", desc: "Khóa ngoại tham chiếu đến levels.id (Khối 3, 4 hoặc 5)" }
        ]
    },
    {
        num: "2.17",
        name: "package_orders",
        meaning: "Quản lý Đơn hàng thuê gói & Lịch sử thanh toán VietQR / PayOS",
        columns: [
            { col: "id", type: "BIGINT UNSIGNED", key: "PK", null: "NO", def: "AUTO_INCREMENT", desc: "Mã định danh đơn hàng thanh toán" },
            { col: "code", type: "VARCHAR(32)", key: "UNIQUE", null: "NO", def: "–", desc: "Mã đơn hàng hiển thị giao dịch (Ví dụ: MOS-202609-OQBPB)" },
            { col: "user_id", type: "BIGINT UNSIGNED", key: "FK", null: "NO", def: "–", desc: "Khóa ngoại liên kết tài khoản giáo viên đặt mua (users.id)" },
            { col: "package_id", type: "BIGINT UNSIGNED", key: "FK", null: "NO", def: "–", desc: "Khóa ngoại liên kết gói dịch vụ được đặt (packages.id)" },
            { col: "package_name", type: "VARCHAR(100)", key: "None", null: "NO", def: "–", desc: "Lưu tên gói tại thời điểm đặt để bảo toàn lịch sử giao dịch" },
            { col: "price", type: "DECIMAL(12,2)", key: "None", null: "NO", def: "0.00", desc: "Số tiền thanh toán thực tế của đơn hàng (VNĐ)" },
            { col: "duration_days", type: "INT UNSIGNED", key: "None", null: "NO", def: "30", desc: "Số ngày được gia hạn khi kích hoạt đơn hàng thành công" },
            { col: "max_students", type: "INT UNSIGNED", key: "None", null: "NO", def: "35", desc: "Sĩ số học sinh được cấp phép khi kích hoạt gói" },
            { col: "status", type: "VARCHAR(30)", key: "INDEX", null: "NO", def: "'pending'", desc: "Trạng thái đơn: 'pending' (Chờ thanh toán), 'active' (Đã kích hoạt), 'cancelled' (Đã hủy)" },
            { col: "payment_method", type: "VARCHAR(30)", key: "None", null: "NO", def: "'vietqr'", desc: "Phương thức thanh toán: 'vietqr' (Chuyển khoản VietQR), 'payos' (Cổng trực tuyến)" },
            { col: "notes", type: "TEXT", key: "None", null: "YES", def: "NULL", desc: "Ghi chú thêm từ phía giáo viên khi đặt mua" },
            { col: "admin_notes", type: "TEXT", key: "None", null: "YES", def: "NULL", desc: "Ghi chú phê duyệt của Quản trị viên khi kích hoạt thủ công" },
            { col: "activated_at", type: "TIMESTAMP", key: "None", null: "YES", def: "NULL", desc: "Thời điểm đơn hàng được kích hoạt thành công" },
            { col: "created_at", type: "TIMESTAMP", key: "None", null: "YES", def: "NULL", desc: "Thời điểm khởi tạo đơn hàng" },
            { col: "updated_at", type: "TIMESTAMP", key: "None", null: "YES", def: "NULL", desc: "Thời điểm cập nhật trạng thái đơn" }
        ]
    },
    {
        num: "2.18",
        name: "support_messages",
        meaning: "Tin nhắn tư vấn Live Chat từ Website & Bắn thông báo Telegram",
        columns: [
            { col: "id", type: "BIGINT UNSIGNED", key: "PK", null: "NO", def: "AUTO_INCREMENT", desc: "Mã định danh tin nhắn tư vấn" },
            { col: "user_id", type: "BIGINT UNSIGNED", key: "FK", null: "YES", def: "NULL", desc: "Khóa ngoại liên kết người dùng (nếu đã đăng nhập, NULL nếu là khách)" },
            { col: "sender_name", type: "VARCHAR(100)", key: "None", null: "NO", def: "–", desc: "Họ và tên người gửi câu hỏi tư vấn" },
            { col: "sender_contact", type: "VARCHAR(150)", key: "None", null: "NO", def: "–", desc: "Thông tin liên hệ của khách hàng (Số điện thoại / Zalo / Email)" },
            { col: "message", type: "TEXT", key: "None", null: "NO", def: "–", desc: "Nội dung câu hỏi tư vấn gửi từ widget Live Chat" },
            { col: "ip_address", type: "VARCHAR(45)", key: "None", null: "YES", def: "NULL", desc: "Địa chỉ IP máy khách nhằm phòng chống spam gửi tin tự động" },
            { col: "status", type: "VARCHAR(30)", key: "None", null: "NO", def: "'pending'", desc: "Trạng thái xử lý: 'pending' (Chờ xử lý), 'replied' (Đã phản hồi), 'closed' (Đã đóng)" },
            { col: "telegram_sent", type: "BOOLEAN", key: "None", null: "NO", def: "0", desc: "Đánh dấu đã bắn thành công thông báo sang Telegram Admin Bot chưa" },
            { col: "created_at", type: "TIMESTAMP", key: "None", null: "YES", def: "NULL", desc: "Thời điểm gửi câu hỏi" },
            { col: "updated_at", type: "TIMESTAMP", key: "None", null: "YES", def: "NULL", desc: "Thời điểm cập nhật trạng thái phản hồi" }
        ]
    },
    {
        num: "2.19",
        name: "programs",
        meaning: "Chương trình đào tạo chuẩn quốc tế (IC3 Spark / MOS)",
        columns: [
            { col: "id", type: "BIGINT UNSIGNED", key: "PK", null: "NO", def: "AUTO_INCREMENT", desc: "Mã định danh chương trình đào tạo" },
            { col: "name", type: "VARCHAR(150)", key: "None", null: "NO", def: "–", desc: "Tên chương trình (VD: Chuẩn Quốc Tế IC3 GS6 Spark)" },
            { col: "slug", type: "VARCHAR(100)", key: "UNIQUE", null: "NO", def: "–", desc: "Đường dẫn thân thiện URL của chương trình (VD: ic3-gs6)" },
            { col: "code", type: "VARCHAR(50)", key: "UNIQUE", null: "NO", def: "–", desc: "Mã code định danh kỹ thuật (VD: ic3_gs6_spark)" },
            { col: "description", type: "TEXT", key: "None", null: "YES", def: "NULL", desc: "Mô tả tổng quan về chuẩn khảo thí quốc tế của chương trình" },
            { col: "created_at", type: "TIMESTAMP", key: "None", null: "YES", def: "NULL", desc: "Thời điểm tạo chương trình" },
            { col: "updated_at", type: "TIMESTAMP", key: "None", null: "YES", def: "NULL", desc: "Thời điểm cập nhật thông tin" }
        ]
    },
    {
        num: "2.20",
        name: "levels",
        meaning: "Khối lớp đào tạo (Khối 3, Khối 4, Khối 5)",
        columns: [
            { col: "id", type: "BIGINT UNSIGNED", key: "PK", null: "NO", def: "AUTO_INCREMENT", desc: "Mã định danh Khối lớp đào tạo" },
            { col: "program_id", type: "BIGINT UNSIGNED", key: "FK", null: "NO", def: "–", desc: "Khóa ngoại liên kết bảng programs (chương trình trực thuộc)" },
            { col: "name", type: "VARCHAR(100)", key: "None", null: "NO", def: "–", desc: "Tên hiển thị (VD: IC3 GS6 Spark Level 1 — Khối 3)" },
            { col: "slug", type: "VARCHAR(120)", key: "UNIQUE", null: "NO", def: "–", desc: "Đường dẫn slug thân thiện (khoi-3-spark-level-1)" },
            { col: "grade", type: "TINYINT UNSIGNED", key: "INDEX", null: "NO", def: "3", desc: "Số khối lớp đào tạo (3, 4 hoặc 5)" },
            { col: "order", type: "INT", key: "None", null: "NO", def: "1", desc: "Thứ tự sắp xếp hiển thị trên lưới khối lớp" },
            { col: "created_at", type: "TIMESTAMP", key: "None", null: "YES", def: "NULL", desc: "Thời điểm tạo khối lớp" },
            { col: "updated_at", type: "TIMESTAMP", key: "None", null: "YES", def: "NULL", desc: "Thời điểm cập nhật" }
        ]
    },
    {
        num: "2.21",
        name: "topics",
        meaning: "7 Chủ đề kiến thức chuẩn quốc tế IC3 GS6 của mỗi Khối lớp",
        columns: [
            { col: "id", type: "BIGINT UNSIGNED", key: "PK", null: "NO", def: "AUTO_INCREMENT", desc: "Mã định danh chủ đề kiến thức" },
            { col: "level_id", type: "BIGINT UNSIGNED", key: "FK", null: "NO", def: "–", desc: "Khóa ngoại liên kết bảng levels (thuộc Khối lớp nào)" },
            { col: "name", type: "VARCHAR(150)", key: "None", null: "NO", def: "–", desc: "Tên gọi chủ đề kiến thức (VD: Điện toán căn bản, Các ứng dụng chủ chốt)" },
            { col: "code", type: "VARCHAR(50)", key: "None", null: "NO", def: "–", desc: "Mã code định danh chủ đề (computing_fundamentals, key_applications)" },
            { col: "order", type: "INT", key: "None", null: "NO", def: "1", desc: "Thứ tự hiển thị trên bản đồ học tập trực quan" },
            { col: "created_at", type: "TIMESTAMP", key: "None", null: "YES", def: "NULL", desc: "Thời điểm tạo chủ đề" },
            { col: "updated_at", type: "TIMESTAMP", key: "None", null: "YES", def: "NULL", desc: "Thời điểm cập nhật" }
        ]
    },
    {
        num: "2.22",
        name: "practice_tests",
        meaning: "Ngân hàng 35 Bộ đề thi & Bài luyện tập theo từng Chủ đề",
        columns: [
            { col: "id", type: "BIGINT UNSIGNED", key: "PK", null: "NO", def: "AUTO_INCREMENT", desc: "Mã định danh bài luyện thi" },
            { col: "topic_id", type: "BIGINT UNSIGNED", key: "FK", null: "NO", def: "–", desc: "Khóa ngoại liên kết bảng topics (thuộc chủ đề nào)" },
            { col: "name", type: "VARCHAR(255)", key: "None", null: "NO", def: "–", desc: "Tên bài luyện thi (VD: Bài luyện 1, Đề thi thử số 1)" },
            { col: "slug", type: "VARCHAR(255)", key: "UNIQUE", null: "NO", def: "–", desc: "Đường dẫn URL bài thi (VD: k3-cd1-bai-1)" },
            { col: "duration_minutes", type: "INT UNSIGNED", key: "None", null: "NO", def: "0", desc: "Thời gian làm bài thi (phút, 0 = Không giới hạn thời gian)" },
            { col: "question_count", type: "INT UNSIGNED", key: "None", null: "NO", def: "14", desc: "Tổng số câu hỏi có trong bài thi này" },
            { col: "pass_score", type: "INT UNSIGNED", key: "None", null: "NO", def: "700", desc: "Điểm số tối thiểu để đạt chuẩn bài thi (thang 1000 điểm)" },
            { col: "max_score", type: "INT UNSIGNED", key: "None", null: "NO", def: "1000", desc: "Điểm số tối đa của bài thi chuẩn IIG (1000 điểm)" },
            { col: "difficulty", type: "VARCHAR(50)", key: "None", null: "NO", def: "'Cơ bản'", desc: "Mức độ thử thách của bài thi: Cơ bản, Trung bình, Nâng cao" },
            { col: "is_published", type: "BOOLEAN", key: "None", null: "NO", def: "1", desc: "Trạng thái công khai cho học sinh làm bài (1: Mở, 0: Khóa)" },
            { col: "shuffle_questions", type: "BOOLEAN", key: "None", null: "NO", def: "0", desc: "Tự động xáo trộn ngẫu nhiên thứ tự câu hỏi khi làm bài" },
            { col: "shuffle_options", type: "BOOLEAN", key: "None", null: "NO", def: "0", desc: "Tự động xáo trộn thứ tự các phương án trả lời A, B, C, D" },
            { col: "position", type: "INT", key: "None", null: "NO", def: "1", desc: "Vị trí sắp xếp của bài thi trong danh mục chủ đề" },
            { col: "created_at", type: "TIMESTAMP", key: "None", null: "YES", def: "NULL", desc: "Thời điểm tạo đề thi" },
            { col: "updated_at", type: "TIMESTAMP", key: "None", null: "YES", def: "NULL", desc: "Thời điểm cập nhật đề thi" }
        ]
    },
    {
        num: "2.23",
        name: "questions",
        meaning: "Ngân hàng 509 Câu hỏi trắc nghiệm & mô phỏng chuẩn IC3 GS6",
        columns: [
            { col: "id", type: "BIGINT UNSIGNED", key: "PK", null: "NO", def: "AUTO_INCREMENT", desc: "Mã định danh câu hỏi kiểm tra" },
            { col: "practice_test_id", type: "BIGINT UNSIGNED", key: "FK", null: "NO", def: "–", desc: "Khóa ngoại liên kết bảng practice_tests (thuộc bài thi nào)" },
            { col: "prompt", type: "TEXT", key: "None", null: "NO", def: "–", desc: "Nội dung câu hỏi đề bài" },
            { col: "type", type: "VARCHAR(50)", key: "None", null: "NO", def: "'MultipleChoice'", desc: "Loại câu hỏi: MultipleChoice (Một đáp án), MultipleResponse, Matching" },
            { col: "difficulty", type: "VARCHAR(50)", key: "None", null: "YES", def: "NULL", desc: "Mức độ khó của câu hỏi" },
            { col: "explanation", type: "TEXT", key: "None", null: "YES", def: "NULL", desc: "Lời giải thích chi tiết đáp án hiển thị sau khi nộp bài" },
            { col: "metadata", type: "JSON", key: "None", null: "YES", def: "NULL", desc: "Dữ liệu JSON lưu trữ các cấu hình chuyên sâu của câu hỏi" },
            { col: "is_published", type: "BOOLEAN", key: "None", null: "NO", def: "1", desc: "Trạng thái công khai của câu hỏi (1: Hiển thị, 0: Bản nháp)" },
            { col: "position", type: "INT", key: "None", null: "NO", def: "1", desc: "Thứ tự câu hỏi trong đề thi" },
            { col: "created_at", type: "TIMESTAMP", key: "None", null: "YES", def: "NULL", desc: "Thời điểm tạo câu hỏi" },
            { col: "updated_at", type: "TIMESTAMP", key: "None", null: "YES", def: "NULL", desc: "Thời điểm cập nhật" }
        ]
    },
    {
        num: "2.24",
        name: "question_options",
        meaning: "Phương án lựa chọn trả lời (A, B, C, D) của câu hỏi",
        columns: [
            { col: "id", type: "BIGINT UNSIGNED", key: "PK", null: "NO", def: "AUTO_INCREMENT", desc: "Mã định danh phương án trả lời" },
            { col: "question_id", type: "BIGINT UNSIGNED", key: "FK", null: "NO", def: "–", desc: "Khóa ngoại liên kết bảng questions (Xóa xếp tầng CASCADE)" },
            { col: "label", type: "VARCHAR(255)", key: "None", null: "NO", def: "–", desc: "Nhãn hiển thị phương án (A, B, C, D hoặc nội dung văn bản)" },
            { col: "value", type: "VARCHAR(255)", key: "None", null: "YES", def: "NULL", desc: "Giá trị định danh của phương án" },
            { col: "is_correct", type: "BOOLEAN", key: "None", null: "NO", def: "0", desc: "Đánh dấu đáp án đúng: 1 (Chính xác), 0 (Sai)" },
            { col: "position", type: "INT", key: "None", null: "NO", def: "1", desc: "Thứ tự hiển thị phương án" },
            { col: "metadata", type: "JSON", key: "None", null: "YES", def: "NULL", desc: "Dữ liệu JSON bổ trợ (cho câu hỏi ghép nối Matching)" }
        ]
    },
    {
        num: "2.25",
        name: "question_assets",
        meaning: "Tệp hình ảnh & Tài nguyên đính kèm minh họa đề thi",
        columns: [
            { col: "id", type: "BIGINT UNSIGNED", key: "PK", null: "NO", def: "AUTO_INCREMENT", desc: "Mã định danh tệp media" },
            { col: "question_id", type: "BIGINT UNSIGNED", key: "FK", null: "NO", def: "–", desc: "Khóa ngoại liên kết bảng questions (câu hỏi sở hữu tệp)" },
            { col: "type", type: "VARCHAR(50)", key: "None", null: "NO", def: "'image'", desc: "Loại tệp: 'image' (Hình ảnh PNG/JPG/WebP), 'audio' (Âm thanh)" },
            { col: "url", type: "VARCHAR(255)", key: "None", null: "NO", def: "–", desc: "Đường dẫn lưu trữ tệp trên máy chủ (/storage/questions/...)" },
            { col: "caption", type: "VARCHAR(255)", key: "None", null: "YES", def: "NULL", desc: "Chú thích tệp hình ảnh hiển thị dưới đề bài" },
            { col: "position", type: "INT", key: "None", null: "NO", def: "1", desc: "Thứ tự ưu tiên hiển thị hình ảnh" }
        ]
    },
    {
        num: "2.26",
        name: "test_attempts",
        meaning: "Lượt thi & Lịch sử nộp bài kiểm tra của Học sinh",
        columns: [
            { col: "id", type: "BIGINT UNSIGNED", key: "PK", null: "NO", def: "AUTO_INCREMENT", desc: "Mã định danh lượt làm bài thi" },
            { col: "user_id", type: "BIGINT UNSIGNED", key: "FK", null: "NO", def: "–", desc: "Khóa ngoại liên kết tài khoản học sinh làm bài (users.id)" },
            { col: "practice_test_id", type: "BIGINT UNSIGNED", key: "FK", null: "NO", def: "–", desc: "Khóa ngoại liên kết đề thi đã làm (practice_tests.id)" },
            { col: "score", type: "INT UNSIGNED", key: "INDEX", null: "NO", def: "0", desc: "Điểm số đạt được quy đổi theo thang 1000 điểm chuẩn IIG" },
            { col: "correct_answers", type: "INT UNSIGNED", key: "None", null: "NO", def: "0", desc: "Số lượng câu hỏi trả lời chính xác" },
            { col: "total_questions", type: "INT UNSIGNED", key: "None", null: "NO", def: "0", desc: "Tổng số câu hỏi của bài luyện thi" },
            { col: "duration_seconds", type: "INT UNSIGNED", key: "None", null: "NO", def: "0", desc: "Thời gian làm bài thực tế của học sinh (đơn vị: giây)" },
            { col: "is_passed", type: "BOOLEAN", key: "None", null: "NO", def: "0", desc: "Đạt chuẩn kỳ thi: 1 (Đạt >= 700 điểm), 0 (Chưa đạt)" },
            { col: "answers_payload", type: "JSON", key: "None", null: "YES", def: "NULL", desc: "Chuỗi JSON lưu chi tiết các câu trả lời học sinh đã chọn để xem lại" },
            { col: "started_at", type: "TIMESTAMP", key: "None", null: "YES", def: "NULL", desc: "Thời điểm bấm bắt đầu làm bài" },
            { col: "completed_at", type: "TIMESTAMP", key: "INDEX", null: "YES", def: "NULL", desc: "Thời điểm nộp bài và hoàn thành chấm điểm" },
            { col: "created_at", type: "TIMESTAMP", key: "None", null: "YES", def: "NULL", desc: "Thời điểm ghi nhận lượt thi" },
            { col: "updated_at", type: "TIMESTAMP", key: "None", null: "YES", def: "NULL", desc: "Thời điểm cập nhật" }
        ]
    },
    {
        num: "2.27",
        name: "classrooms",
        meaning: "Danh sách Lớp học do Giáo viên chủ nhiệm phụ trách",
        columns: [
            { col: "id", type: "BIGINT UNSIGNED", key: "PK", null: "NO", def: "AUTO_INCREMENT", desc: "Mã định danh lớp học" },
            { col: "name", type: "VARCHAR(255)", key: "None", null: "NO", def: "–", desc: "Tên lớp học (Ví dụ: Lớp 3A1, Lớp 4A1, Lớp 5A1)" },
            { col: "grade", type: "TINYINT UNSIGNED", key: "INDEX", null: "NO", def: "3", desc: "Khối lớp của học sinh (3, 4 hoặc 5)" },
            { col: "school_year", type: "VARCHAR(50)", key: "None", null: "YES", def: "NULL", desc: "Niên khóa học tập (Ví dụ: 2026-2027)" },
            { col: "teacher_id", type: "BIGINT UNSIGNED", key: "FK", null: "YES", def: "NULL", desc: "Khóa ngoại liên kết giáo viên chủ nhiệm phụ trách (users.id)" },
            { col: "created_at", type: "TIMESTAMP", key: "None", null: "YES", def: "NULL", desc: "Thời điểm tạo lớp học" },
            { col: "updated_at", type: "TIMESTAMP", key: "None", null: "YES", def: "NULL", desc: "Thời điểm cập nhật" }
        ]
    },
    {
        num: "2.28",
        name: "teacher_level",
        meaning: "Bảng trung gian phân quyền Khối lớp cho Giáo viên giảng dạy",
        columns: [
            { col: "teacher_id", type: "BIGINT UNSIGNED", key: "FK", null: "NO", def: "–", desc: "Khóa ngoại liên kết tài khoản giáo viên (users.id)" },
            { col: "level_id", type: "BIGINT UNSIGNED", key: "FK", null: "NO", def: "–", desc: "Khóa ngoại liên kết Khối lớp được phép phụ trách (levels.id)" }
        ]
    },
    {
        num: "2.29",
        name: "game_transactions",
        meaning: "Nhật ký biến động Sao thưởng và Thời gian chơi game",
        columns: [
            { col: "id", type: "BIGINT UNSIGNED", key: "PK", null: "NO", def: "AUTO_INCREMENT", desc: "Mã định danh giao dịch game" },
            { col: "user_id", type: "BIGINT UNSIGNED", key: "FK", null: "NO", def: "–", desc: "Khóa ngoại liên kết tài khoản học sinh (users.id)" },
            { col: "type", type: "VARCHAR(50)", key: "None", null: "NO", def: "–", desc: "Loại giao dịch: 'earn_star' (Nhận sao), 'exchange_package' (Đổi giờ chơi)" },
            { col: "stars_change", type: "INT", key: "None", null: "NO", def: "0", desc: "Số lượng sao cộng (+) hoặc trừ (-)" },
            { col: "time_seconds_change", type: "INT", key: "None", null: "NO", def: "0", desc: "Số giây thời gian chơi game được cộng (+)" },
            { col: "description", type: "VARCHAR(255)", key: "None", null: "NO", def: "–", desc: "Diễn giải chi tiết lý do biến động (VD: Hoàn thành bài thi đạt chuẩn)" },
            { col: "created_at", type: "TIMESTAMP", key: "None", null: "YES", def: "NULL", desc: "Thời điểm thực hiện giao dịch" }
        ]
    },
    {
        num: "2.30",
        name: "game_settings",
        meaning: "Bảng cấu hình hệ thống & Tham số Khu trò chơi dạng Key - Value",
        columns: [
            { col: "id", type: "BIGINT UNSIGNED", key: "PK", null: "NO", def: "AUTO_INCREMENT", desc: "Mã định danh thiết lập hệ thống" },
            { col: "key", type: "VARCHAR(100)", key: "UNIQUE", null: "NO", def: "–", desc: "Tên khóa cấu hình hệ thống (VD: game_enabled, telegram_bot_token)" },
            { col: "value", type: "TEXT", key: "None", null: "YES", def: "NULL", desc: "Giá trị cấu hình lưu dưới dạng chuỗi văn bản hoặc mảng JSON" },
            { col: "description", type: "VARCHAR(255)", key: "None", null: "YES", def: "NULL", desc: "Diễn giải ý nghĩa tham số cấu hình phục vụ quản trị" },
            { col: "created_at", type: "TIMESTAMP", key: "None", null: "YES", def: "NULL", desc: "Thời điểm tạo cấu hình" },
            { col: "updated_at", type: "TIMESTAMP", key: "None", null: "YES", def: "NULL", desc: "Thời điểm cập nhật cấu hình" }
        ]
    }
];
