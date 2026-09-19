# 🎮 IC3 QUEST — HỆ THỐNG LUYỆN THI & HỌC TẬP TIN HỌC CHUẨN QUỐC TẾ IC3 SPARK & IC3 GS6

<p align="center">
  <img src="public/images/ic3-quest-logo.png" width="180" alt="IC3 Quest Logo">
</p>

<p align="center">
  <b>Nền tảng giáo dục số hóa phong cách Game Phiêu Lưu (Gamified Education) dành cho Học sinh Tiểu học, Giáo viên và Phụ huynh</b>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel">
  <img src="https://img.shields.io/badge/PHP-8.3-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP">
  <img src="https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL">
  <img src="https://img.shields.io/badge/Railway-Deploy-0B0D0E?style=for-the-badge&logo=railway&logoColor=white" alt="Railway">
  <img src="https://img.shields.io/badge/PayOS-VietQR-0052CC?style=for-the-badge" alt="PayOS">
  <img src="https://img.shields.io/badge/Telegram-Bot_API-26A5E4?style=for-the-badge&logo=telegram&logoColor=white" alt="Telegram">
</p>

---

## 🌟 1. Tổng Quan Dự Án

**IC3 QUEST** là hệ thống luyện thi chứng chỉ tin học quốc tế **IC3 Spark (Dành cho Tiểu học: Khối 3, 4, 5)** và **IC3 GS6** được thiết kế theo phương pháp giáo dục số hóa hiện đại:
- **Gamification (Game hóa)**: Biến việc ôn luyện thi khô khan thành chuyến thám hiểm thú vị với hệ thống tích sao vàng, mở khóa khối lớp, bảng xếp hạng và các trò chơi mini-game giáo dục tương tác thị giác máy tính AI (MediaPipe).
- **Chuẩn hóa IIG Quốc Tế**: Ngân hàng đề thi bám sát chuẩn kiến thức IIG thế giới, thang điểm 1000 điểm, tự động phân tích lỗ hổng kiến thức và gợi ý ôn tập thông minh.
- **Tích hợp thông minh**: Kết nối Cổng thanh toán trực tuyến **PayOS / VietQR** tự động kích hoạt gói bản quyền và **Telegram Bot AI** thông báo đơn hàng tức thì cho Quản trị viên.

🔗 **Link Demo Trực Tuyến**: [https://web-production-8a808e.up.railway.app](https://web-production-8a808e.up.railway.app)

---

## 🎯 2. Các Phân Hệ & Tính Năng Cốt Lõi

### 👶 Phân Hệ Học Sinh (Student Journey)
- **Bản Đồ 3 Khối Lớp**: Khối 3 (*IC3 Spark Level 1*), Khối 4 (*Level 2*), Khối 5 (*Level 3*).
- **Kho 7 Chủ Đề Kiến Thức**: Máy tính & Thiết bị, Phần mềm & Ứng dụng, Mạng & Internet, An toàn thông tin số, v.v.
- **Phòng Thi Trực Quan**:
  - Giao diện thi trắc nghiệm, hình ảnh hotspot tương tác chân thực.
  - Phản hồi âm thanh sống động khi chọn đúng/sai.
  - Chấm điểm chuẩn thang 1000 điểm của IIG kèm xếp loại và phân tích chi tiết từng câu làm sai.
- **Khu Vui Chơi & Tích Sao Đổi Quà**:
  - Tích lũy Sao Vàng sau mỗi bài thi đạt điểm cao.
  - Đổi sao lấy thời gian trải nghiệm **Mini-game tương tác cử chỉ AI (MediaPipe Hand/Pose Tracking)** và game giải đố giáo dục.
  - Bảng xếp hạng **Top 10 Hiệp Sĩ Nhí** và bộ sưu tập **8 Huy Hiệu Danh Giá**.

### 👩‍🏫 Phân Hệ Giáo Viên (Teacher Hub)
- **Quản lý Sĩ số & Học sinh**: Cấp quyền mở khóa khối lớp, theo dõi tiến độ từng em trong lớp chủ nhiệm.
- **Báo Cáo & Phân Tích Thông Minh**:
  - Biểu đồ radar năng lực phân tích điểm mạnh / lỗ hổng kiến thức của từng chủ đề.
  - Thống kê tỷ lệ đạt chuẩn, danh sách học sinh cần hỗ trợ ôn tập thêm.
- **Bảng Giá & Thuê Gói Bản Quyền**:
  - Thuê các gói bản quyền theo lớp/học kỳ: *Gói Khởi Đầu (35 HS)*, *Gói Tiêu Chuẩn (100 HS)*, *Gói Trường Học (300 HS)*.
  - Thanh toán quét mã **VietQR** hoặc trực tuyến qua **PayOS**, kích hoạt tự động tức thì.
  - Quản lý lịch sử đơn hàng, xem hóa đơn và gia hạn gói.

### 🛡️ Phân Hệ Quản Trị Viên (Admin Management)
- **Executive Dashboard**: Thống kê doanh thu thời gian thực, số lượng đơn hàng, học sinh active và biểu đồ tăng trưởng.
- **Question Studio**: Công cụ soạn thảo câu hỏi chuyên nghiệp, hỗ trợ:
  - Trắc nghiệm đơn / đa lựa chọn.
  - Câu hỏi điểm ảnh tương tác (Hotspot Image).
  - Ghép nối, kéo thả, điền khuyết.
- **Quản Lý Gói Bản Quyền (Package CRUD)**: Tùy chỉnh giá, tính năng, sĩ số, tự động tạo màu sắc và giao diện đồng bộ.
- **Telegram Bot Quản Trị (`@trikun_cdphp_bot`)**:
  - Bắn thông báo tức thì khi có giáo viên đăng ký gói hoặc gửi câu hỏi tư vấn.
  - Duyệt kích hoạt gói ngay trên Telegram bằng nút bấm (Inline Callback Keyboard).
  - Tra cứu doanh thu, danh sách đơn chờ duyệt qua lệnh bot (`/doanhthu`, `/choduyet`, `/trogiup`).

### 👨‍👩‍👧 Phân Hệ Phụ Huynh (Parent Dashboard)
- Theo dõi lịch sử làm bài, số sao tích lũy và thời gian học tập của con.
- Cảnh báo tự động khi con làm sai nhiều lần ở một chủ đề kiến thức cụ thể để phụ huynh đồng hành hỗ trợ.

---

## 💻 3. Kiến Trúc & Công Nghệ Sử Dụng (Tech Stack)

| Thành phần | Công nghệ / Thư viện | Vai trò |
| :--- | :--- | :--- |
| **Backend** | **Laravel 12 (PHP 8.3)** | MVC Architecture, RESTful Routing, Eloquent ORM, Service Layer |
| **Database** | **MySQL 8.0** | Lưu trữ quan hệ thực thể, ràng buộc khóa ngoại, tối ưu chỉ mục |
| **Frontend** | **Blade Components & Vanilla CSS** | Thiết kế UI 3D Gamified Card xúc giác, Responsive hoàn toàn |
| **Asset Bundler** | **Vite 8 & Tailwind CSS** | Biên dịch CSS/JS siêu tốc, tối ưu dung lượng asset |
| **AI / Vision** | **Google MediaPipe** | Nhận diện cử chỉ bàn tay & cơ thể cho mini-game tương tác webcam |
| **Thanh toán** | **PayOS API & VietQR** | Tạo mã QR thanh toán động, Webhook xác nhận tự động 24/7 |
| **Thông báo** | **Telegram Bot API** | Hệ thống bot hai chiều: đẩy thông báo & nhận lệnh điều khiển |
| **Hosting / CI-CD**| **Railway & GitHub** | Tự động build và deploy qua Nixpacks & Docker container |

---

## 🔑 4. Tài Khoản Mẫu Để Trải Nghiệm (Demo Accounts)

Mật khẩu chung cho tất cả tài khoản mẫu là: `password`

| Vai trò | Email đăng nhập | Mật khẩu | Chức năng kiểm thử chính |
| :--- | :--- | :--- | :--- |
| 🛡️ **Quản Trị Viên (Admin)** | `admin@ic3.test` | `password` | Quản trị hệ thống, Question Studio, duyệt đơn, cài đặt game |
| 👩‍🏫 **Giáo Viên (Teacher)** | `teacher@ic3.test` | `password` | Quản lý lớp 3A1, xem báo cáo điểm, nâng cấp gói bản quyền |
| 👦 **Học Sinh (Student)** | `student@ic3.test` | `password` | Vào phòng thi, làm bài trắc nghiệm, tích sao, chơi mini-game |
| 👨‍👩‍👦 **Phụ Huynh (Parent)** | `parent@ic3.test` | `password` | Xem dashboard tiến độ và báo cáo học tập của con |

---

## 🚀 5. Hướng Dẫn Cài Đặt Cục Bộ (Local Development)

### Yêu cầu môi trường:
- PHP >= 8.3 (hỗ trợ các extension: `pdo_mysql`, `curl`, `mbstring`, `openssl`)
- Composer >= 2.x
- Node.js >= 20.x & NPM
- MySQL >= 8.0 (khuyên dùng **Laragon** trên Windows)

### Các bước cài đặt:

```bash
# 1. Clone dự án về máy
git clone https://github.com/kuncode1311-cloud/cdphp_MOS.git
cd cdphp_MOS

# 2. Cài đặt các thư viện PHP & JavaScript
composer install
npm install

# 3. Tạo file cấu hình môi trường
cp .env.example .env

# 4. Tạo khóa bảo mật ứng dụng (App Key)
php artisan key:generate

# 5. Cấu hình Database trong file .env
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=MOS
# DB_USERNAME=root
# DB_PASSWORD=

# 6. Chạy Migration và nạp dữ liệu mẫu
php artisan migrate --seed

# 7. Biên dịch tài nguyên giao diện
npm run build

# 8. Khởi động máy chủ cục bộ
php artisan serve
```

👉 Mở trình duyệt và truy cập: `http://127.0.0.1:8000`

---

## ☁️ 6. Triển Khai Lên Railway (Production Deployment)

Dự án đã được cấu hình sẵn các file:
- `Procfile`: Tự động chạy `storage:link`, `migrate --force` và khởi chạy web server.
- `nixpacks.toml`: Chỉ định phiên bản **PHP 8.3**, **Composer** và **Node.js 20**.

### Các biến môi trường cần thiết trên Railway (Variables):
```dotenv
APP_NAME="IC3 Quest"
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:5HoHYVPhPjHMPVsAvC9zuO7p4BuDtZujZMXkNGzfBr8=
APP_URL=https://<ten-service-cua-ban>.up.railway.app

DB_CONNECTION=mysql
DB_HOST=${{MySQL.MYSQLHOST}}
DB_PORT=${{MySQL.MYSQLPORT}}
DB_DATABASE=${{MySQL.MYSQLDATABASE}}
DB_USERNAME=${{MySQL.MYSQLUSER}}
DB_PASSWORD=${{MySQL.MYSQLPASSWORD}}

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
```

Sau khi deploy, mở tab **Console** trên Railway và chạy lệnh nạp dữ liệu:
```bash
php artisan db:seed --force
```

---

## 📁 7. Cấu Trúc Thư Mục Dự Án

```text
cdphp_MOS/
├── app/
│   ├── Console/Commands/        # Lệnh Artisan (Telegram poll daemon, import câu hỏi...)
│   ├── Enums/                   # Các Enum chuẩn (UserRole, QuestionType...)
│   ├── Http/Controllers/       # Bộ điều khiển (Admin, Learning, Pricing, Auth...)
│   ├── Models/                  # Eloquent Models (User, Question, PracticeTest, Package...)
│   ├── Observers/               # Observers tự động tính toán dữ liệu
│   └── Services/                # Service Layer (PayosService, TelegramService, SubscriptionService)
├── config/                      # Cấu hình hệ thống & tích hợp bên thứ ba
├── database/
│   ├── migrations/              # Cấu trúc các bảng dữ liệu
│   └── seeders/                 # Dữ liệu mẫu (Khối lớp, câu hỏi, tài khoản demo)
├── public/
│   ├── games/                   # Tài nguyên mã nguồn mini-game AI MediaPipe
│   └── images/                  # Logo, banner, mascot hình ảnh
├── resources/
│   ├── css/                     # CSS tùy chỉnh (game-theme.css phong cách 3D)
│   ├── js/                      # JavaScript logic
│   └── views/                   # Giao diện Blade (admin, learning, pricing, layouts)
├── routes/
│   ├── web.php                  # Định tuyến toàn bộ ứng dụng web
│   └── console.php              # Định tuyến console commands
├── Procfile                     # File khởi chạy Railway
├── nixpacks.toml                # Cấu hình môi trường build Railway
└── tests/                       # Bộ kiểm thử tự động (Feature & Unit Tests)
```

---

## 📄 8. Bản Quyền & Giấy Phép (License)

Dự án được phát triển nhằm phục vụ mục đích học tập, nghiên cứu và báo cáo đồ án.  
Mã nguồn phát hành dưới giấy phép [MIT License](LICENSE).
