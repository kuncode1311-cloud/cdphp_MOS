# 📦 KHO LƯU TRỮ DỮ LIỆU ĐỀ THI GỐC (IC3 LEGACY SOURCE BACKUP)

## 1. Tệp tin này là gì?
- File: `legacy_ic3_source.zip` (Dung lượng: ~57 MB)
- Chứa toàn bộ các gói đề thi iSpring HTML5 gốc của 3 Khối:
  - Khối 3 (Grade 3): 11 bài luyện thi
  - Khối 4 (Grade 4): 13 bài luyện thi
  - Khối 5 (Grade 5): 11 bài luyện thi
  - File danh mục: `manifest.json`

## 2. Vì sao được gom vào đây?
- Toàn bộ 509 câu hỏi và ảnh minh họa đã được trích xuất hoàn tất và lưu độc lập vào CSDL MySQL của MOS.
- Thư mục gốc `public/legacy/` chứa hơn 35 thư mục con và hàng ngàn file làm rác cây thư mục dự án và tốn dung lượng `public/`.
- Do đó, toàn bộ dữ liệu gốc được gom gọn gàng vào tệp nén duy nhất này để cất giữ an toàn.

## 3. Khi nào cần dùng lại?
- Khi cần **clone lại dữ liệu từ đầu** hoặc chạy lại lệnh:
  ```bash
  php artisan ic3:import-questions
  ```
- Khi chạy lệnh trên, hệ thống sẽ tự động tìm thấy file nén này trong `storage/legacy_backup/` và giải nén phục vụ import nếu cần.
- Hoặc anh/chị có thể giải nén thủ công file `legacy_ic3_source.zip` vào thư mục `public/legacy/`.
