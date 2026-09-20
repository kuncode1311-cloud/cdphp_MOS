// Dữ liệu Chương 2: PHƯƠNG PHÁP THỰC HIỆN VÀ THIẾT KẾ HỆ THỐNG

export const chapter2Data = {
    title: "Chương 2. PHƯƠNG PHÁP THỰC HIỆN",
    sections: [
        {
            title: "2.1. Phân tích hệ thống nghiệp vụ",
            subsections: [
                {
                    title: "2.1.1. Quy trình nghiệp vụ 5 bước cốt lõi",
                    content: `Hệ thống luyện thi IC3 Quest vận hành khép kín và tự động hóa thông qua sự phối hợp nhịp nhàng giữa 4 nhóm đối tượng người dùng theo quy trình 5 bước cốt lõi:

Bước 1: Khởi tạo hệ thống & Quản trị danh mục (Quản trị viên - Admin)
Quản trị viên đăng nhập vào Admin Portal, thiết lập cấu trúc chương trình đào tạo chuẩn quốc tế (IC3 GS6 Spark), tạo các Khối lớp (Khối 3, Khối 4, Khối 5) và các Chủ đề kiến thức (7 chủ đề chuẩn). Admin khởi tạo và cấu hình các gói bản quyền mở bán trên trang Bảng giá (Standard, Pro, School VIP), thiết lập tỷ lệ quy đổi điểm Sao sang phút chơi mini-game và cấu hình kết nối Telegram Bot.

Bước 2: Đăng ký & Thuê gói bản quyền (Giáo viên - Teacher)
Giáo viên truy cập Cổng bảng giá công khai, lựa chọn gói dịch vụ phù hợp với nhu cầu giảng dạy của lớp mình. Hệ thống sinh mã đơn hàng duy nhất và hiển thị mã VietQR động cùng thông tin chuyển khoản ngân hàng tự động. Giáo viên quét mã thanh toán bằng ứng dụng ngân hàng di động. Cổng PayOS hoặc Admin xác nhận kích hoạt đơn hàng, cấp quyền quản lý Khối lớp và cấp hạn mức sĩ số học sinh tương ứng cho giáo viên. Giáo viên tạo lớp học (như Lớp 3A1, Lớp 4A1) và tạo tài khoản học sinh.

Bước 3: Luyện thi phòng thi ảo & Chấm điểm tự động (Học sinh - Student)
Học sinh đăng nhập bằng tài khoản hoặc mã số học sinh (\`student_code\` đăng nhập nhanh). Học sinh chọn Khối lớp và Chủ đề muốn ôn tập, sau đó bấm vào bài luyện thi để mở Phòng thi ảo (Quiz Player). Hệ thống kích hoạt thuật toán xáo trộn câu hỏi và đáp án chống gian lận, bật đồng hồ đếm ngược thời gian thực. Học sinh thực hiện làm bài tương tác và nộp bài. Hệ thống chấm điểm tự động tức thì dựa trên thang điểm 1000 chuẩn IIG, lưu lịch sử lượt thi vào bảng \`test_attempts\`.

Bước 4: Gamification & Đổi thưởng giờ chơi game (Học sinh & Hệ thống)
Nếu học sinh đạt điểm số vượt chuẩn (>= 700/1000 điểm), hệ thống tự động cộng thưởng 10 - 20 Ngôi sao tích lũy vào ví \`reward_stars\`, đồng thời cập nhật điểm số lên Bảng xếp hạng Top 10 Hiệp sĩ nhí. Học sinh có thể vào Khu trò chơi, dùng Sao tích lũy để đổi các gói phút chơi mini-game (Ví dụ: 30 Sao đổi 5 phút chơi game giải trí). Trong quá trình chơi game, hệ thống chạy AJAX đếm lùi từng giây và tự động khóa màn hình game khi hết giờ.

Bước 5: Giám sát tiến độ & Cảnh báo học tập (Phụ huynh & Giáo viên)
Phụ huynh học sinh truy cập Góc Phụ huynh (Parent Dashboard) xem các biểu đồ phân tích trực quan: biểu đồ tiến độ điểm số theo thời gian, biểu đồ mạng nhện Radar đánh giá năng lực qua 7 chủ đề, và biểu đồ tròn tỷ lệ Đạt/Chưa đạt. Hệ thống tự động phân tích và đưa ra cảnh báo sớm nếu học sinh làm sai nhiều lần ở một bài thi hoặc chủ đề cụ thể để phụ huynh và giáo viên có biện pháp kèm cặp kịp thời.`
                },
                {
                    title: "2.1.2. Yêu cầu chức năng của hệ thống",
                    content: `Dưới đây là bảng tổng hợp các yêu cầu chức năng nghiệp vụ của 4 phân hệ trong hệ thống IC3 Quest:`,
                    tableFunctionalRequirements: [
                        { stt: "1", role: "Khách / Chung", name: "Xem bảng giá & Đăng ký gói", desc: "Xem chi tiết các gói dịch vụ, điền thông tin đăng ký và chọn phương thức thanh toán VietQR / PayOS.", priority: "Cao" },
                        { stt: "2", role: "Khách / Chung", name: "Live Chat tư vấn Telegram", desc: "Gửi câu hỏi và thông tin liên hệ từ widget trên web, tự động bắn thông báo tới Telegram Admin.", priority: "Cao" },
                        { stt: "3", role: "Khách / Chung", name: "Đăng nhập phân quyền", desc: "Xác thực tài khoản và tự động chuyển hướng đúng phân hệ tương ứng với vai trò (Admin, GV, HS).", priority: "Cao" },
                        { stt: "4", role: "Admin", name: "Bảng điều khiển thống kê", desc: "Hiển thị các chỉ số tổng quan: người dùng, khối lớp, đề thi, câu hỏi, doanh thu và biểu đồ trực quan.", priority: "Cao" },
                        { stt: "5", role: "Admin", name: "Quản lý người dùng & Lớp học", desc: "CRUD tài khoản người dùng, phân quyền giáo viên, khởi tạo danh sách lớp học và quản lý học sinh.", priority: "Cao" },
                        { stt: "6", role: "Admin", name: "IC3 Question Studio", desc: "Quản trị ngân hàng 509 câu hỏi và 35 bộ đề thi theo cây phân cấp Chương trình -> Khối -> Chủ đề.", priority: "Cao" },
                        { stt: "7", role: "Admin", name: "Quản lý Gói dịch vụ & Đơn hàng", desc: "Thêm sửa gói dịch vụ, duyệt và kích hoạt đơn hàng thuê gói của giáo viên, gia hạn ngày sử dụng.", priority: "Cao" },
                        { stt: "8", role: "Admin", name: "Cấu hình Khu trò chơi & BXH", desc: "Cài đặt tỷ lệ đổi Sao lấy phút chơi game, reset bảng xếp hạng tuần và cấu hình token bot Telegram.", priority: "Cao" },
                        { stt: "9", role: "Admin", name: "Xuất báo cáo kết quả thi", desc: "Trích xuất toàn bộ dữ liệu lịch sử thi của học sinh ra file định dạng CSV để lưu trữ và báo cáo.", priority: "Trung bình" },
                        { stt: "10", role: "Giáo viên", name: "Quản lý lớp học & Học sinh", desc: "Xem danh sách học sinh lớp phụ trách, theo dõi điểm số và xếp hạng học lực của từng em.", priority: "Cao" },
                        { stt: "11", role: "Học sinh", name: "Cổng học tập & Chọn Khối lớp", desc: "Khám phá thế giới IC3 Quest với 4 Action Hub 3D, chuyển đổi nhanh giữa Khối 3, 4, 5.", priority: "Cao" },
                        { stt: "12", role: "Học sinh", name: "Phòng thi ảo tương tác", desc: "Làm bài thi trắc nghiệm bấm giờ, giao diện hỗ trợ ảnh câu hỏi, thanh tiến độ và âm thanh phản hồi.", priority: "Cao" },
                        { stt: "13", role: "Hệ thống", name: "Chấm điểm & Tích lũy Sao", desc: "Tự động so khớp đáp án, chấm thang điểm 1000, cộng Sao thưởng nếu đạt chuẩn >= 700 điểm.", priority: "Cao" },
                        { stt: "14", role: "Học sinh", name: "Bảng xếp hạng & Huy hiệu", desc: "Vinh danh Top 10 Hiệp sĩ nhí xuất sắc nhất đợt thi đua, mở khóa 8 huy hiệu thành tích danh giá.", priority: "Cao" },
                        { stt: "15", role: "Học sinh", name: "Khu trò chơi & Đổi Sao", desc: "Đổi Sao lấy phút chơi mini-game giáo dục, tự động đếm lùi thời gian chơi và ngắt khi hết giờ.", priority: "Cao" },
                        { stt: "16", role: "Phụ huynh", name: "Parent Dashboard & Cảnh báo", desc: "Theo dõi biểu đồ tiến độ học tập của con, phân tích điểm yếu 7 chủ đề và nhận cảnh báo làm sai nhiều lần.", priority: "Cao" }
                    ]
                },
                {
                    title: "2.1.3. Sơ đồ khối chức năng chi tiết theo từng vai trò",
                    content: `Cấu trúc chức năng của hệ thống IC3 Quest được phân rã thành 4 phân hệ khối nghiệp vụ độc lập:

1. Phân hệ Khối chức năng Quản trị viên (Admin Portal):
- Khối Tổng quan & Báo cáo: Dashboard số liệu thời gian thực, biểu đồ tăng trưởng người dùng, biểu đồ doanh thu bản quyền, xuất báo cáo lịch sử thi CSV.
- Khối Quản trị Tài nguyên Học tập: Quản lý Chương trình đào tạo (IC3 GS6), Quản lý Khối lớp (Khối 3, 4, 5), Quản lý 7 Chủ đề kiến thức chuẩn, IC3 Question Studio biên soạn bộ đề và 509 câu hỏi.
- Khối Quản trị Người dùng & Phân quyền: Danh sách tài khoản, phân quyền khối lớp cho giáo viên, quản lý danh sách lớp học, cấp tài khoản học sinh.
- Khối Quản lý Kinh doanh & Gói dịch vụ: Thiết lập các gói bản quyền (giá bán, thời hạn, sĩ số), quản lý danh sách đơn hàng thanh toán VietQR/PayOS, kích hoạt và hủy đơn.
- Khối Cấu hình Hệ thống & Gamification: Cài đặt tỷ lệ đổi Sao ra phút chơi game, quản lý chu kỳ reset Bảng xếp hạng tuần, cấu hình Webhook Telegram Bot và quản lý tin nhắn Live Chat.

2. Phân hệ Khối chức năng Giáo viên (Teacher Portal):
- Khối Lớp học: Quản lý sĩ số học sinh trong lớp chủ nhiệm, theo dõi trạng thái kích hoạt tài khoản của học sinh.
- Khối Quản lý Tiến độ: Bảng điểm tổng hợp của cả lớp, phân loại học sinh Giỏi - Khá - Trung bình - Cần cố gắng, xem chi tiết bài làm từng em.
- Khối Bản quyền: Đăng ký thuê gói bản quyền theo khối lớp, gia hạn thời gian sử dụng, xem lịch sử các đơn thanh toán.

3. Phân hệ Khối chức năng Học sinh (Student Portal):
- Khối Cổng học tập: Hero Banner phiêu lưu, 4 thẻ lối tắt Action Hub 3D (Làm bài, Thành tích, Trò chơi, Phụ huynh), danh mục khóa học và khối lớp.
- Khối Phòng thi ảo (Quiz Player): Hiển thị bản đồ 7 chủ đề, danh sách các bài luyện thi, màn hình làm bài tương tác có xáo trộn ngẫu nhiên đề thi và đáp án, đồng hồ đếm ngược, tự động nộp bài và hiển thị kết quả chuẩn 1000 điểm IIG.
- Khối Gamification & Thi đua: Tích lũy ví Sao vàng, Bảng xếp hạng Top 10 Hiệp sĩ nhí đua top toàn trường, 8 huy hiệu thành tích danh dự.
- Khối Giải trí lành mạnh: Cửa hàng đổi Sao lấy phút chơi game (5 phút, 10 phút), phòng chơi mini-game giáo dục, đồng hồ kiểm soát giờ chơi nghiêm ngặt.

4. Phân hệ Khối chức năng Phụ huynh (Parent Portal):
- Khối Biểu đồ Phân tích: Biểu đồ đường tiến trình điểm số qua các lần thi, biểu đồ Radar năng lực 7 chủ đề kiến thức, biểu đồ tròn tỷ lệ Đạt/Chưa đạt.
- Khối Lịch sử & Chi tiết: Bảng kê toàn bộ các bài thi con đã làm, thời gian làm bài, số câu đúng/sai và xem lại đề bài chi tiết.
- Khối Cảnh báo Sớm: Thuật toán phát hiện tự động các chủ đề và bài luyện con làm sai từ 3 lần trở lên, đưa ra lời khuyên cho cha mẹ.`
                }
            ]
        },
        {
            title: "2.2. Các biểu đồ thiết kế hệ thống (UML Diagrams)",
            subsections: [
                {
                    title: "2.2.1. Lược đồ Use Case tổng thể toàn hệ thống",
                    content: `Lược đồ Use Case tổng thể phản ánh toàn bộ các tương tác giữa 4 tác nhân chính (Admin, Giáo viên, Học sinh, Phụ huynh) và 2 tác nhân ngoại vi hỗ trợ (Cổng thanh toán VietQR / PayOS và Máy chủ Telegram Bot API) với các khối ca sử dụng của hệ thống:
- Tác nhân Admin: Đăng nhập, Xem Dashboard thống kê, Quản lý người dùng và phân quyền, Quản lý cấu trúc đào tạo (Chương trình/Khối/Chủ đề), Quản trị ngân hàng đề thi & câu hỏi (Question Studio), Quản lý gói dịch vụ và duyệt đơn thanh toán, Cài đặt cấu hình trò chơi & bảng xếp hạng, Cấu hình Telegram Bot, Xuất báo cáo CSV.
- Tác nhân Giáo viên: Xem bảng giá, Đăng ký và thanh toán thuê gói (kết nối VietQR / PayOS), Đăng nhập, Quản lý lớp học phụ trách, Xem bảng điểm và phân loại học sinh.
- Tác nhân Học sinh: Đăng nhập, Tham gia Cổng học tập, Luyện thi phòng thi ảo (tự động xáo trộn đề), Xem kết quả thi và tích lũy Sao thưởng, Vinh danh Bảng xếp hạng hiệp sĩ nhí, Đổi Sao lấy phút chơi mini-game và tham gia chơi game.
- Tác nhân Phụ huynh: Xem Parent Dashboard, Xem biểu đồ tiến độ học tập của con, Nhận cảnh báo bài thi làm sai nhiều lần.
- Tác nhân Khách vãng lai: Xem thông tin giới thiệu, Tra cứu bảng giá gói dịch vụ, Gửi tin nhắn tư vấn Live Chat (kết nối Telegram Bot API).`
                },
                {
                    title: "2.2.2. Đặc tả chi tiết 12 Use Cases nghiệp vụ",
                    content: `Nhằm mô tả chuẩn xác từng luồng dữ liệu và điều kiện rẽ nhánh, dưới đây là bảng đặc tả chi tiết cho 12 Use Cases cốt lõi của hệ thống:`,
                    useCases: [
                        {
                            id: "UC01",
                            name: "Đăng nhập phân quyền hệ thống (Auth Login)",
                            actor: "Admin, Giáo viên, Học sinh",
                            goal: "Xác thực danh tính người dùng và chuyển hướng vào đúng phân hệ chức năng tương ứng.",
                            precondition: "Tài khoản người dùng đã được tạo trong bảng users và ở trạng thái hoạt động (status = active).",
                            mainFlow: "1. Người dùng truy cập trang `/dang-nhap`.\n2. Hệ thống hiển thị Form đăng nhập gồm trường Email/Mã học sinh và Mật khẩu, cùng các nút đăng nhập nhanh.\n3. Người dùng nhập thông tin và bấm nút Đăng nhập.\n4. Hệ thống kiểm tra CSRF token, so khớp mật khẩu bằng thuật toán Bcrypt.\n5. Nếu chính xác, hệ thống khởi tạo Session đăng nhập an toàn, cập nhật thời gian đăng nhập.\n6. Dựa vào trường `role` của tài khoản, hệ thống chuyển hướng Admin tới `/quan-tri`, Học sinh tới trang chủ `/` và Giáo viên tới bảng quản lý lớp.",
                            altFlow: "- Nếu email hoặc mã học sinh không tồn tại: Báo lỗi 'Tài khoản không tồn tại trên hệ thống'.\n- Nếu mật khẩu không khớp: Báo lỗi 'Mật khẩu đăng nhập không chính xác'.\n- Nếu tài khoản đang ở trạng thái khóa (status = locked): Báo lỗi 'Tài khoản của bạn đã bị tạm khóa, vui lòng liên hệ quản trị viên'.",
                            postcondition: "Phiên đăng nhập Session được lưu trữ, thanh Header hiển thị tên và avatar người dùng."
                        },
                        {
                            id: "UC02",
                            name: "Đăng ký tài khoản giáo viên & Thuê gói bản quyền",
                            actor: "Giáo viên, Khách hàng",
                            goal: "Cho phép giáo viên tự đăng ký tài khoản và khởi tạo đơn đăng ký thuê gói bản quyền phần mềm.",
                            precondition: "Người dùng truy cập vào trang Bảng giá `/bang-gia`.",
                            mainFlow: "1. Giáo viên xem danh sách các gói dịch vụ (Standard, Pro, School VIP) kèm bảng tính năng chi tiết.\n2. Giáo viên chọn gói mong muốn và bấm 'Đăng ký & Thuê gói ngay'.\n3. Hệ thống hiển thị form nhập: Họ tên, Email, Số điện thoại, Tên trường học và Mật khẩu.\n4. Giáo viên điền đầy đủ thông tin và gửi yêu cầu.\n5. Hệ thống validate dữ liệu, khởi tạo tài khoản giáo viên mới trong bảng `users` (role = teacher, status = pending).\n6. Hệ thống tạo một bản ghi đơn hàng mới trong bảng `package_orders` với mã đơn duy nhất dạng `MOS-YYYYMM-XXXXX` và trạng thái `pending`.\n7. Hệ thống chuyển hướng giáo viên sang trang Thanh toán đơn hàng `/bang-gia/thanh-toan/{code}`.",
                            altFlow: "- Nếu email giáo viên đã tồn tại trên hệ thống: Hiển thị thông báo yêu cầu đăng nhập trước khi thuê gói.\n- Nếu thông tin số điện thoại không hợp lệ: Báo lỗi định dạng.",
                            postcondition: "Tài khoản và đơn hàng được lưu trữ thành công ở trạng thái chờ thanh toán."
                        },
                        {
                            id: "UC03",
                            name: "Thanh toán đơn hàng qua VietQR / PayOS và Kích hoạt tự động",
                            actor: "Giáo viên, Cổng thanh toán PayOS / VietQR, Hệ thống",
                            goal: "Xử lý thanh toán số tiền thuê gói và tự động kích hoạt quyền giảng dạy cho giáo viên.",
                            precondition: "Đơn hàng đang ở trạng thái chờ thanh toán (status = pending).",
                            mainFlow: "1. Tại trang checkout, hệ thống sinh mã VietQR động chứa số tài khoản, số tiền và nội dung chuyển khoản chính xác.\n2. Giáo viên mở ứng dụng Mobile Banking quét mã VietQR và xác nhận chuyển khoản.\n3. Máy chủ PayOS gửi tín hiệu Webhook về máy chủ IC3 Quest tại route `/bang-gia/payos-webhook` kèm mã đơn hàng và chữ ký số an toàn.\n4. Hệ thống verify chữ ký Webhook, tìm bản ghi đơn hàng tương ứng trong `package_orders`.\n5. Hệ thống cập nhật trạng thái đơn hàng `status = active`, ghi nhận `activated_at`.\n6. Hệ thống tự động cập nhật tài khoản giáo viên: tăng hạn mức `max_students`, cộng số ngày sử dụng vào `expires_at`, gán các khối lớp được phép phụ trách vào bảng `teacher_level`.\n7. Trang thanh toán của giáo viên tự động nhận trạng thái thành công và chuyển hướng về trang lịch sử thuê gói.",
                            altFlow: "- Nếu giáo viên chuyển khoản thủ công bằng Internet Banking: Giáo viên bấm nút 'Tôi đã chuyển khoản', đơn hàng chuyển sang trạng thái chờ Admin duyệt thủ công.\n- Nếu chữ ký Webhook không trùng khớp: Hệ thống từ chối cập nhật và ghi nhật ký cảnh báo bảo mật.",
                            postcondition: "Tài khoản giáo viên được cấp quyền sử dụng đầy đủ các tính năng giảng dạy theo gói."
                        },
                        {
                            id: "UC04",
                            name: "Quản lý người dùng, phân quyền giáo viên và học sinh (Admin)",
                            actor: "Quản trị viên (Admin)",
                            goal: "Quản lý danh sách người dùng toàn hệ thống, cấp quyền khối lớp và quản trị trạng thái tài khoản.",
                            precondition: "Admin đã đăng nhập thành công vào Admin Portal.",
                            mainFlow: "1. Admin truy cập mục Quản lý người dùng tại `/quan-tri/quan-ly`.\n2. Hệ thống hiển thị bảng danh sách người dùng với đầy đủ thông tin: Tên, Email, Vai trò, Trường học, Số sao, Hạn sử dụng và Trạng thái.\n3. Admin có thể lọc danh sách theo vai trò (Admin, Giáo viên, Học sinh), tìm kiếm theo tên hoặc email.\n4. Admin bấm nút Thêm mới hoặc Sửa thông tin tài khoản.\n5. Admin chọn các Khối lớp phân công cho giáo viên phụ trách (Khối 3, 4, 5).\n6. Admin bấm nút Lưu, hệ thống đồng bộ dữ liệu vào bảng `users` và bảng pivot `teacher_level`.",
                            altFlow: "- Admin có thể bấm nút Khóa nhanh tài khoản, hệ thống chuyển `status = locked`, người dùng này sẽ bị hủy phiên đăng nhập ngay lập tức.\n- Khi xóa người dùng: Hệ thống yêu cầu xác nhận kép để tránh xóa nhầm dữ liệu lịch sử bài thi.",
                            postcondition: "Thông tin người dùng và phân quyền được cập nhật tức thời."
                        },
                        {
                            id: "UC05",
                            name: "Quản lý cấu trúc Chương trình đào tạo, Khối lớp và Chủ đề IC3",
                            actor: "Quản trị viên (Admin)",
                            goal: "Khởi tạo và duy trì cây phân cấp kiến thức chuẩn quốc tế: Chương trình -> Khối lớp -> Chủ đề.",
                            precondition: "Admin đã đăng nhập vào hệ thống.",
                            mainFlow: "1. Admin truy cập màn hình Cấu trúc đào tạo.\n2. Hệ thống hiển thị cây phân cấp: Chương trình IC3 GS6 Spark -> 3 Khối lớp (Khối 3, 4, 5) -> 7 Chủ đề kiến thức chuẩn của mỗi khối.\n3. Admin có thể thêm mới một Khối lớp hoặc biên tập tên, mã code và mô tả của từng Chủ đề.\n4. Admin thiết lập thứ tự hiển thị (\`order\`) của các chủ đề trên bản đồ học tập của học sinh.\n5. Hệ thống lưu trữ các thay đổi vào bảng `programs`, `levels`, `topics`.",
                            altFlow: "- Nếu mã code của chủ đề bị trùng lặp: Hệ thống báo lỗi và yêu cầu nhập mã duy nhất.",
                            postcondition: "Cấu trúc danh mục học tập được cập nhật, đồng bộ ngay lập tức ra giao diện Cổng học sinh."
                        },
                        {
                            id: "UC06",
                            name: "IC3 Question Studio - Quản trị bộ đề thi và biên soạn câu hỏi",
                            actor: "Admin, Giáo viên được phân quyền",
                            goal: "Biên tập ngân hàng câu hỏi chất lượng cao, thiết lập đáp án đúng, hình ảnh minh họa và lời giải chi tiết.",
                            precondition: "Người dùng có quyền quản trị câu hỏi truy cập `/quan-tri/bo-de-cau-hoi`.",
                            mainFlow: "1. Giao diện Question Studio hiển thị thanh điều hướng 3 cấp: Chọn Khối lớp -> Chọn Chủ đề -> Chọn Bộ đề thi.\n2. Hệ thống tải danh sách các câu hỏi thuộc bộ đề đã chọn dạng danh sách trực quan.\n3. Người dùng bấm 'Thêm câu hỏi mới'.\n4. Form biên soạn mở ra gồm: Nội dung đề bài, loại câu hỏi (MultipleChoice, MultipleResponse, Matching), điểm số, lời giải thích.\n5. Người dùng tải lên ảnh đính kèm (sơ đồ, hình minh họa Word/Excel) và thiết lập các phương án A, B, C, D kèm đánh dấu đáp án đúng.\n6. Bấm nút 'Lưu câu hỏi', hệ thống ghi dữ liệu vào bảng `questions`, `question_options`, `question_assets`.",
                            altFlow: "- Nếu câu hỏi trắc nghiệm chưa chọn phương án đúng nào: Hệ thống cảnh báo và yêu cầu tick chọn ít nhất 1 đáp án đúng trước khi lưu.",
                            postcondition: "Câu hỏi mới được bổ sung vào ngân hàng đề và sẵn sàng cho học sinh làm bài."
                        },
                        {
                            id: "UC07",
                            name: "Quản lý lớp học và gán học sinh vào lớp (Giáo viên)",
                            actor: "Giáo viên",
                            goal: "Tổ chức danh sách học sinh theo từng lớp học do mình phụ trách.",
                            precondition: "Tài khoản giáo viên đang có hiệu lực bản quyền.",
                            mainFlow: "1. Giáo viên truy cập mục Lớp học của tôi.\n2. Giáo viên tạo lớp học mới: Nhập tên lớp (VD: Lớp 3A1), chọn Khối lớp tương ứng.\n3. Hệ thống tạo bản ghi lớp học trong bảng `classrooms`, liên kết với `teacher_id` của giáo viên.\n4. Giáo viên thêm học sinh vào lớp bằng cách nhập Họ tên, Mã học sinh (\`student_code\`) hoặc Email.\n5. Hệ thống tạo tài khoản học sinh, gán `classroom_id` và cấp quyền Khối lớp tương ứng trong bảng `level_user`.",
                            altFlow: "- Nếu số lượng học sinh trong lớp vượt quá hạn mức `max_students` của gói bản quyền: Hệ thống thông báo gói đã đạt giới hạn sĩ số và hướng dẫn nâng cấp gói Pro/VIP.",
                            postcondition: "Lớp học được thiết lập đầy đủ danh sách học sinh, sẵn sàng cho việc theo dõi tiến độ."
                        },
                        {
                            id: "UC08",
                            name: "Luyện thi phòng thi ảo tương tác (Quiz Player)",
                            actor: "Học sinh",
                            goal: "Học sinh thực hiện bài thi trắc nghiệm bấm giờ trong môi trường mô phỏng thi thật.",
                            precondition: "Học sinh đã đăng nhập và được cấp quyền vào Khối lớp tương ứng.",
                            mainFlow: "1. Học sinh bấm vào một bài luyện thi tại Cổng học sinh, hệ thống chuyển tới màn hình chuẩn bị `/bai-luyen/{slug}/lam-bai`.\n2. Học sinh xem thông tin: Tên đề thi, số câu hỏi (10 - 30 câu), thời gian làm bài, điểm chuẩn đạt (700/1000) và bấm 'Bắt đầu làm bài'.\n3. Hệ thống tải danh sách câu hỏi, tự động xáo trộn thứ tự câu hỏi và phương án trả lời nếu đề bật chế độ Shuffle.\n4. Màn hình làm bài hiển thị: Câu hỏi hiện tại, ảnh minh họa phóng to được, 4 phương án lựa chọn, thanh tiến trình câu hỏi và đồng hồ đếm ngược JS thời gian thực.\n5. Học sinh tick chọn đáp án cho từng câu và bấm nút 'Nộp bài'.\n6. Nếu đồng hồ đếm ngược về 00:00 mà học sinh chưa bấm nộp: Hệ thống tự động khóa giao diện và nộp bài lên máy chủ.",
                            altFlow: "- Nếu học sinh làm mất kết nối Internet tạm thời: Dữ liệu câu trả lời đã tick được lưu tạm trong bộ nhớ trình duyệt, khi có mạng trở lại hệ thống tự đồng bộ nộp bài.",
                            postcondition: "Bài thi được gửi về server để kích hoạt quy trình chấm điểm tự động."
                        },
                        {
                            id: "UC09",
                            name: "Chấm điểm tự động và Lưu lịch sử bài làm",
                            actor: "Hệ thống (IC3 Engine)",
                            goal: "So khớp phương án trả lời của thí sinh với đáp án chuẩn, tính điểm theo thang 1000 điểm IIG và cộng thưởng.",
                            precondition: "Hệ thống tiếp nhận dữ liệu bài làm của học sinh từ Quiz Player.",
                            mainFlow: "1. Hệ thống duyệt qua danh sách các câu hỏi của đề thi, lấy danh sách đáp án đúng từ bảng `question_options`.\n2. So khớp từng phương án học sinh đã chọn: câu trả lời đúng 100% được tính điểm trọn vẹn.\n3. Tính tổng điểm quy đổi theo thang 1000 điểm chuẩn quốc tế IC3: $Điểm = (Số câu đúng / Tổng số câu) * 1000$.\n4. Kiểm tra điều kiện Đạt chuẩn: nếu $Điểm >= 700$ thì đánh dấu `is_passed = true`, ngược lại `is_passed = false`.\n5. Tạo bản ghi kết quả mới trong bảng `test_attempts` lưu: `user_id`, `practice_test_id`, `score`, `total_correct`, `duration_seconds`.\n6. Nếu bài thi đạt chuẩn: Hệ thống cộng thêm 10 Ngôi sao vào ví `reward_stars` của học sinh trong bảng `users` và ghi nhật ký giao dịch `game_transactions`.\n7. Hiển thị báo cáo kết quả tổng kết trực quan cho học sinh kèm biểu tượng Cup vàng chúc mừng hoặc lời khuyên cố gắng.",
                            altFlow: "- Nếu bài thi làm lại và điểm thấp hơn lần trước: Hệ thống vẫn lưu lịch sử lượt thi nhưng giữ nguyên kỷ lục điểm cao nhất trên Bảng xếp hạng.",
                            postcondition: "Điểm số và Sao thưởng được cập nhật vào cơ sở dữ liệu."
                        },
                        {
                            id: "UC10",
                            name: "Đổi Sao tích lũy lấy phút chơi mini-game giáo dục",
                            actor: "Học sinh",
                            goal: "Cho phép học sinh sử dụng Sao tích lũy từ kết quả học tập để đổi thời gian chơi game giải trí lành mạnh.",
                            precondition: "Học sinh có số dư Sao trong ví `reward_stars` lớn hơn hoặc bằng mức quy đổi của gói game.",
                            mainFlow: "1. Học sinh truy cập Khu trò chơi tại `/tro-choi`.\n2. Hệ thống hiển thị số Sao hiện có, số giây chơi game còn lại và 2 gói đổi thưởng: Gói 1 (30 Sao đổi 5 phút) và Gói 2 (50 Sao đổi 10 phút).\n3. Học sinh bấm nút 'Đổi gói ngay'.\n4. Hệ thống kiểm tra số dư Sao của học sinh qua phương thức \`exchangeGamePackage()\`.\n5. Nếu đủ điều kiện, hệ thống trừ số Sao tương ứng và cộng số giây vào cột `game_time_seconds` của học sinh trong bảng `users`.\n6. Hệ thống ghi nhận bản ghi giao dịch đổi thưởng vào bảng `game_transactions`.\n7. Học sinh bấm 'Chơi game ngay' để mở màn hình mini-game phiêu lưu (Ví dụ game Bảo vệ em bé). Đồng hồ đếm lùi AJAX tiêu hao thời gian chơi thực tế.",
                            altFlow: "- Nếu học sinh không đủ Sao: Hệ thống hiển thị thông báo khích lệ 'Bạn chưa đủ Sao, hãy làm thêm bài thi đạt chuẩn để tích lũy thêm Sao nhé!'.\n- Nếu Admin đang tạm khóa Khu trò chơi: Báo lỗi bảo trì.",
                            postcondition: "Số Sao bị trừ, số giây chơi game tăng lên và giao dịch được lưu vết minh bạch."
                        },
                        {
                            id: "UC11",
                            name: "Parent Dashboard - Giám sát tiến độ học tập và Cảnh báo sớm",
                            actor: "Phụ huynh",
                            goal: "Cung cấp góc nhìn trực quan, phân tích toàn diện về năng lực học tập của con và nhận diện các điểm yếu kiến thức.",
                            precondition: "Phụ huynh đăng nhập vào tài khoản của con và bấm vào thẻ 'Góc Phụ Huynh' (`/phu-huynh`).",
                            mainFlow: "1. Hệ thống truy xuất toàn bộ lịch sử thi trong bảng `test_attempts` của học sinh.\n2. Gọi Service \`ParentLearningAnalyticsService\` tổng hợp dữ liệu.\n3. Hiển thị 4 thẻ tóm tắt nhanh: Tổng số bài đã thi, Điểm số trung bình, Tỷ lệ bài đạt chuẩn (%), và Tổng thời gian đã dành cho việc luyện thi.\n4. Vẽ biểu đồ đường (Line Chart) mô tả xu hướng phát triển điểm số qua các tuần gần nhất.\n5. Vẽ biểu đồ mạng nhện (Radar Chart) phân tích mức độ thành thạo trên 7 chủ đề kiến thức IC3 GS6.\n6. Vẽ biểu đồ tròn (Doughnut Chart) biểu thị tỷ lệ câu làm đúng, làm sai và bỏ trống.\n7. Khu vực Cảnh báo thông minh liệt kê danh sách các bài thi hoặc chủ đề con thi chưa đạt từ 2 lần trở lên kèm lời khuyên thiết thực.",
                            altFlow: "- Phụ huynh có thể chuyển bộ lọc mốc thời gian: 7 ngày qua, 30 ngày qua hoặc Toàn thời gian; hệ thống tự động tải lại số liệu mượt mà qua AJAX.",
                            postcondition: "Phụ huynh nắm bắt chính xác điểm mạnh, điểm yếu của con để đồng hành cùng nhà trường."
                        },
                        {
                            id: "UC12",
                            name: "Live Chat tư vấn trực tuyến và Bắn thông báo Telegram Admin",
                            actor: "Khách hàng, Quản trị viên, Bot Telegram (@sp_trikun_bot)",
                            goal: "Kết nối trao đổi tư vấn tức thời giữa khách hàng trên website và điện thoại của Quản trị viên.",
                            precondition: "Hệ thống đã cấu hình Token Bot và Chat ID Telegram trong bảng `game_settings`.",
                            mainFlow: "1. Khách hàng nhấp vào biểu tượng Live Chat Messenger ở góc phải màn hình web.\n2. Cửa sổ chat mở ra, khách hàng điền Họ tên, Số điện thoại liên hệ và nội dung cần tư vấn rồi bấm 'Gửi tin nhắn'.\n3. Hệ thống lưu tin nhắn vào bảng `support_messages` với trạng thái `pending`.\n4. Hệ thống tự động gọi API Telegram Bot \`sendMessage\` bắn một thông báo chi tiết đến nhóm kín quản trị viên trên ứng dụng Telegram.\n5. Quản trị viên nhận thông báo đẩy trên điện thoại ngay lập tức, đọc nội dung và có thể bấm link phản hồi hoặc liên hệ trực tiếp qua số điện thoại của khách hàng.",
                            altFlow: "- Nếu kết nối mạng tới Telegram bị nghẽn: Tin nhắn vẫn được lưu an toàn trong bảng `support_messages` để Admin xem trên giao diện quản trị, cờ `telegram_sent` được đánh dấu false để retry sau.",
                            postcondition: "Tin nhắn tư vấn được ghi nhận và Quản trị viên tiếp nhận tức thì."
                        }
                    ]
                },
                {
                    title: "2.2.3. Lược đồ hoạt động (Activity Diagrams) cho các Use Cases",
                    content: `Nhằm trực quan hóa quy trình xử lý của các ca sử dụng phức tạp, dưới đây là mô tả luồng hoạt động của các Use Case chính:

1. Luồng hoạt động Đăng nhập phân quyền (Lược đồ 2.2):
Bắt đầu -> Người dùng nhập Email và Mật khẩu trên giao diện -> Nhấn nút Đăng nhập -> Máy chủ tiếp nhận request qua bộ lọc CSRF Middleware -> Kiểm tra tài khoản trong bảng users -> Nếu tài khoản không tồn tại: Báo lỗi 'Tài khoản không tồn tại' -> Nếu tồn tại: So khớp hash mật khẩu bằng Bcrypt -> Nếu mật khẩu sai: Báo lỗi 'Sai mật khẩu' -> Nếu mật khẩu đúng: Kiểm tra trạng thái status -> Nếu status = 'locked': Báo lỗi 'Tài khoản đã bị khóa' -> Nếu status = 'active': Khởi tạo Session đăng nhập -> Kiểm tra trường role -> Nếu role = 'admin': Chuyển hướng tới /quan-tri -> Nếu role = 'teacher': Chuyển hướng tới trang quản lý lớp -> Nếu role = 'student': Chuyển hướng tới trang chủ học tập / -> Kết thúc.

2. Luồng hoạt động Thanh toán VietQR / PayOS tự động (Lược đồ 2.3):
Bắt đầu -> Giáo viên chọn gói bản quyền và bấm Thuê gói -> Hệ thống tạo đơn hàng trong package_orders với mã MOS-YYYYMM-XXXXX (status = pending) -> Sinh chuỗi mã VietQR chứa số tài khoản, số tiền và nội dung chuyển khoản -> Giáo viên quét mã thanh toán trên ứng dụng Mobile Banking -> Máy chủ ngân hàng xử lý giao dịch -> Cổng PayOS bắn Webhook về URL callback của hệ thống -> Hệ thống trích xuất dữ liệu Webhook -> Đối chiếu chữ ký bảo mật HMAC-SHA256 -> Nếu sai chữ ký: Ghi log cảnh báo và hủy bỏ -> Nếu đúng chữ ký: Kiểm tra trạng thái giao dịch -> Nếu giao dịch thành công: Cập nhật đơn hàng status = 'active' -> Cập nhật tài khoản giáo viên: tăng max_students, gia hạn expires_at, gán khối lớp vào teacher_level -> Gửi thông báo thành công cho giáo viên -> Kết thúc.

3. Luồng hoạt động Học sinh làm bài phòng thi ảo & Chấm điểm (Lược đồ 2.4):
Bắt đầu -> Học sinh chọn bài thi và bấm Bắt đầu làm bài -> Hệ thống kiểm tra quyền truy cập khối lớp -> Tải danh sách câu hỏi của bài thi từ CSDL -> Nếu đề thi bật shuffle: Thực hiện xáo trộn ngẫu nhiên thứ tự câu hỏi và đáp án -> Khởi tạo giao diện Quiz Player và kích hoạt đồng hồ đếm ngược JS -> Học sinh đọc câu hỏi và tick chọn đáp án -> Học sinh bấm Nộp bài (hoặc hết giờ tự nộp) -> Dữ liệu bài làm được submit lên máy chủ -> Hệ thống gọi bộ máy chấm điểm so khớp từng câu hỏi với đáp án đúng -> Tính điểm theo thang 1000 chuẩn IIG -> Lưu bản ghi vào test_attempts -> Kiểm tra điểm số: Nếu điểm >= 700: Cộng 10 Sao tích lũy vào bảng users và ghi nhận game_transactions -> Cập nhật điểm thi đua lên Bảng xếp hạng Top 10 -> Trả về màn hình tổng kết kết quả thi trực quan cho học sinh -> Kết thúc.

4. Luồng hoạt động Đổi Sao lấy phút chơi mini-game giải trí (Lược đồ 2.5):
Bắt đầu -> Học sinh vào Khu trò chơi -> Hệ thống đọc số dư reward_stars và game_time_seconds hiện tại -> Học sinh chọn gói đổi thưởng (30 Sao đổi 5 phút hoặc 50 Sao đổi 10 phút) -> Bấm Đổi gói -> Gửi yêu cầu AJAX lên máy chủ -> Kiểm tra trạng thái mở cửa game trong game_settings -> Nếu game đang bảo trì: Báo lỗi -> Nếu đang mở: Kiểm tra số dư Sao của học sinh -> Nếu không đủ Sao: Báo lỗi thiếu Sao -> Nếu đủ Sao: Thực hiện trừ Sao, cộng số giây tương ứng vào game_time_seconds -> Lưu nhật ký giao dịch game_transactions -> Trả về kết quả thành công qua JSON -> Giao diện tự động cập nhật số dư mới -> Học sinh bấm Chơi game -> Bật đồng hồ đếm lùi thời gian chơi -> Khi hết giờ: Tự động khóa màn hình game và yêu cầu làm thêm bài thi để nhận Sao -> Kết thúc.`
                }
            ]
        },
        {
            title: "2.3. Thiết kế Cơ sở dữ liệu chi tiết",
            subsections: [
                {
                    title: "2.3.1. Sơ đồ thực thể mối quan hệ (ERD)",
                    content: `Cơ sở dữ liệu của hệ thống IC3 Quest được chuẩn hóa ở dạng chuẩn 3NF (Third Normal Form) nhằm loại bỏ triệt để hiện tượng dư thừa dữ liệu, đồng thời tối ưu hóa tốc độ truy vấn thông qua các chỉ mục Indexing. Hệ thống bao gồm 17 bảng quan hệ chặt chẽ:

Các mối quan hệ thực thể cốt lõi trong hệ thống:
1. Mối quan hệ phân cấp nội dung học tập:
- \`programs\` (1) ──── (N) \`levels\`: Một chương trình đào tạo (IC3 GS6 Spark) chia làm nhiều Khối lớp (Khối 3, 4, 5).
- \`levels\` (1) ──── (N) \`topics\`: Mỗi Khối lớp bao gồm 7 Chủ đề kiến thức chuẩn quốc tế.
- \`topics\` (1) ──── (N) \`practice_tests\`: Mỗi Chủ đề chứa nhiều Bài luyện thi trắc nghiệm.
- \`practice_tests\` (1) ──── (N) \`questions\`: Mỗi Bài luyện thi liên kết với nhiều Câu hỏi (từ 10 đến 30 câu).
- \`questions\` (1) ──── (N) \`question_options\`: Mỗi Câu hỏi chứa nhiều Phương án lựa chọn (A, B, C, D).
- \`questions\` (1) ──── (N) \`question_assets\`: Một Câu hỏi có thể đính kèm nhiều Tệp hình ảnh minh họa bài thi.

2. Mối quan hệ tổ chức lớp học & Người dùng:
- \`users\` (1) ──── (N) \`classrooms\`: Một Giáo viên chủ nhiệm phụ trách quản lý nhiều Lớp học.
- \`classrooms\` (1) ──── (N) \`users\`: Một Lớp học chứa nhiều Học sinh (liên kết qua khóa ngoại \`users.classroom_id\`).
- \`users\` (1) ──── (N) \`test_attempts\`: Một Học sinh lưu trữ toàn bộ lịch sử các Lượt làm bài thi.
- \`practice_tests\` (1) ──── (N) \`test_attempts\`: Một Bài thi được làm bởi nhiều Học sinh khác nhau.

3. Mối quan hệ phân quyền đa-đa (Many-to-Many):
- \`users\` (N) ──── (N) \`levels\` thông qua bảng pivot \`level_user\`: Xác định Học sinh được cấp quyền ôn luyện Khối lớp nào.
- \`users\` (N) ──── (N) \`levels\` thông qua bảng pivot \`teacher_level\`: Xác định Giáo viên được phụ trách giảng dạy Khối lớp nào.

4. Mối quan hệ Gói dịch vụ & Tài chính:
- \`packages\` (1) ──── (N) \`package_orders\`: Một Gói dịch vụ có nhiều Đơn đăng ký thuê bản quyền của giáo viên.
- \`users\` (1) ──── (N) \`package_orders\`: Một Giáo viên có thể thực hiện nhiều Đơn đặt mua gói theo từng năm học.
- \`packages\` (N) ──── (N) \`levels\` qua bảng pivot \`package_level\`: Gói bản quyền mở khóa quyền truy cập cho những Khối lớp nào.

5. Mối quan hệ Gamification & Chăm sóc khách hàng:
- \`users\` (1) ──── (N) \`game_transactions\`: Ghi nhận biến động tăng Sao khi thi đạt và giảm Sao khi đổi giờ chơi game.
- \`users\` (1) ──── (N) \`support_messages\`: Lưu trữ các tin nhắn câu hỏi tư vấn Live Chat gửi tới Admin.`
                },
                {
                    title: "2.3.2. Báo cáo đặc tả cấu trúc chi tiết của 17 bảng CSDL trong MySQL",
                    content: `Dưới đây là báo cáo đặc tả kỹ thuật chi tiết của toàn bộ 17 bảng dữ liệu đang vận hành trong cơ sở dữ liệu MySQL của hệ thống IC3 Quest:`
                }
            ]
        }
    ]
};
