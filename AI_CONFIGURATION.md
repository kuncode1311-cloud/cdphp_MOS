# Cấu hình AI soạn đề

Phần soạn đề ưu tiên API riêng tương thích Chat Completions. Khi lỗi kết nối,
hết thời gian chờ, lỗi HTTP hoặc câu hỏi trả về không hợp lệ, hệ thống chuyển
sang Gemini AI Studio và tiếp tục xoay các khóa Gemini hiện có.

Thêm vào `.env` trên máy chạy MOS:

```dotenv
QUESTION_AI_BASE_URL=https://api-trikun.up.railway.app/v1
QUESTION_AI_API_KEY=
QUESTION_AI_MODEL=ag/gemini-3.8-flash-low
QUESTION_AI_TIMEOUT=60
QUESTION_AI_CONNECT_TIMEOUT=10
```

- `QUESTION_AI_MODEL`: điền đúng tên model hoặc nhóm model trong 9Router.
- `QUESTION_AI_API_KEY`: khóa của 9Router, có thể để trống nếu máy chủ không yêu cầu xác thực.
- URL phải kết thúc bằng `/v1`, không phải `/dashboard` hoặc `/chat/completions`.
- Giữ `GEMINI_API_KEY` hoặc `GEMINI_API_KEYS` hiện có để dự phòng.
- Sau khi đổi `.env`, chạy `php artisan config:clear`.

Khi chưa điền URL hoặc model, MOS tiếp tục dùng Gemini. Khi API riêng hoạt động,
không cần khóa Gemini để soạn đề. Kết quả `[]` hợp lệ được trả về giao diện để
giáo viên bổ sung nội dung, không gọi thêm dịch vụ dự phòng.

MOS không đặt cứng số token đầu ra; model tự áp dụng giới hạn tối đa của nhà cung cấp.

Ảnh được gửi dưới dạng `image_url`, PDF dưới dạng `file` chứa dữ liệu base64.
Cần chọn model có khả năng đọc loại tài liệu tương ứng. PDF chỉ được tải lên
Google nếu phải chuyển sang Gemini.

Nếu Railway hiển thị `No running instances` hoặc `Application failed to respond`,
cần xem nhật ký triển khai và khôi phục dịch vụ 9Router trước khi kiểm tra kết nối
thực tế từ MOS. Đổi cấu hình MOS không khởi động lại dịch vụ Railway.

Tham khảo: [API 9Router](https://github.com/decolua/9router/blob/master/README.md).
