# QUY TẮC PHÁT TRIỂN & PHONG CÁCH CODE CHO DỰ ÁN (PROJECT CODING GUIDELINES)

Tài liệu quy chuẩn luôn được áp dụng cho toàn bộ dự án MOS (IC3 Digital Adventure):

## 1. Ưu tiên Code & Chú thích Tiếng Việt
- Toàn bộ ghi chú code, docblock, nhãn UI, thông báo lỗi (messages) và trao đổi với người dùng đều bằng Tiếng Việt chuẩn mực, rõ ràng, dễ hiểu.
- Văn phong giao diện phù hợp với học sinh tiểu học, giáo viên và phụ huynh: thân thiện, khích lệ và chuẩn ngôn ngữ giáo dục số.

## 2. Chuẩn Laravel & Tái sử dụng (Reusable Components)
- Ưu tiên tạo và sử dụng Blade Components trong `resources/views/components/` hoặc partials cho các phần tử lặp lại (thẻ khối lớp, thẻ bài học, thẻ thống kê, nút bấm 3D, thanh tiến độ...).
- Tách biệt CSS dùng chung vào `resources/css/game-theme.css`, tránh copy-paste hàng trăm dòng `<style>` trùng lặp giữa các view.
- Tuân thủ chuẩn PSR-12, RESTful routing, Model Eloquent quan hệ chuẩn chỉ.

## 3. Tái sử dụng & Kết nối Dữ liệu Động từ Database (DB-Driven)
- Tất cả các khối lớp (`Level`), chủ đề (`Topic`), bài luyện (`PracticeTest`), số sao (`TestAttempt`), thời gian chơi (`GameSetting`), gói dịch vụ (`Package`) đều được truy xuất trực tiếp từ Database.
- Tuyệt đối không fix cứng dữ liệu giả (hardcoded mock data) khi Database đã có cấu trúc và quan hệ sẵn có.

## 4. Giao diện Đa Sắc Màu & Thẻ 3D Card Đẹp Mắt (Gamified 3D UI)
- Thiết kế phong cách game phiêu lưu giáo dục rực rỡ, vui tươi và tràn đầy năng lượng.
- Thẻ 3D (3D Cards) có viền nổi `border: 3.5px solid #ffffff`, bóng đổ nổi khối sâu `box-shadow: 0 16px 36px rgba(0,0,0,0.18), inset 0 -6px 0 rgba(0,0,0,0.15)`.
- Nút bấm tactile xúc giác: nổi lên khi hover (`translateY(-2px)`) và lún xuống chân thực khi bấm (`active: translateY(4px)`).
- Bảng màu phong phú: Cam hổ phách, Xanh ngọc lục bảo, Xanh biển Sky Blue, Tím thạch anh, Hồng ngọc Neon, Vàng kim Hoàng gia.

## 5. Chuẩn Logic Thông Minh trên Tất Cả Các Luồng (All Flows)
- **Trang chủ học sinh (`learning.home`)**: Hero banner sinh động, 4 Thẻ lối tắt Action Hub 3D, Lưới khối lớp động, Thanh tiến độ ngày và thử thách tuần.
- **Kho bài học & Khối lớp (`learning.programs` & `learning.level`)**: Lưới 3D card khối lớp, Bản đồ 7 chủ đề trực quan, mở rộng/thu gọn mượt mà, bộ lọc tìm kiếm instant search không giật lag.
- **Phòng thi & Chuẩn bị (`learning.test` & `learning.launch`)**: Thẻ giới thiệu arcade 3D, 4 khối thống kê, thanh tiến độ câu hỏi, phản hồi âm thanh và kết quả 1000 điểm chuẩn IIG.
- **Bảng thành tích & Game (`learning.achievements` & `learning.games`)**: 3 Pod năng lượng, Cửa hàng đổi giờ chơi mini-game, 8 Huy hiệu danh giá, Bảng xếp hạng Top 10 Hiệp sĩ nhí.
- **Góc Phụ Huynh (`learning.parent-dashboard`)**: Biểu đồ trực quan, phân tích điểm yếu và cảnh báo con làm sai nhiều lần thông minh.
- **Khung dùng chung (`layouts.app`)**: Header Topbar 3D phiêu lưu, sidebar đa sắc màu, modal hướng dẫn sao vàng.
