---
name: vietnamese-laravel-3d-ui
description: >-
  Hướng dẫn và quy chuẩn thiết kế giao diện Laravel tiếng Việt: tạo Blade components tái sử dụng,
  3D card đa sắc màu rực rỡ phong cách game giáo dục, kết nối động dữ liệu từ Database,
  logic thông minh và nhất quán cho mọi luồng màn hình. Kích hoạt khi phát triển hoặc nâng cấp UI/UX.
---

# Quy Chuẩn Giao Diện 3D Card Đa Sắc Màu & Laravel Tiếng Việt

Tài liệu này định nghĩa phong cách lập trình và thiết kế giao diện chuẩn hóa cho dự án, giúp AI và lập trình viên luôn đồng nhất tư duy, tái sử dụng tối đa và mang lại trải nghiệm game hóa (gamified) trực quan, đẹp mắt cho người dùng.

---

## 1. 5 Nguyên Tắc Cốt Lõi

1. **Chuẩn Laravel & Tái sử dụng tối đa**:
   - Sử dụng Blade Components (`resources/views/components/`) hoặc Partials cho các thành phần lặp lại: Thẻ khối lớp, Thẻ chủ đề, Thẻ bài luyện, Thẻ thống kê (Stat Pod), Header Capsule.
   - Tránh copy-paste style hàng trăm dòng giữa các view. Tập trung các class dùng chung vào `resources/css/game-theme.css` hoặc component style.

2. **Ngôn ngữ Tiếng Việt chuẩn mực & Thân thiện**:
   - Toàn bộ nhãn, chú thích code (docblock, inline comments), thông báo lỗi và chuỗi hiển thị đều dùng Tiếng Việt tự nhiên, phù hợp với học sinh tiểu học, phụ huynh và giáo viên.
   - Thuật ngữ nhất quán: *Nhà thám hiểm, Hiệp sĩ IC3, Sao vàng, Bài luyện tập, Thử thách số, Góc Phụ Huynh*.

3. **Dữ liệu động từ Cơ Sở Dữ Liệu (Database-driven)**:
   - Tất cả thẻ, số liệu, tiến độ, thời gian thi, số câu hỏi, độ khó, trạng thái mở khóa đều truy xuất từ Eloquent Models (`Level`, `Topic`, `PracticeTest`, `TestAttempt`, `GameSetting`, `User`).
   - Không fix cứng danh sách bài học hay số sao tĩnh trong HTML.

4. **Giao diện 3D Card rực rỡ & Hiệu ứng Xúc giác (Tactile Feel)**:
   - **Border nổi 3D**: `border: 3.5px solid #ffffff` hoặc viền màu tương phản.
   - **Đổ bóng nổi khối**: `box-shadow: 0 14px 32px rgba(0, 0, 0, 0.18), inset 0 -6px 0 rgba(0, 0, 0, 0.15)`.
   - **Xúc giác bấm nút (Tactile click)**:
     - Hover: `transform: translateY(-4px) scale(1.02);`
     - Active / Click: `transform: translateY(3px); box-shadow: inset 0 -2px 0 rgba(0,0,0,0.2), 0 4px 8px rgba(0,0,0,0.1);`
   - **Orb Icon 3D**: Vòng tròn icon có gradient, viền trắng nổi và emoji / icon drop-shadow.

5. **Logic Thông Minh cho Mọi Luồng (All Flows)**:
   - Kiểm tra quyền truy cập (`canAccessLevel`, `isStudent`, `canAccessAdmin`).
   - Bộ lọc tìm kiếm thời gian thực (instant search) không giật lag.
   - Thanh tiến độ động tính % chuẩn xác, đếm ngược thời gian thông minh.

---

## 2. Bảng Phối Màu 3D Card Sinh Động (Color Palette)

| Khối / Chủ đề | Tên chủ đề / Khối | Gradient nền 3D Card | Màu chữ nút / Accent |
| :--- | :--- | :--- | :--- |
| **Khối 1** | Khởi đầu số | `linear-gradient(180deg, #f59e0b, #d97706)` | Vàng cam hổ phách (`#b45309`) |
| **Khối 2** | Khám phá số | `linear-gradient(180deg, #14b8a6, #0f766e)` | Xanh mòng két Teal (`#0f766e`) |
| **Khối 3 (Spark 1)** | Căn bản máy tính | `linear-gradient(180deg, #10b981, #059669)` | Lục bảo Emerald (`#065f46`) |
| **Khối 4 (Spark 2)** | Ứng dụng & Dữ liệu | `linear-gradient(180deg, #0ea5e9, #0284c7)` | Xanh biển Sky Blue (`#0369a1`) |
| **Khối 5 (Spark 3)** | Mạng & An toàn số | `linear-gradient(180deg, #8b5cf6, #6d28d9)` | Tím thạch anh Purple (`#5b21b6`) |
| **Chủ đề Bứt phá** | Sáng tạo số | `linear-gradient(180deg, #f43f5e, #be123c)` | Đỏ hồng Ruby (`#9f1239`) |
| **Chủ đề Chinh phục** | Tư duy thuật toán | `linear-gradient(180deg, #6366f1, #4338ca)` | Xanh hoàng gia Indigo (`#3730a3`) |
| **Cửa hàng / Sao** | Đổi vé mini-game | `linear-gradient(180deg, #ffc048, #ff9f1a)` | Vàng kim Gold (`#78350f`) |

---

## 3. Cấu Trúc CSS 3D Card Mẫu Chuẩn

```css
/* Thẻ 3D Card game hóa mẫu chuẩn */
.card-3d-adventure {
    border-radius: 28px;
    padding: 26px 22px;
    border: 4px solid #ffffff;
    box-shadow: 0 16px 36px rgba(0, 0, 0, 0.18), inset 0 -7px 0 rgba(0, 0, 0, 0.14);
    transition: transform 0.2s cubic-bezier(0.175, 0.885, 0.32, 1.275), box-shadow 0.2s ease;
    will-change: transform;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    position: relative;
}

.card-3d-adventure:hover {
    transform: translateY(-6px) scale(1.02);
    box-shadow: 0 24px 48px rgba(0, 0, 0, 0.25), inset 0 -7px 0 rgba(0, 0, 0, 0.14);
}

/* Nút bấm 3D Tactile */
.btn-3d-tactile {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 13px 24px;
    border-radius: 18px;
    font-weight: 1000;
    font-size: 14.5px;
    text-decoration: none;
    border: 3px solid #ffffff;
    box-shadow: 0 6px 0 rgba(0, 0, 0, 0.2), 0 10px 20px rgba(0, 0, 0, 0.15);
    transition: all 0.15s ease;
    cursor: pointer;
}

.btn-3d-tactile:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 0 rgba(0, 0, 0, 0.2), 0 14px 25px rgba(0, 0, 0, 0.2);
}

.btn-3d-tactile:active {
    transform: translateY(4px);
    box-shadow: 0 2px 0 rgba(0, 0, 0, 0.2), 0 4px 8px rgba(0, 0, 0, 0.1);
}
```

---

## 4. Danh Sách Các Luồng Cần Đảm Bảo Nhất Quán

1. **Trang Chủ (`learning.home`)**:
   - Hero banner động, 4 Thẻ lối tắt Action Hub 3D, Lưới khối lớp động, Thanh tiến độ ngày và thử thách tuần.
2. **Kho Bài Học (`learning.programs`)**:
   - Capsule header, Danh sách khối lớp 3D card với trạng thái mở khóa từ DB.
3. **Bản Đồ 7 Chủ Đề (`learning.level`)**:
   - 7 Card chủ đề 7 màu rực rỡ, tính năng mở rộng/thu gọn mượt mà, bộ lọc instant search, thẻ bài luyện 3D với chi tiết số câu và thời gian.
4. **Giới Thiệu Bài Luyện (`learning.test`)**:
   - Briefing card arcade, 4 khối thống kê 3D (Thời gian, Số câu, Điểm chuẩn, Thưởng sao), nút Bắt đầu thử thách 3D.
5. **Bảng Thành Tích & Đổi Thưởng (`learning.achievements`)**:
   - 3 Pod năng lượng, Cửa hàng đổi giờ chơi game hiệp sĩ, Hệ thống 8 huy hiệu danh giá, Bảng xếp hạng Top 10 học sinh.
6. **Khu Trò Chơi (`learning.games`)**:
   - Thẻ game Hiệp Sĩ Song Kiếm nổi bật, bảng đếm ngược giờ chơi và kết nối đổi sao.
7. **Góc Phụ Huynh (`learning.parent-dashboard`)**:
   - Thống kê năng lực 7 chủ đề, biểu đồ radar/cột trực quan, cảnh báo các bài làm sai nhiều lần.
