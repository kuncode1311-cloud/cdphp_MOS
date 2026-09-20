// Nội dung Lời mở đầu & Chương 1: TỔNG QUAN TÀI LIỆU VÀ CƠ SỞ CÔNG NGHỆ

export const introAndChapter1 = {
    loiMoDau: {
        title: "LỜI MỞ ĐẦU",
        content: [
            `Trong xu thế phát triển mạnh mẽ của khoa học và công nghệ hiện đại, cuộc Cách mạng công nghiệp lần thứ tư (CMCN 4.0) đang tác động sâu rộng đến mọi khía cạnh đời sống kinh tế - xã hội, trong đó Giáo dục và Đào tạo là lĩnh vực chịu sự chuyển dịch sâu sắc nhất. Chuyển đổi số giáo dục không còn là một giải pháp tình thế mang tính xu hướng, mà đã trở thành yêu cầu chiến lược bắt buộc nhằm hiện đại hóa phương thức giảng dạy, nâng cao năng lực quản trị nhà trường và phát triển phẩm chất, năng lực số toàn diện cho thế hệ trẻ ngay từ bậc tiểu học.`,
            `Chứng chỉ Tin học Quốc tế IC3 Spark (dành cho học sinh tiểu học từ lớp 3 đến lớp 5) và chứng chỉ MOS (Microsoft Office Specialist dành cho học sinh, sinh viên) do Tập đoàn Khảo thí Tin học Certiport (Hoa Kỳ) và Microsoft xây dựng, được Bộ Giáo dục & Đào tạo Việt Nam công nhận là chuẩn năng lực công nghệ thông tin tương đương. Việc trang bị các chứng chỉ quốc tế này từ sớm giúp các em học sinh làm chủ máy tính, sử dụng Internet thông minh, an toàn và hình thành tư duy số vững chắc trong học tập.`,
            `Tuy nhiên, khảo sát thực tế tại các trường học và trung tâm tin học hiện nay cho thấy: quy trình ôn tập, luyện thi chứng chỉ IC3 Spark vẫn còn phụ thuộc rất nhiều vào sách vở in ấn truyền thống và các bài giảng tĩnh rời rạc. Học sinh không được làm quen với giao diện thi chuẩn quốc tế, thiếu áp lực đồng hồ đếm ngược; giáo viên mất rất nhiều thời gian chấm bài thủ công, khó theo dõi tiến độ từng học sinh; và đặc biệt là phụ huynh hoàn toàn thiếu công cụ đồng hành để biết con mình học đến đâu, đạt kết quả ra sao. Mặt khác, phương thức học tập khô khan dễ khiến các em học sinh tiểu học nản lòng, thiếu động lực học tập tự giác.`,
            `Xuất phát từ những trăn trở thực tiễn đó, nhóm sinh viên đã quyết định lựa chọn và triển khai đề tài tốt nghiệp: "XÂY DỰNG HỆ THỐNG HỌC TẬP VÀ LUYỆN THI CHỨNG CHỈ TIN HỌC QUỐC TẾ IC3 SPARK & MOS TRỰC TUYẾN (IC3 QUEST)". Hệ thống được thiết kế như một thế giới game phiêu lưu giáo dục rực rỡ, tích hợp cơ chế Gamification thông minh (làm bài tích lũy Sao vàng, đua top bảng xếp hạng hiệp sĩ nhí, dùng Sao đổi phút chơi mini-game lành mạnh), giúp học sinh hào hứng tự giác ôn luyện; đồng thời cung cấp công cụ quản trị tinh gọn cho giáo viên và bảng điều khiển trực quan cho phụ huynh.`
        ],
        urgency: `Tính cấp thiết của đề tài được thể hiện qua các điểm then chốt:
1. Nhu cầu xã hội cấp thiết: Đề án phổ cập tin học quốc tế IC3 tại các trường tiểu học tại TP.HCM và cả nước đang được triển khai mạnh mẽ, tạo nhu cầu cực lớn về nền tảng luyện thi chuẩn hóa.
2. Đột phá về phương pháp sư phạm: Ứng dụng Gamification biến quá trình ôn thi áp lực thành hành trình phiêu lưu thú vị, kích thích tư duy và tinh thần tự học của trẻ nhỏ.
3. Giải pháp công nghệ độc lập, tự chủ: Xây dựng trên nền tảng mã nguồn mở hiện đại PHP 8.3 / Laravel 11, tích hợp thanh toán số VietQR / PayOS nội địa và thông báo Telegram, giảm thiểu chi phí bản quyền đắt đỏ của các phần mềm nước ngoài.`,
        objectives: `Mục tiêu nghiên cứu cụ thể:
- Về mặt lý thuyết: Nghiên cứu kiến trúc MVC nâng cao trong Laravel 11/12, các nguyên lý bảo mật web, thuật toán xáo trộn câu hỏi đề thi (Shuffle Algorithm), cơ chế Session/Cookie, và các mô hình thiết kế cơ sở dữ liệu quan hệ 3NF.
- Về mặt thực tiễn: Xây dựng hoàn chỉnh ứng dụng web IC3 Quest vận hành ổn định trên môi trường máy chủ cục bộ Laragon với 4 phân hệ người dùng độc lập: Quản trị viên (Admin), Giáo viên (Teacher), Học sinh (Student), và Phụ huynh (Parent).
- Về mặt chức năng: Ngân hàng 509 câu hỏi chuẩn IC3 GS6 phân chia theo 3 Khối lớp (3, 4, 5) và 7 chủ đề; phòng thi ảo có đồng hồ đếm ngược; chấm điểm tự động thang 1000 điểm IIG; hệ thống đổi Sao lấy giờ chơi game; và tích hợp cổng thanh toán VietQR / PayOS an toàn.`,
        scope: `Đối tượng và phạm vi nghiên cứu:
- Đối tượng: Quy trình kiểm tra, đánh giá năng lực tin học theo chuẩn quốc tế IC3 Spark GS6 và MOS; các mô hình học tập kết hợp trò chơi (Gamification); kiến trúc framework Laravel và cơ sở dữ liệu quan hệ MySQL.
- Phạm vi: Hệ thống web application chạy trên môi trường máy chủ cục bộ (Laragon / Apache / MySQL / PHP 8.3), tập trung vào chương trình IC3 GS6 Spark cấp độ 1, 2, 3 (Khối 3, 4, 5).`,
        structure: `Bố cục của báo cáo đồ án tốt nghiệp gồm 04 chương:
- Chương 1: TỔNG QUAN TÀI LIỆU – Khảo sát bài toán thực tế, chỉ ra thực trạng, đề xuất giải pháp, mục tiêu và cơ sở lý thuyết công nghệ.
- Chương 2: PHƯƠNG PHÁP THỰC HIỆN – Phân tích quy trình nghiệp vụ 5 bước, yêu cầu chức năng, sơ đồ khối, 12 biểu đồ Use Case & Activity, sơ đồ ERD và đặc tả chi tiết 17 bảng CSDL MySQL.
- Chương 3: CÀI ĐẶT THỰC NGHIỆM VÀ KẾT QUẢ – Mô tả môi trường cài đặt, phân tích 16 hình ảnh giao diện chụp thực tế, minh họa 5 đoạn mã nguồn thuật toán tiêu biểu và bảng kiểm thử chất lượng 10 Test Cases.
- Chương 4: KẾT LUẬN VÀ HƯỚNG PHÁT TRIỂN – Đánh giá kết quả đạt được, kỹ năng tích lũy, hạn chế và định hướng phát triển.`
    },

    chapter1: {
        title: "Chương 1. TỔNG QUAN TÀI LIỆU",
        sections: [
            {
                title: "1.1. Khảo sát bài toán thực tế",
                subsections: [
                    {
                        title: "1.1.1. Thực trạng tổ chức đào tạo và thi chứng chỉ tin học quốc tế",
                        content: `Trong bối cảnh nền giáo dục Việt Nam đang đẩy mạnh chuyển đổi số theo Quyết định số 131/QĐ-TTg của Thủ tướng Chính phủ, tin học đã trở thành môn học bắt buộc từ lớp 3. Các chứng chỉ tin học quốc tế như IC3 Spark (dành cho học sinh tiểu học) và MOS (dành cho học sinh trung học và sinh viên) đang được đông đảo các trường học, trung tâm khảo thí và phụ huynh lựa chọn làm thước đo chuẩn mực để đánh giá năng lực số của học sinh.

Tuy nhiên, công tác ôn tập và luyện thi hiện nay tại phần lớn các cơ sở giáo dục vẫn còn gặp phải nhiều rào cản lớn:
1. Thiếu môi trường trải nghiệm thi chuẩn xác: Các bài thi IC3 Spark trên thực tế của Certiport được thực hiện hoàn toàn trên máy tính, có giao diện mô phỏng tương tác, đồng hồ đếm ngược và quy tắc tính điểm chuẩn 1000 điểm. Khi học sinh chỉ ôn luyện trên giấy hoặc file PDF tĩnh, các em thường bị bỡ ngỡ, lúng túng và chịu áp lực tâm lý lớn khi bước vào phòng thi thật.
2. Gánh nặng công việc của giáo viên: Giáo viên tin học tại trường tiểu học thường phải phụ trách nhiều lớp với sĩ số từ 35 - 45 học sinh/lớp. Việc in ấn đề cương, chấm bài thủ công và theo dõi tiến độ từng học sinh ngốn rất nhiều thời gian, khiến giáo viên khó có thể kèm cặp sát sao các học sinh còn yếu.
3. Sự tách rời của phụ huynh: Phụ huynh học sinh tiểu học rất mong muốn biết con mình có tiến bộ hay không, nhưng hiện tại không có kênh thông tin tự động nào giúp họ theo dõi điểm số, số bài thi đã hoàn thành hay các chủ đề con thường xuyên làm sai để kịp thời hỗ trợ.
4. Thiếu động lực và yếu tố tương tác: Học sinh tiểu học có đặc thù tâm lý hiếu động, ưa thích hình ảnh trực quan sinh động và dễ chán nản trước những bài tập văn bản đơn điệu. Việc thiếu yếu tố khen thưởng, thi đua lành mạnh làm giảm sút hứng thú học tập của các em.`
                    },
                    {
                        title: "1.1.2. Đề xuất giải pháp xây dựng hệ thống IC3 Quest",
                        content: `Để giải quyết triệt để những bất cập trên, nhóm tác giả đề xuất xây dựng hệ thống IC3 Quest – nền tảng học tập và luyện thi trực tuyến chuyên biệt cho học sinh tiểu học luyện thi chứng chỉ quốc tế IC3 Spark kết hợp phong cách game phiêu lưu giáo dục (Gamification).

Giải pháp của IC3 Quest tập trung vào các trụ cột đột phá:
- Giao diện 3D Gamification đa sắc màu: Sử dụng hệ thống thẻ nổi 3D (3D Cards) xúc giác, đổ bóng đa tầng rực rỡ, âm thanh phản hồi sinh động, biến mỗi bài kiểm tra thành một "Nhiệm vụ vượt ải" hào hứng.
- Cơ chế khen thưởng và Đổi giờ chơi game: Học sinh thi đạt (điểm số >= 700/1000) sẽ được thưởng 10 - 20 Ngôi sao tích lũy. Các em có thể dùng Sao để vinh danh trên Bảng xếp hạng Top 10 Hiệp sĩ nhí hoặc đổi lấy các gói phút chơi mini-game giải trí lành mạnh ngay trên web. Hệ thống tự động đếm lùi thời gian chơi game và ngắt khi hết giờ, giúp phụ huynh hoàn toàn an tâm con không bị nghiện game.
- Bộ đề chuẩn quốc tế: Tích hợp 35 bài luyện thi với hơn 509 câu hỏi chất lượng cao thuộc 7 chủ đề chuẩn của IC3 GS6 (Điện toán căn bản, Các ứng dụng then chốt, Cuộc sống trực tuyến), đầy đủ hình ảnh minh họa chi tiết.
- Phân hệ quản lý toàn diện cho Giáo viên và Phụ huynh: Giáo viên có thể tạo lớp học, gán học sinh, quản trị ngân hàng câu hỏi qua Question Studio; Phụ huynh có Parent Dashboard với biểu đồ phân tích năng lực và hệ thống cảnh báo sớm thông minh.`
                    },
                    {
                        title: "1.1.3. Mục tiêu cụ thể của đề tài tốt nghiệp",
                        content: `Đề tài tập trung hoàn thành các mục tiêu cụ thể sau:
1. Xây dựng hoàn chỉnh website IC3 Quest với mã nguồn tối ưu trên nền tảng PHP 8.3 và Laravel 11.x, tuân thủ các tiêu chuẩn PSR-12, bảo mật CSRF và XSS.
2. Thiết kế và chuẩn hóa cơ sở dữ liệu quan hệ MySQL gồm 17 bảng dữ liệu, lưu trữ toàn vẹn thông tin người dùng, ngân hàng câu hỏi, lịch sử nộp bài và các giao dịch Sao/giờ chơi.
3. Phát triển 4 phân hệ chức năng tương ứng với 4 nhóm đối tượng: Quản trị viên (Admin), Giáo viên (Teacher), Học sinh (Student), Phụ huynh (Parent).
4. Tích hợp cổng thanh toán trực tuyến VietQR và PayOS cho phép giáo viên mua gói bản quyền phần mềm nhanh chóng bằng ứng dụng Mobile Banking của mọi ngân hàng Việt Nam.
5. Tích hợp kết nối Webhook Telegram Bot (@sp_trikun_bot) hỗ trợ widget Live Chat thời gian thực trên website, giúp khách hàng gửi câu hỏi tư vấn trực tiếp đến điện thoại quản trị viên.`
                    },
                    {
                        title: "1.1.4. Khảo sát các hệ thống thi trực tuyến hiện nay",
                        content: `Nhằm đánh giá đúng vị trí và tính ưu việt của đề tài, nhóm đã tiến hành khảo sát, phân tích so sánh IC3 Quest với các hệ thống thi trực tuyến phổ biến hiện nay:

Bảng 1.1: Bảng so sánh tính năng giữa IC3 Quest và các hệ thống thi trực tuyến hiện nay

| Tiêu chí so sánh | Google Forms | Azota | GMetrix / TestPrep | IC3 Quest (MOS) |
| :--- | :--- | :--- | :--- | :--- |
| **Giao diện & Trải nghiệm** | Đơn điệu, dạng form khảo sát | Phẳng thông thường | Giao diện cũ, phức tạp | Giao diện 3D Gamified rực rỡ, hấp dẫn học sinh tiểu học |
| **Độ tương thích chuẩn IC3** | Thấp, chỉ có trắc nghiệm cơ bản | Trung bình | Rất cao, chuẩn Certiport | Chuẩn kiến thức IC3 GS6 Spark 7 chủ đề, thang 1000 điểm IIG |
| **Yếu tố Gamification** | Không có | Rất hạn chế | Không có | Sao thưởng, Huy hiệu, Đua top hiệp sĩ, Đổi giờ chơi mini-game |
| **Góc phụ huynh giám sát** | Không có | Có xem điểm cơ bản | Không có | Dashboard chuyên sâu, biểu đồ radar/doughnut, cảnh báo thông minh |
| **Thanh toán nội địa** | Không có | Thẻ cào / Chuyển khoản | Thẻ tín dụng quốc tế (USD) | VietQR quét mã tức thì & Cổng PayOS hoàn toàn tự động |
| **Hỗ trợ khách hàng** | Không có | Email / Zalo chậm | Ticket tiếng Anh | Live Chat trực tiếp trên web đồng bộ Telegram Bot realtime |
| **Chi phí sử dụng** | Miễn phí | Trả phí theo gói | Rất đắt (50$ - 100$/code) | Chi phí tối ưu, có chính sách linh hoạt cho trường học |

Qua bảng so sánh trên, có thể khẳng định IC3 Quest hội tụ đầy đủ những ưu điểm vượt trội: bám sát chuẩn kiến thức quốc tế, mang lại trải nghiệm hào hứng cho học sinh nhỏ tuổi, cung cấp công cụ đồng hành thiết thực cho phụ huynh và hỗ trợ thanh toán, chăm sóc khách hàng tự động tối tân.`
                    }
                ]
            },
            {
                title: "1.2. Cơ sở lý thuyết công nghệ",
                subsections: [
                    {
                        title: "1.2.1. Ngôn ngữ lập trình PHP 8.3",
                        content: `PHP (Hypertext Preprocessor) là ngôn ngữ kịch bản mã nguồn mở chạy phía máy chủ (server-side), được tối ưu hóa chuyên biệt cho việc phát triển các ứng dụng web động quy mô lớn. Được sáng lập từ năm 1995, PHP hiện vẫn là nền tảng vận hành của hơn 77% website toàn cầu (theo W3Techs).

Phiên bản PHP 8.3 được áp dụng trong dự án IC3 Quest mang lại những đột phá công nghệ vượt bậc:
- JIT (Just-In-Time) Compiler: Biên dịch mã bytecode sang mã máy trực tiếp tại thời điểm thực thi, tăng tốc độ xử lý các tác vụ tính toán điểm số và thuật toán xáo trộn câu hỏi nhanh hơn từ 15% - 25% so với các phiên bản cũ.
- Readonly Classes & Typed Constants: Cho phép định nghĩa các lớp dữ liệu và hằng số có định kiểu chặt chẽ, ngăn chặn việc biến đổi trạng thái ngoài ý muốn trong các tầng nghiệp vụ DTO và Service.
- Match Expressions & Nullsafe Operator: Cú pháp ngắn gọn, an toàn, thay thế triệt để các khối switch-case cồng kềnh và loại bỏ nguy cơ lỗi truy cập thuộc tính trên đối tượng null.
- Hỗ trợ xử lý JSON bản địa mạnh mẽ: Giúp mã hóa và giải mã các trường dữ liệu tùy biến (metadata, options, logs) cực nhanh.`
                    },
                    {
                        title: "1.2.2. Framework phát triển Laravel 11.x",
                        content: `Laravel là framework PHP phổ biến và được đánh giá cao nhất thế giới hiện nay, được kiến trúc sư Taylor Otwell sáng lập với triết lý mang lại sự thanh lịch, cú pháp biểu cảm và tối ưu hóa năng suất lập trình.

Các thành phần cốt lõi của Laravel được ứng dụng sâu rộng trong hệ thống IC3 Quest bao gồm:
1. Mô hình kiến trúc MVC (Model - View - Controller): Phân tách độc lập hoàn toàn giữa tầng xử lý logic dữ liệu (Eloquent Models: User, Level, Topic, PracticeTest, Question, Answer, Attempt), tầng giao diện người dùng (Blade Templates) và tầng điều hướng tiếp nhận yêu cầu (Controllers).
2. Eloquent ORM: Công cụ ánh xạ đối tượng dữ liệu mạnh mẽ bậc nhất, cho phép định nghĩa các mối quan hệ quan trọng (HasMany, BelongsTo, BelongsToMany, Eager Loading 'with()') giúp triệt tiêu vấn đề truy vấn trùng lặp N+1 Query.
3. Blade Template Engine & Component Architecture: Cung cấp cú pháp kế thừa layout '@extends', các thẻ '@component', '<x-slot>' và Blade Components tái sử dụng cao, giúp giao diện website đồng nhất 100% và sạch đẹp.
4. Middleware Pipeline: Hệ thống bộ lọc request trung gian đảm nhận kiểm soát phiên làm việc (Session), phòng chống tấn công CSRF bằng thẻ mã hóa ngẫu nhiên, và phân quyền truy cập nghiêm ngặt theo vai trò ('auth', 'admin', 'student').
5. Database Migrations & Seeders: Quản lý vòng đời cấu trúc bảng cơ sở dữ liệu bằng mã nguồn PHP, đảm bảo tính nhất quán tuyệt đối giữa các môi trường phát triển.`
                    },
                    {
                        title: "1.2.3. Hệ quản trị cơ sở dữ liệu MySQL 8.x",
                        content: `MySQL là hệ quản trị cơ sở dữ liệu quan hệ (RDBMS) mã nguồn mở hàng đầu thế giới, nổi tiếng với độ ổn định, tính toàn vẹn tham chiếu ACID và tốc độ truy vấn cực nhanh.

Trong hệ thống IC3 Quest, MySQL 8.x đảm nhận lưu trữ 17 bảng quan hệ được chuẩn hóa theo dạng chuẩn 3NF (Third Normal Form):
- Hỗ trợ kiểu dữ liệu JSON bản địa: Lưu trữ linh hoạt các tùy chọn đáp án, dữ liệu metadata câu hỏi và nhật ký hành động người dùng mà không cần tạo thêm các bảng con dư thừa.
- Khóa chính (Primary Key) tự tăng và Khóa ngoại (Foreign Key) có ràng buộc toàn vẹn 'ON DELETE CASCADE': Giúp bảo vệ dữ liệu câu hỏi, đáp án và kết quả thi không bao giờ bị mồ côi khi có thao tác xóa danh mục.
- Tối ưu hóa chỉ mục (Indexes): Đánh index trên các cột truy vấn thường xuyên như 'slug', 'email', 'role', 'practice_test_id', 'user_id' và 'completed_at', giúp các truy vấn lọc bảng xếp hạng và biểu đồ phụ huynh trả kết quả dưới 20 mili-giây.`
                    },
                    {
                        title: "1.2.4. Công nghệ Frontend hiện đại và Phong cách 3D Gamification",
                        content: `Giao diện người dùng của IC3 Quest được xây dựng dựa trên sự kết hợp hoàn hảo giữa HTML5 ngữ nghĩa, CSS3 nâng cao (TailwindCSS) và JavaScript ES6+ hiện đại:
- Thiết kế Thẻ nổi 3D (3D Cards): Áp dụng chuẩn viền nổi kép \`border: 3.5px solid #ffffff\`, kết hợp bóng đổ nổi khối sâu \`box-shadow: 0 16px 36px rgba(0,0,0,0.18), inset 0 -6px 0 rgba(0,0,0,0.15)\` tạo cảm giác các khối nổi hẳn lên khỏi bề mặt màn hình như những viên gạch đồ chơi Lego.
- Nút bấm tactile xúc giác sống động: Tạo cảm giác bấm chân thực với hiệu ứng nổi khi di chuột (\`translateY(-2px)\`) và lún sâu khi bấm (\`active: translateY(4px)\`), mang lại niềm vui tương tác cho các em nhỏ.
- Bảng màu phong phú đầy năng lượng: Sử dụng bảng màu chọn lọc khoa học gồm Cam hổ phách (\`Amber\`), Xanh ngọc lục bảo (\`Emerald\`), Xanh biển Sky Blue, Tím thạch anh (\`Purple\`), Hồng ngọc Neon (\`Pink\`) và Vàng kim Hoàng gia (\`Gold\`).
- Thiết kế Responsive hoàn chỉnh: Tối ưu hiển thị sắc nét trên cả màn hình máy tính để bàn phòng máy trường học, máy tính xách tay và máy tính bảng cá nhân.`
                    },
                    {
                        title: "1.2.5. Cổng thanh toán trực tuyến PayOS và Chuẩn VietQR",
                        content: `Để giải quyết bài toán thu phí bản quyền gói dịch vụ một cách chuyên nghiệp và minh bạch, IC3 Quest đã tích hợp giải pháp thanh toán điện tử thế hệ mới:
- Chuẩn mã thanh toán VietQR: Hệ thống tự động sinh mã QR động chứa đầy đủ thông tin số tài khoản ngân hàng, tên người thụ hưởng, số tiền chính xác theo gói thuê và mã đơn hàng duy nhất dạng \`MOS-YYYYMM-XXXXX\` trong nội dung chuyển khoản. Giáo viên chỉ cần mở ứng dụng Mobile Banking của bất kỳ ngân hàng nào quét mã là hoàn tất trong 3 giây.
- Cổng thanh toán trực tuyến PayOS: Kết nối API thanh toán an toàn, tự động nhận Webhook khi giao dịch hoàn tất, tự động kích hoạt tài khoản giáo viên và gia hạn thời gian sử dụng bản quyền mà không cần sự can thiệp thủ công của nhân sự quản trị.`
                    },
                    {
                        title: "1.2.6. Tích hợp Telegram Bot Webhook & Live Chat Realtime",
                        content: `Nhằm hỗ trợ chăm sóc khách hàng và tư vấn giáo viên kịp thời nhất:
- Widget Live Chat Messenger: Được nhúng trực tiếp ở góc phải dưới màn hình của website, cho phép khách truy cập hoặc giáo viên gửi câu hỏi tư vấn, số điện thoại liên hệ mà không bắt buộc phải đăng nhập.
- Telegram Bot Webhook (@sp_trikun_bot): Khi có khách hàng gửi tin nhắn trên web, hệ thống tự động bắn một thông báo chi tiết qua Telegram Bot API đến điện thoại của Quản trị viên (gồm tên khách, số điện thoại, nội dung câu hỏi và thời gian). Quản trị viên có thể phản hồi trực tiếp từ ứng dụng quản trị hoặc qua bot, tạo luồng chăm sóc khách hàng khép kín và chuyên nghiệp.`
                    },
                    {
                        title: "1.2.7. Trình soạn thảo mã nguồn Visual Studio Code",
                        content: `Visual Studio Code (VS Code) là môi trường phát triển tích hợp (IDE) gọn nhẹ nhưng cực kỳ mạnh mẽ do Microsoft phát triển. Trong suốt quá trình thực hiện đồ án, nhóm đã khai thác tối đa sức mạnh của VS Code kết hợp với các phần mở rộng (Extensions) chuyên dụng: PHP Intelephense (hỗ trợ kiểm tra kiểu và gợi ý cú pháp PHP), Laravel Blade Snippets, Tailwind CSS IntelliSense, GitLens (quản lý phân nhánh Git) và MySQL Client, giúp nâng cao chất lượng mã nguồn và tốc độ phát triển dự án.`
                    },
                    {
                        title: "1.2.8. Môi trường máy chủ cục bộ Laragon",
                        content: `Laragon là môi trường máy chủ cục bộ (local development environment) tối ưu, nhẹ nhàng và cô lập trên hệ điều hành Windows 11. Laragon tích hợp đồng bộ các phần mềm máy chủ phiên bản mới nhất: Apache 2.4.62, MySQL 8.0.30, PHP 8.3.28 và Node.js v24.19.0. Laragon cung cấp cơ chế tự động tạo Virtual Host cục bộ và chứng chỉ SSL tự ký, đảm bảo môi trường lập trình và thử nghiệm của đồ án có tính tương thích và ổn định tương đương với máy chủ triển khai trên môi trường sản xuất thực tế.`
                    }
                ]
            }
        ]
    }
};
