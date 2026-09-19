# 📚 TỪ ĐIỂN CƠ SỞ DỮ LIỆU TIẾNG VIỆT (HỆ THỐNG IC3 QUEST)

> **Mục đích**: Tài liệu này việt hóa chi tiết toàn bộ cấu trúc bảng (tables), các cột dữ liệu (columns), kiểu dữ liệu và ý nghĩa nghiệp vụ trong hệ thống **IC3 Quest — Quản trị & Thi thử Chuẩn Quốc tế IC3 GS6**.
>
> ⚠️ **Lưu ý kiến trúc**: Tên bảng và tên cột trong CSDL được duy trì bằng tiếng Anh chuẩn quy ước Laravel/Eloquent quốc tế để đảm bảo tính ổn định, tương thích ORM, auth guards và 100% bộ kiểm thử tự động (tests) chạy xanh. Mọi chú thích, mô tả hiển thị giao diện, tài liệu hướng dẫn và mô hình đều được việt hóa trực quan 100%.

---

## 📑 MỤC LỤC CÁC BẢNG DỮ LIỆU

1. [Bảng `users` (Tài Khoản Người Dùng)](#1-bảng-users-tài-khoản-người-dùng)
2. [Bảng `packages` (Gói Dịch Vụ & Bản Quyền Giáo Viên)](#2-bảng-packages-gói-dịch-vụ--bản-quyền-giáo-viên)
3. [Bảng `package_level` (Khối Lớp Cấp Quyền Của Gói)](#3-bảng-package_level-khối-lớp-cấp-quyền-của-gói)
4. [Bảng `package_orders` (Đơn Hàng Thuê Gói & Thanh Toán)](#4-bảng-package_orders-đơn-hàng-thuê-gói--thanh-toán)
5. [Bảng `support_messages` (Tin Nhắn Tư Vấn & Live Chat Telegram)](#5-bảng-support_messages-tin-nhắn-tư-vấn--live-chat-telegram)
6. [Bảng `programs` (Chương Trình Đào Tạo)](#6-bảng-programs-chương-trình-đào-tạo)
7. [Bảng `levels` (Khối Lớp / Cấp Độ)](#7-bảng-levels-khối-lớp--cấp-độ)
8. [Bảng `topics` (Chủ Đề Kiến Thức)](#8-bảng-topics-chủ-đề-kiến-thức)
9. [Bảng `practice_tests` (Ngân Hàng Đề Thi & Bài Luyện)](#9-bảng-practice_tests-ngân-hàng-đề-thi--bài-luyện)
10. [Bảng `questions` (Ngân Hàng Câu Hỏi)](#10-bảng-questions-ngân-hàng-câu-hỏi)
11. [Bảng `answers` (Phương Án Trả Lời)](#11-bảng-answers-phương-án-trả-lời)
12. [Bảng `test_attempts` (Lượt Thi & Lịch Sử Làm Bài)](#12-bảng-test_attempts-lượt-thi--lịch-sử-làm-bài)
13. [Bảng `classrooms` (Lớp Học Giáo Viên Phụ Trách)](#13-bảng-classrooms-lớp-học-giáo-viên-phụ-trách)
14. [Bảng `classroom_student` (Danh Sách Học Sinh Trong Lớp)](#14-bảng-classroom_student-danh-sách-học-sinh-trong-lớp)
15. [Bảng `teacher_level` (Quyền Khối Lớp Của Giáo Viên)](#15-bảng-teacher_level-quyền-khối-lớp-của-giáo-viên)
16. [Bảng `game_settings` (Cấu Hình Khu Trò Chơi & Đổi Quà)](#16-bảng-game_settings-cấu-hình-khu-trò-chơi--đổi-quà)
17. [Bảng `star_logs` (Nhật Ký Tích Lũy Ngôi Sao)](#17-bảng-star_logs-nhật-ký-tích-lũy-ngôi-sao)

---

### 1. Bảng `users` (Tài Khoản Người Dùng)
Bảng trung tâm lưu trữ toàn bộ người dùng trong hệ thống (Quản trị viên, Giáo viên, Học sinh).

| Tên Cột (Database) | Kiểu Dữ Liệu | Diễn Giải Tiếng Việt | Ghi Chú / Giá Trị Mẫu |
| :--- | :--- | :--- | :--- |
| `id` | BIGINT UNSIGNED | Mã định danh người dùng (Khóa chính) | Tự tăng (Auto Increment) |
| `name` | VARCHAR(255) | Họ và tên đầy đủ | Ví dụ: Cô Nguyễn Mai Linh |
| `email` | VARCHAR(255) | Email đăng nhập | Duy nhất (Unique) |
| `password` | VARCHAR(255) | Mật khẩu tài khoản | Mã hóa Bcrypt/Argon2 |
| `role` | VARCHAR(50) | Vai trò phân quyền | `admin` (Quản trị), `teacher` (Giáo viên), `student` (Học sinh) |
| `phone` | VARCHAR(20) | Số điện thoại liên hệ | Ví dụ: 0912345678 |
| `school_name` | VARCHAR(150) | Tên trường học / Đơn vị công tác | Ví dụ: TH Chu Văn An |
| `status` | VARCHAR(30) | Trạng thái tài khoản | `active` (Hoạt động), `locked` (Bị khóa), `pending` (Chờ duyệt) |
| `max_students` | INT | Sĩ số học sinh tối đa giáo viên được tạo | Mặc định 0, tăng theo gói bản quyền thuê |
| `expires_at` | TIMESTAMP | Hạn sử dụng bản quyền giáo viên | Ngày hết hạn quyền giảng dạy |
| `stars` | INT | Số lượng sao tích lũy (Học sinh) | Mặc định 0 |
| `remember_token` | VARCHAR(100) | Mã ghi nhớ phiên đăng nhập | Laravel Auth token |
| `created_at` | TIMESTAMP | Thời điểm tạo tài khoản | Ngày giờ tạo |
| `updated_at` | TIMESTAMP | Thời điểm cập nhật gần nhất | Ngày giờ cập nhật |

---

### 2. Bảng `packages` (Gói Dịch Vụ & Bản Quyền Giáo Viên)
Lưu thông tin các gói bản quyền được cấu hình và mở bán trên trang Bảng Giá.

| Tên Cột (Database) | Kiểu Dữ Liệu | Diễn Giải Tiếng Việt | Ghi Chú / Giá Trị Mẫu |
| :--- | :--- | :--- | :--- |
| `id` | BIGINT UNSIGNED | Mã định danh gói (Khóa chính) | Tự tăng |
| `name` | VARCHAR(100) | Tên gói dịch vụ | Ví dụ: Gói Tiêu Chuẩn (Standard) |
| `slug` | VARCHAR(120) | Đường dẫn thân thiện (URL Slug) | `goi-tieu-chuan-standard` (Unique) |
| `badge` | VARCHAR(50) | Huy hiệu nhãn nổi bật | `Phổ biến nhất ⭐`, `Trải nghiệm 🚀`, `Siêu tiết kiệm 👑` |
| `description` | TEXT | Mô tả ngắn gọn về gói | Diễn giải lợi ích cho giáo viên |
| `price` | DECIMAL(12,2) | Giá bán thực tế (VNĐ) | Ví dụ: 990000 |
| `original_price` | DECIMAL(12,2) | Giá gốc niêm yết trước giảm (VNĐ) | Ví dụ: 1490000 |
| `duration_days` | INT | Thời gian sử dụng (Số ngày) | 30 ngày, 90 ngày, 365 ngày |
| `max_students` | INT | Số lượng học sinh tối đa được quản lý | 35, 100, 300 học sinh (0 = Không giới hạn) |
| `features` | JSON | Danh sách tính năng của gói | Mảng chuỗi các gạch đầu dòng |
| `is_active` | BOOLEAN | Trạng thái mở bán | `true` (Đang bán), `false` (Ẩn/Tạm ngưng) |
| `sort_order` | INT | Thứ tự ưu tiên hiển thị | 1, 2, 3... |
| `created_at` | TIMESTAMP | Ngày tạo | |
| `updated_at` | TIMESTAMP | Ngày chỉnh sửa | |

---

### 3. Bảng `package_level` (Khối Lớp Cấp Quyền Của Gói)
Bảng trung gian liên kết đa-đa giữa gói dịch vụ và các khối lớp (`levels`) được mở khóa.

| Tên Cột (Database) | Kiểu Dữ Liệu | Diễn Giải Tiếng Việt |
| :--- | :--- | :--- |
| `package_id` | BIGINT UNSIGNED | Khóa ngoại tham chiếu tới `packages.id` |
| `level_id` | BIGINT UNSIGNED | Khóa ngoại tham chiếu tới `levels.id` (Khối 3, 4, hoặc 5) |

---

### 4. Bảng `package_orders` (Đơn Hàng Thuê Gói & Thanh Toán)
Lưu thông tin các đơn đăng ký mua gói của giáo viên, mã VietQR và trạng thái cổng PayOS.

| Tên Cột (Database) | Kiểu Dữ Liệu | Diễn Giải Tiếng Việt | Ghi Chú / Giá Trị Mẫu |
| :--- | :--- | :--- | :--- |
| `id` | BIGINT UNSIGNED | Mã đơn hàng (Khóa chính) | Tự tăng |
| `code` | VARCHAR(32) | Mã đơn hiển thị | Ví dụ: `ORD-ABC12345` |
| `user_id` | BIGINT UNSIGNED | Khóa ngoại tham chiếu tài khoản đặt mua (`users.id`) | |
| `package_id` | BIGINT UNSIGNED | Khóa ngoại tham chiếu gói mua (`packages.id`) | |
| `package_name` | VARCHAR(100) | Lưu tên gói tại thời điểm đặt | Tránh bị đổi nếu sửa gói |
| `price` | DECIMAL(12,2) | Số tiền phải thanh toán (VNĐ) | |
| `duration_days` | INT | Số ngày được cộng khi kích hoạt | |
| `max_students` | INT | Sĩ số được cấp quyền | |
| `status` | VARCHAR(30) | Trạng thái đơn hàng | `pending` (Chờ thanh toán), `active` (Đã kích hoạt), `cancelled` (Đã hủy) |
| `payment_method` | VARCHAR(30) | Phương thức thanh toán | `bank_transfer` (VietQR), `payos` (Cổng trực tuyến) |
| `notes` | TEXT | Ghi chú thêm từ khách hàng | |
| `admin_notes` | TEXT | Ghi chú xử lý của Quản trị viên | |
| `activated_at` | TIMESTAMP | Thời điểm đơn được kích hoạt | |
| `created_at` | TIMESTAMP | Thời điểm tạo đơn | |
| `updated_at` | TIMESTAMP | Thời điểm cập nhật đơn | |

---

### 5. Bảng `support_messages` (Tin Nhắn Tư Vấn & Live Chat Telegram)
Lưu tin nhắn của giáo viên gửi từ widget Live Chat trên web và trạng thái đồng bộ về Telegram Admin.

| Tên Cột (Database) | Kiểu Dữ Liệu | Diễn Giải Tiếng Việt | Ghi Chú |
| :--- | :--- | :--- | :--- |
| `id` | BIGINT UNSIGNED | Khóa chính tự tăng | |
| `user_id` | BIGINT UNSIGNED | Khóa ngoại người dùng (nếu đã đăng nhập) | Có thể NULL cho khách |
| `sender_name` | VARCHAR(100) | Tên người gửi tin nhắn | Ví dụ: Thầy Trần Văn Bình |
| `sender_contact`| VARCHAR(150) | Thông tin liên hệ (SĐT / Zalo / Email) | Ví dụ: 0987123456 |
| `message` | TEXT | Nội dung câu hỏi tư vấn | |
| `ip_address` | VARCHAR(45) | Địa chỉ IP của người gửi | Phòng chống spam |
| `status` | VARCHAR(30) | Trạng thái xử lý | `pending` (Chờ xử lý), `replied` (Đã phản hồi), `closed` (Đã đóng) |
| `telegram_sent` | BOOLEAN | Đã bắn thành công sang Telegram Admin chưa | `true` / `false` |
| `created_at` | TIMESTAMP | Thời điểm gửi tin nhắn | |
| `updated_at` | TIMESTAMP | Thời điểm cập nhật | |

---

### 6. Bảng `programs` (Chương Trình Đào Tạo)
| Tên Cột | Kiểu Dữ Liệu | Diễn Giải Tiếng Việt |
| :--- | :--- | :--- |
| `id` | BIGINT UNSIGNED | Mã chương trình học |
| `name` | VARCHAR(150) | Tên chương trình (VD: Chuẩn Quốc Tế IC3 GS6 Spark) |
| `code` | VARCHAR(50) | Mã định danh (VD: `ic3-gs6`) |
| `description` | TEXT | Mô tả tổng quan về chương trình học |

---

### 7. Bảng `levels` (Khối Lớp / Cấp Độ)
| Tên Cột | Kiểu Dữ Liệu | Diễn Giải Tiếng Việt |
| :--- | :--- | :--- |
| `id` | BIGINT UNSIGNED | Mã khối lớp |
| `program_id` | BIGINT UNSIGNED | Thuộc chương trình nào (`programs.id`) |
| `name` | VARCHAR(100) | Tên hiển thị (VD: IC3 GS6 Spark Level 1 — Khối 3) |
| `grade` | INT | Số khối lớp (3, 4, 5) |
| `order` | INT | Thứ tự sắp xếp |

---

### 8. Bảng `topics` (Chủ Đề Kiến Thức)
| Tên Cột | Kiểu Dữ Liệu | Diễn Giải Tiếng Việt |
| :--- | :--- | :--- |
| `id` | BIGINT UNSIGNED | Mã chủ đề |
| `level_id` | BIGINT UNSIGNED | Thuộc khối lớp nào (`levels.id`) |
| `name` | VARCHAR(150) | Tên chủ đề (VD: Điện toán căn bản, Các ứng dụng chủ chốt, Cuộc sống trực tuyến) |
| `code` | VARCHAR(50) | Mã chủ đề (VD: `computing_fundamentals`, `key_applications`, `living_online`) |

---

### 9. Bảng `practice_tests` (Ngân Hàng Đề Thi & Bài Luyện)
| Tên Cột | Kiểu Dữ Liệu | Diễn Giải Tiếng Việt | Ghi Chú |
| :--- | :--- | :--- | :--- |
| `id` | BIGINT UNSIGNED | Mã đề thi | Khóa chính |
| `level_id` | BIGINT UNSIGNED | Thuộc khối lớp nào | Khóa ngoại |
| `title` | VARCHAR(255) | Tiêu đề bài thi (VD: Đề Luyện Thi IC3 GS6 Cấp Tỉnh Số 1) | |
| `slug` | VARCHAR(255) | Đường dẫn thân thiện URL | |
| `description` | TEXT | Hướng dẫn và lưu ý làm bài | |
| `duration_minutes` | INT | Thời gian làm bài thi (phút) | Mặc định 45 hoặc 50 phút |
| `pass_score` | INT | Điểm số đạt yêu cầu (trên thang điểm 1000) | Chuẩn quốc tế thường là 700/1000 |
| `access_code` | VARCHAR(50) | Mã bảo vệ phòng thi (nếu bật chế độ thi mật khẩu) | |
| `shuffle_questions`| BOOLEAN | Tự động xáo trộn thứ tự câu hỏi | Chống nhìn bài |
| `shuffle_answers` | BOOLEAN | Tự động xáo trộn các phương án trả lời A, B, C, D | |
| `status` | VARCHAR(30) | Trạng thái đề thi (`published`, `draft`) | |

---

### 10. Bảng `questions` (Ngân Hàng Câu Hỏi)
| Tên Cột | Kiểu Dữ Liệu | Diễn Giải Tiếng Việt |
| :--- | :--- | :--- |
| `id` | BIGINT UNSIGNED | Mã câu hỏi |
| `practice_test_id` | BIGINT UNSIGNED | Thuộc đề thi nào |
| `topic_id` | BIGINT UNSIGNED | Thuộc chủ đề nào |
| `content` | TEXT | Nội dung đề bài câu hỏi |
| `type` | VARCHAR(50) | Thể loại: Trắc nghiệm (`multiple_choice`), Mô phỏng kéo thả, v.v. |
| `points` | INT | Điểm số của câu hỏi |
| `explanation` | TEXT | Lời giải chi tiết sau khi học sinh nộp bài |

---

### 11. Bảng `answers` (Phương Án Trả Lời)
| Tên Cột | Kiểu Dữ Liệu | Diễn Giải Tiếng Việt |
| :--- | :--- | :--- |
| `id` | BIGINT UNSIGNED | Mã phương án |
| `question_id` | BIGINT UNSIGNED | Khóa ngoại câu hỏi |
| `content` | TEXT | Nội dung phương án (A, B, C, D) |
| `is_correct` | BOOLEAN | Đáp án đúng (`true`) hay sai (`false`) |
| `order` | INT | Thứ tự hiển thị |

---

### 12. Bảng `test_attempts` (Lượt Thi & Lịch Sử Làm Bài)
| Tên Cột | Kiểu Dữ Liệu | Diễn Giải Tiếng Việt |
| :--- | :--- | :--- |
| `id` | BIGINT UNSIGNED | Mã lượt thi |
| `user_id` | BIGINT UNSIGNED | Học sinh nào thực hiện lượt thi |
| `practice_test_id` | BIGINT UNSIGNED | Đề thi đã làm |
| `score` | DECIMAL(5,2) | Điểm số đạt được |
| `total_correct` | INT | Số câu trả lời đúng |
| `total_questions` | INT | Tổng số câu hỏi của bài |
| `is_passed` | BOOLEAN | Đạt chuẩn hay Chưa đạt (`true`/`false`) |
| `started_at` | TIMESTAMP | Thời gian bắt đầu bấm làm bài |
| `completed_at` | TIMESTAMP | Thời gian nộp bài kết thúc |

---

### 13. Bảng `classrooms` (Lớp Học Giáo Viên Phụ Trách)
| Tên Cột | Kiểu Dữ Liệu | Diễn Giải Tiếng Việt |
| :--- | :--- | :--- |
| `id` | BIGINT UNSIGNED | Mã lớp học |
| `teacher_id` | BIGINT UNSIGNED | Giáo viên chủ nhiệm / phụ trách (`users.id`) |
| `level_id` | BIGINT UNSIGNED | Lớp thuộc khối mấy (`levels.id`) |
| `name` | VARCHAR(100) | Tên lớp học (VD: Lớp 3A1 - Năm học 2026) |
| `code` | VARCHAR(20) | Mã tham gia lớp của học sinh (VD: `LOP3A1-2026`) |

---

### 14. Bảng `game_settings` (Cấu Hình Trò Chơi)
| Tên Cột | Kiểu Dữ Liệu | Diễn Giải Tiếng Việt |
| :--- | :--- | :--- |
| `id` | BIGINT UNSIGNED | Mã cấu hình |
| `key` | VARCHAR(100) | Tên khóa cấu hình |
| `value` | TEXT / JSON | Giá trị thiết lập trò chơi (số sao cần để mở khóa, lượt quay thưởng, v.v.) |

---

### 15. Bảng `star_logs` (Nhật Ký Sao Thưởng)
| Tên Cột | Kiểu Dữ Liệu | Diễn Giải Tiếng Việt |
| :--- | :--- | :--- |
| `id` | BIGINT UNSIGNED | Mã giao dịch sao |
| `user_id` | BIGINT UNSIGNED | Học sinh nhận hoặc tiêu sao |
| `amount` | INT | Số sao cộng (+) hoặc trừ (-) |
| `reason` | VARCHAR(255) | Lý do (VD: Hoàn thành bài thi xuất sắc đạt 1000 điểm) |

---

## 🎯 TỔNG KẾT
Tài liệu này được đồng bộ với phiên bản CSDL hiện hành và được bảo trì cùng với mã nguồn. Mọi thay đổi về cấu trúc bảng hoặc thêm bảng mới trong tương lai sẽ được cập nhật tiếp tại đây.
