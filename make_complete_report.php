<?php
/**
 * GENERATE COMPLETE REPORT FOR IC3 QUEST / MOS
 */

require_once __DIR__ . '/DocxReportGenerator.php';

$gen = new DocxReportGenerator();
$md = [];

function addMd(string $line) {
    global $md;
    $md[] = $line;
}

// -------------------------------------------------------------
// 1. TRANG BÌA NGOÀI
// -------------------------------------------------------------
$gen->addParagraph("BỘ GIÁO DỤC VÀ ĐÀO TẠO", 'center', 28, true, false, '002060', 120, 40, 0);
$gen->addParagraph("TRƯỜNG CAO ĐẲNG CÔNG NGHỆ THÔNG TIN TP.HCM", 'center', 32, true, false, '002060', 40, 40, 0);
$gen->addParagraph("KHOA CÔNG NGHỆ THÔNG TIN", 'center', 28, true, false, '002060', 40, 360, 0);

$gen->addParagraph("BÁO CÁO ĐỒ ÁN MÔN HỌC", 'center', 32, true, false, 'C00000', 360, 80, 0);
$gen->addParagraph("CHUYÊN ĐỀ LẬP TRÌNH PHP", 'center', 28, true, false, '002060', 40, 360, 0);

$gen->addParagraph("ĐỀ TÀI:", 'center', 28, true, false, '000000', 360, 80, 0);
$gen->addParagraph("XÂY DỰNG HỆ THỐNG HỌC TẬP VÀ LUYỆN THI CHỨNG CHỈ TIN HỌC QUỐC TẾ IC3 SPARK & MOS TRỰC TUYẾN (IC3 QUEST)", 'center', 36, true, false, '002060', 80, 720, 0);

$gen->addTable(
    ['THÔNG TIN HƯỚNG DẪN VÀ THỰC HIỆN', 'CHI TIẾT'],
    [
        ['Giảng viên hướng dẫn:', 'Thầy Huỳnh Luân'],
        ['Sinh viên thực hiện 1 (Nhóm trưởng):', 'Lê Minh Trí          MSSV: (Tạm để trống)'],
        ['Sinh viên thực hiện 2:', 'Âu Lê Thành Tài       MSSV: (Tạm để trống)'],
        ['Sinh viên thực hiện 3:', 'Võ Trung Kiều Diễm    MSSV: (Tạm để trống)'],
        ['Ngành học:', 'Công nghệ Thông tin'],
        ['Lớp / Khóa:', 'CD25CT1 / Khóa K25'],
    ],
    [3200, 5871]
);

$gen->addParagraph("TP. Hồ Chí Minh, tháng 09 năm 2026", 'center', 26, true, false, '595959', 720, 120, 0);
$gen->addPageBreak();

// -------------------------------------------------------------
// 2. TRANG BÌA TRONG (BÌA LÓT)
// -------------------------------------------------------------
$gen->addParagraph("BỘ GIÁO DỤC VÀ ĐÀO TẠO", 'center', 28, true, false, '002060', 120, 40, 0);
$gen->addParagraph("TRƯỜNG CAO ĐẲNG CÔNG NGHỆ THÔNG TIN TP.HCM", 'center', 32, true, false, '002060', 40, 40, 0);
$gen->addParagraph("KHOA CÔNG NGHỆ THÔNG TIN", 'center', 28, true, false, '002060', 40, 360, 0);

$gen->addParagraph("BÁO CÁO ĐỒ ÁN MÔN HỌC", 'center', 32, true, false, 'C00000', 360, 80, 0);
$gen->addParagraph("CHUYÊN ĐỀ LẬP TRÌNH PHP", 'center', 28, true, false, '002060', 40, 360, 0);

$gen->addParagraph("ĐỀ TÀI:", 'center', 28, true, false, '000000', 360, 80, 0);
$gen->addParagraph("XÂY DỰNG HỆ THỐNG HỌC TẬP VÀ LUYỆN THI CHỨNG CHỈ TIN HỌC QUỐC TẾ IC3 SPARK & MOS TRỰC TUYẾN (IC3 QUEST)", 'center', 36, true, false, '002060', 80, 720, 0);

$gen->addTable(
    ['THÔNG TIN HƯỚNG DẪN VÀ THỰC HIỆN', 'CHI TIẾT'],
    [
        ['Giảng viên hướng dẫn:', 'Thầy Huỳnh Luân'],
        ['Sinh viên thực hiện 1 (Nhóm trưởng):', 'Lê Minh Trí          MSSV: (Tạm để trống)'],
        ['Sinh viên thực hiện 2:', 'Âu Lê Thành Tài       MSSV: (Tạm để trống)'],
        ['Sinh viên thực hiện 3:', 'Võ Trung Kiều Diễm    MSSV: (Tạm để trống)'],
        ['Ngành học:', 'Công nghệ Thông tin'],
        ['Lớp / Khóa:', 'CD25CT1 / Khóa K25'],
    ],
    [3200, 5871]
);

$gen->addParagraph("TP. Hồ Chí Minh, tháng 09 năm 2026", 'center', 26, true, false, '595959', 720, 120, 0);
$gen->addPageBreak();

// -------------------------------------------------------------
// 3. LỜI CẢM ƠN
// -------------------------------------------------------------
$gen->addHeading1("LỜI CẢM ƠN");
$gen->addParagraph("Để hoàn thành báo cáo đồ án môn học này, tập thể nhóm sinh viên chúng em xin gửi lời cảm ơn chân thành và sâu sắc nhất đến Ban Giám hiệu, quý Thầy Cô Khoa Công Nghệ Thông Tin – Trường Cao đẳng Công nghệ Thông tin TP.HCM (ITC). Trong suốt quá trình học tập tại trường, quý Thầy Cô đã luôn tận tâm truyền thụ cho chúng em những nền tảng kiến thức chuyên sâu, tư duy lập trình khoa học cùng những kinh nghiệm thực tế quý báu, tạo bệ phóng vững chắc để chúng em tự tin nghiên cứu và hiện thực hóa các sản phẩm công nghệ.", 'both', 26, false, false, '000000', 80, 80, 720);

$gen->addParagraph("Đặc biệt, nhóm chúng em xin bày tỏ lòng biết ơn sâu sắc và lời tri ân đặc biệt nhất đến Thầy Huỳnh Luân – Giảng viên hướng dẫn trực tiếp đồ án môn học. Thầy đã luôn dành thời gian theo sát, định hướng phương pháp tiếp cận đúng đắn từ những ngày đầu khởi động ý tưởng, chỉ dẫn chi tiết về cấu trúc MVC trong Laravel, cơ chế phân tích cú pháp dữ liệu ngân hàng câu hỏi iSpring HTML5 độc lập, cho đến việc giải quyết bài toán chống gian lận và tối ưu hóa thời gian thực trong phòng thi ảo. Những lời nhận xét nghiêm khắc, tinh tế và đầy tâm huyết của Thầy chính là kim chỉ nam giúp sản phẩm đồ án của nhóm đạt được độ hoàn thiện kỹ thuật cao nhất.", 'both', 26, false, false, '000000', 80, 80, 720);

$gen->addParagraph("Mặc dù nhóm đã nỗ lực hết mình để phân tích, thiết kế và lập trình hoàn thiện hệ thống, song với kinh nghiệm thực tế còn khiêm tốn, đồ án khó tránh khỏi những thiếu sót nhất định. Nhóm chúng em rất mong nhận được những ý kiến đóng góp, chỉ dẫn quý báu từ quý Thầy Cô hội đồng chấm thi để nhóm có thể tiếp tục hoàn thiện, phát triển hệ thống ngày một vững chắc và tối ưu hơn trong tương lai.", 'both', 26, false, false, '000000', 80, 80, 720);

$gen->addParagraph("Nhóm sinh viên thực hiện xin kính chúc Thầy Huỳnh Luân cùng toàn thể quý Thầy Cô Khoa Công nghệ Thông tin luôn dồi dào sức khỏe, tràn đầy niềm say mê và gặt hái được nhiều thành tựu rực rỡ trong sự nghiệp giáo dục cao quý!", 'both', 26, false, false, '000000', 80, 160, 720);

$gen->addParagraph("TP. Hồ Chí Minh, ngày 14 tháng 09 năm 2026", 'right', 26, false, true, '000000', 120, 40, 0);
$gen->addParagraph("Tập thể nhóm sinh viên thực hiện:", 'right', 26, true, false, '000000', 40, 40, 0);
$gen->addParagraph("Lê Minh Trí – Âu Lê Thành Tài – Võ Trung Kiều Diễm", 'right', 26, true, false, '002060', 40, 120, 0);
$gen->addPageBreak();

// -------------------------------------------------------------
// 4. LỜI CAM ĐOAN
// -------------------------------------------------------------
$gen->addHeading1("LỜI CAM ĐOAN");
$gen->addParagraph("Nhóm sinh viên thực hiện xin cam đoan đề tài: “Xây dựng hệ thống học tập và luyện thi chứng chỉ tin học quốc tế IC3 Spark & MOS trực tuyến (IC3 Quest)” là công trình nghiên cứu và xây dựng phần mềm thực tế độc lập của nhóm chúng em dưới sự định hướng, hướng dẫn chuyên môn của Thầy Huỳnh Luân.", 'both', 26, false, false, '000000', 80, 80, 720);

$gen->addParagraph("Toàn bộ nội dung phân tích bài toán, các sơ đồ thiết kế UML (Use Case, Activity Diagram), cấu trúc cơ sở dữ liệu quan hệ 12 bảng, mã nguồn lập trình trên nền tảng PHP 8.3 / Laravel Framework, giao diện tương tác Blade, thuật toán xử lý dữ liệu 509 câu hỏi và kết quả kiểm thử thực nghiệm được trình bày trong cuốn báo cáo này hoàn toàn xuất phát từ sự lao động nghiêm túc của nhóm.", 'both', 26, false, false, '000000', 80, 80, 720);

$gen->addParagraph("Mọi thông tin, dữ liệu trích dẫn tham khảo từ các tài liệu khoa học, thư viện mã nguồn mở và chuẩn mực chứng chỉ quốc tế IC3 GS6 đều được nhóm ghi chú nguồn gốc xuất xứ rõ ràng trong Danh mục tài liệu tham khảo. Nhóm chúng em xin chịu mọi hình thức kỷ luật theo quy chế đào tạo của Nhà trường nếu có bất kỳ hành vi sao chép không trung thực nào.", 'both', 26, false, false, '000000', 80, 160, 720);

$gen->addParagraph("TP. Hồ Chí Minh, ngày 14 tháng 09 năm 2026", 'right', 26, false, true, '000000', 120, 40, 0);
$gen->addParagraph("Đại diện nhóm sinh viên (Nhóm trưởng)", 'right', 26, true, false, '000000', 40, 140, 0);
$gen->addParagraph("(Ký và ghi rõ họ tên)", 'right', 24, false, true, '595959', 40, 200, 0);
$gen->addParagraph("Lê Minh Trí", 'right', 26, true, false, '002060', 120, 120, 0);
$gen->addPageBreak();

// -------------------------------------------------------------
// 5. NHẬN XÉT CỦA GIẢNG VIÊN HƯỚNG DẪN & PHẢN BIỆN
// -------------------------------------------------------------
$gen->addHeading1("NHẬN XÉT CỦA GIẢNG VIÊN HƯỚNG DẪN");
$gen->addParagraph("Họ và tên giảng viên hướng dẫn: Thầy Huỳnh Luân", 'both', 26, true, false, '002060', 60, 60, 0);
$gen->addParagraph("Đơn vị công tác: Khoa Công nghệ Thông tin – Trường Cao đẳng Công nghệ Thông tin TP.HCM", 'both', 26, false, false, '000000', 40, 120, 0);

$gen->addTable(
    ['TIÊU CHÍ ĐÁNH GIÁ', 'Ý KIẾN NHẬN XÉT CỦA GIẢNG VIÊN HƯỚNG DẪN'],
    [
        ['1. Tinh thần, thái độ làm việc của nhóm sinh viên', '................................................................................................................................................................................................................................................'],
        ['2. Bố cục, hình thức và phương pháp trình bày báo cáo', '................................................................................................................................................................................................................................................'],
        ['3. Cơ sở lý thuyết và năng lực ứng dụng công nghệ (PHP/Laravel/MySQL)', '................................................................................................................................................................................................................................................'],
        ['4. Tính thực tiễn, quy mô chức năng và khả năng ứng dụng của đồ án', '................................................................................................................................................................................................................................................'],
        ['5. Kết quả đạt được so với mục tiêu đề ra ban đầu', '................................................................................................................................................................................................................................................'],
        ['6. Những nội dung cần bổ sung, chỉnh sửa hoặc phát triển tiếp', '................................................................................................................................................................................................................................................'],
    ],
    [3200, 5871]
);

$gen->addParagraph("ĐIỂM ĐÁNH GIÁ: ............ / 10.0 (Bằng chữ: .................................................................................)", 'both', 26, true, false, '002060', 120, 60, 0);
$gen->addParagraph("Kết luận:  [  ] ĐỒNG Ý CHO BẢO VỆ          [  ] KHÔNG ĐỒNG Ý CHO BẢO VỆ", 'both', 26, true, false, '000000', 60, 120, 0);
$gen->addParagraph("TP. Hồ Chí Minh, ngày ...... tháng ...... năm 2026", 'right', 26, false, true, '000000', 120, 40, 0);
$gen->addParagraph("GIẢNG VIÊN HƯỚNG DẪN", 'right', 26, true, false, '002060', 40, 140, 0);
$gen->addParagraph("(Ký và ghi rõ họ tên)", 'right', 24, false, true, '595959', 40, 200, 0);
$gen->addParagraph("Thầy Huỳnh Luân", 'right', 26, true, false, '002060', 120, 120, 0);
$gen->addPageBreak();

$gen->addHeading1("NHẬN XÉT CỦA GIẢNG VIÊN PHẢN BIỆN");
$gen->addParagraph("Họ và tên giảng viên phản biện: ........................................................................................................................", 'both', 26, false, false, '000000', 60, 60, 0);
$gen->addParagraph("Đơn vị công tác: Khoa Công nghệ Thông tin – Trường Cao đẳng Công nghệ Thông tin TP.HCM", 'both', 26, false, false, '000000', 40, 120, 0);

$gen->addTable(
    ['TIÊU CHÍ ĐÁNH GIÁ', 'Ý KIẾN NHẬN XÉT CỦA GIẢNG VIÊN PHẢN BIỆN'],
    [
        ['1. Tính cấp thiết và giá trị thực tiễn của đề tài', '................................................................................................................................................................................................................................................'],
        ['2. Chất lượng phân tích, thiết kế hệ thống và CSDL', '................................................................................................................................................................................................................................................'],
        ['3. Độ hoàn thiện của chương trình phần mềm demo', '................................................................................................................................................................................................................................................'],
        ['4. Ưu điểm nổi bật và những hạn chế còn tồn tại', '................................................................................................................................................................................................................................................'],
        ['5. Câu hỏi dành cho nhóm sinh viên trong buổi bảo vệ', '1) .............................................................................................................................................................\n2) .............................................................................................................................................................'],
    ],
    [3200, 5871]
);

$gen->addParagraph("ĐIỂM ĐÁNH GIÁ: ............ / 10.0 (Bằng chữ: .................................................................................)", 'both', 26, true, false, '002060', 120, 60, 0);
$gen->addParagraph("TP. Hồ Chí Minh, ngày ...... tháng ...... năm 2026", 'right', 26, false, true, '000000', 120, 40, 0);
$gen->addParagraph("GIẢNG VIÊN PHẢN BIỆN", 'right', 26, true, false, '002060', 40, 140, 0);
$gen->addParagraph("(Ký và ghi rõ họ tên)", 'right', 24, false, true, '595959', 40, 200, 0);
$gen->addParagraph("........................................................", 'right', 26, true, false, '002060', 120, 120, 0);
$gen->addPageBreak();

// -------------------------------------------------------------
// 6. LỊCH LÀM VIỆC CỦA SINH VIÊN
// -------------------------------------------------------------
$gen->addHeading1("LỊCH LÀM VIỆC CỦA NHÓM SINH VIÊN");
$gen->addParagraph("Bảng tiến độ và phân công nhiệm vụ chi tiết trong suốt quá trình triển khai đề tài:", 'both', 26, false, false, '000000', 60, 100, 0);

$gen->addTable(
    ['TUẦN', 'NỘI DUNG CÔNG VIỆC', 'NGƯỜI PHỤ TRÁCH', 'KẾT QUẢ ĐẠT ĐƯỢC'],
    [
        ['Tuần 1', 'Khảo sát bài toán thực tế, tìm hiểu đề thi IC3 GS6 & MOS. Họp nhóm thống nhất mục tiêu đề tài.', 'Cả nhóm', 'Hoàn thiện Đề cương chi tiết đồ án'],
        ['Tuần 2', 'Phân tích yêu cầu nghiệp vụ, xác định các vai trò (Admin, Giáo viên, Học sinh, Phụ huynh).', 'Lê Minh Trí', 'Tài liệu đặc tả yêu cầu nghiệp vụ'],
        ['Tuần 3', 'Thiết kế các lược đồ UML: Use Case tổng thể, 10 Use Cases chi tiết, Activity Diagrams.', 'Âu Lê Thành Tài', 'Bộ tài liệu biểu đồ thiết kế UML'],
        ['Tuần 4', 'Thiết kế cơ sở dữ liệu quan hệ (ERD), chuẩn hóa 12 bảng MySQL (users, tests, questions...).', 'Võ Trung Kiều Diễm', 'Script Migration & Seeder Laravel'],
        ['Tuần 5', 'Xây dựng kiến trúc Laravel, cấu hình xác thực đa vai trò, phân hệ Admin Dashboard & Quản lý lớp.', 'Lê Minh Trí', 'Khung ứng dụng chạy ổn định trên Laragon'],
        ['Tuần 6', 'Phát triển module IC3 Question Studio, cơ chế Import trích xuất 509 câu hỏi từ gói iSpring HTML5.', 'Lê Minh Trí\nÂu Lê Thành Tài', 'Ngân hàng 509 câu hỏi lưu vào CSDL MySQL'],
        ['Tuần 7', 'Xây dựng giao diện học sinh, phòng thi ảo (Native Quiz Player), logic đếm ngược & nộp bài.', 'Âu Lê Thành Tài', 'Học sinh làm bài trắc nghiệm tương tác'],
        ['Tuần 8', 'Xây dựng thuật toán tính điểm, cơ chế tích lũy Sao thưởng, đổi quà game, Dashboard phụ huynh.', 'Võ Trung Kiều Diễm', 'Hoàn thiện Gamification & Báo cáo phụ huynh'],
        ['Tuần 9', 'Kiểm thử chất lượng phần mềm (10 Test Cases), tối ưu hóa giao diện CSS, khắc phục lỗi bảo mật.', 'Cả nhóm', 'Hệ thống vận hành trơn tru, không phát sinh lỗi'],
        ['Tuần 10', 'Soạn thảo tài liệu báo cáo hoàn chỉnh theo quy chuẩn ITC, chuẩn bị slide và video demo bảo vệ.', 'Cả nhóm', 'Báo cáo đồ án và sản phẩm phần mềm demo'],
    ],
    [1000, 3871, 1800, 2400]
);
$gen->addPageBreak();

// -------------------------------------------------------------
// 7. TÓM TẮT ĐỒ ÁN
// -------------------------------------------------------------
$gen->addHeading1("TÓM TẮT ĐỒ ÁN");
$gen->addParagraph("TÓM TẮT TIẾNG VIỆT", 'left', 26, true, false, '002060', 60, 60, 0);
$gen->addParagraph("Chứng chỉ Tin học Quốc tế IC3 Spark (dành cho học sinh tiểu học) và MOS (dành cho học sinh, sinh viên) ngày càng giữ vai trò quan trọng trong việc chuẩn hóa năng lực ứng dụng công nghệ thông tin trong kỷ nguyên số. Tuy nhiên, các giải pháp ôn luyện truyền thống chủ yếu dựa trên sách in hoặc các gói bài giảng đóng gói iSpring tĩnh, phân tán và khó theo dõi tiến độ. Đề tài “Xây dựng hệ thống học tập và luyện thi chứng chỉ tin học quốc tế IC3 Spark & MOS trực tuyến (IC3 Quest)” được nhóm nghiên cứu và phát triển nhằm giải quyết triệt để vấn đề trên.", 'both', 26, false, false, '000000', 60, 60, 720);

$gen->addParagraph("Hệ thống được phát triển trên nền tảng công nghệ hiện đại gồm PHP 8.3, Laravel Framework 11/13, hệ quản trị cơ sở dữ liệu quan hệ MySQL 8.x và kiến trúc giao diện động với TailwindCSS và JavaScript ES6. Điểm đột phá về mặt kỹ thuật của đồ án là đã nghiên cứu và phát triển thành công giải thuật trích xuất tự động toàn bộ 509 câu hỏi cùng hàng ngàn tài nguyên đa phương tiện từ các gói đề thi đóng gói iSpring HTML5 gốc vào cơ sở dữ liệu MySQL độc lập, giúp loại bỏ hoàn toàn sự phụ thuộc vào các file rác tĩnh, tối ưu hóa dung lượng lưu trữ web server. Hệ thống cung cấp đầy đủ 3 phân hệ chuyên biệt: Quản trị viên (IC3 Question Studio, thống kê trực quan, xuất dữ liệu CSV), Giáo viên (quản lý lớp, phân quyền theo khối lớp), Học sinh (phòng thi ảo với thuật toán trộn ngẫu nhiên câu hỏi/đáp án, giao diện làm bài sinh động, tính điểm chuẩn thời gian thực) cùng cơ chế Gamification tích lũy Sao đổi thời gian minigame và Bảng điều khiển dành cho Phụ huynh giám sát.", 'both', 26, false, false, '000000', 60, 120, 720);

$gen->addParagraph("ABSTRACT IN ENGLISH", 'left', 26, true, false, '002060', 120, 60, 0);
$gen->addParagraph("International computer certificates IC3 Spark and MOS are playing an essential role in standardizing digital literacy in the digital transformation era. Traditional training approaches relying on paper test materials or standalone packaged iSpring multimedia files lack centralized analytics and learning progress monitoring. This project, entitled “Building Online Learning and Examination Preparation System for IC3 Spark & MOS International Computer Certification (IC3 Quest)”, addresses these limitations comprehensively.", 'both', 26, false, false, '000000', 60, 60, 720);

$gen->addParagraph("The system is engineered using state-of-the-art web technologies: PHP 8.3, Laravel 11/13 Framework, MySQL 8.x relational database, TailwindCSS, and JavaScript ES6. The key technological breakthrough of this project is the successful development of an automated parsing algorithm that extracts 509 comprehensive test questions and multimedia assets from legacy iSpring HTML5 packages into an independent normalized relational MySQL schema. IC3 Quest provides rich features across multiple user roles: Admin (Question Studio, analytics dashboard, CSV export), Teachers (classrooms, grade-level access control), Students (interactive virtual quiz player with real-time countdown, question shuffling, instant grading, star-based gamification reward system), and Parents (dedicated monitoring dashboard).", 'both', 26, false, false, '000000', 60, 120, 720);
$gen->addPageBreak();

// -------------------------------------------------------------
// 8. MỤC LỤC & DANH MỤC
// -------------------------------------------------------------
$gen->addHeading1("MỤC LỤC");
$tocItems = [
    ['LỜI CẢM ƠN', 'i'],
    ['LỜI CAM ĐOAN', 'ii'],
    ['NHẬN XÉT CỦA GIẢNG VIÊN HƯỚNG DẪN', 'iii'],
    ['NHẬN XÉT CỦA GIẢNG VIÊN PHẢN BIỆN', 'iv'],
    ['LỊCH LÀM VIỆC CỦA NHÓM SINH VIÊN', 'v'],
    ['TÓM TẮT ĐỒ ÁN', 'vi'],
    ['DANH MỤC SƠ ĐỒ, LƯỢC ĐỒ, BIỂU ĐỒ, HÌNH', 'viii'],
    ['DANH MỤC BẢNG', 'ix'],
    ['KÍ HIỆU CÁC CỤM TỪ VIẾT TẮT', 'x'],
    ['LỜI MỞ ĐẦU', '1'],
    ['Chương 1. TỔNG QUAN TÀI LIỆU', '3'],
    ['   1.1. Khảo sát bài toán thực tế', '3'],
    ['      1.1.1. Thực trạng đào tạo, luyện thi chứng chỉ tin học quốc tế IC3/MOS', '3'],
    ['      1.1.2. Đề xuất giải pháp xây dựng hệ thống trực tuyến IC3 Quest', '4'],
    ['      1.1.3. Mục tiêu cụ thể và phạm vi của đề tài', '5'],
    ['      1.1.4. Khảo sát và so sánh các hệ thống thi trắc nghiệm hiện nay', '6'],
    ['   1.2. Cơ sở lý thuyết và công nghệ áp dụng', '7'],
    ['      1.2.1. Ngôn ngữ lập trình PHP 8.3', '7'],
    ['      1.2.2. Framework phát triển Laravel 11/13', '8'],
    ['      1.2.3. Hệ quản trị cơ sở dữ liệu MySQL 8.x', '10'],
    ['      1.2.4. Công nghệ Frontend (HTML5, TailwindCSS, JavaScript ES6)', '11'],
    ['      1.2.5. Môi trường máy chủ cục bộ Laragon & Visual Studio Code', '12'],
    ['Chương 2. PHƯƠNG PHÁP THỰC HIỆN (PHÂN TÍCH VÀ THIẾT KẾ HỆ THỐNG)', '13'],
    ['   2.1. Phân tích hệ thống nghiệp vụ', '13'],
    ['      2.1.1. Quy trình nghiệp vụ 5 bước cốt lõi của IC3 Quest', '13'],
    ['      2.1.2. Yêu cầu chức năng chi tiết theo từng vai trò', '15'],
    ['      2.1.3. Sơ đồ khối chức năng chi tiết theo từng vai trò', '17'],
    ['   2.2. Các biểu đồ thiết kế hệ thống (UML Diagrams)', '19'],
    ['      2.2.1. Lược đồ Use Case tổng thể toàn hệ thống', '19'],
    ['      2.2.2. Bảng đặc tả chi tiết 10 Use Cases nghiệp vụ trọng tâm', '20'],
    ['      2.2.3. Lược đồ hoạt động (Activity Diagrams) cho 10 Use Cases', '30'],
    ['   2.3. Thiết kế Cơ sở dữ liệu chi tiết', '40'],
    ['      2.3.1. Sơ đồ thực thể mối quan hệ (ERD)', '40'],
    ['      2.3.2. Bảng đặc tả cấu trúc chi tiết 12 bảng CSDL trong MySQL', '41'],
    ['Chương 3. CÀI ĐẶT THỰC NGHIỆM VÀ KẾT QUẢ', '53'],
    ['   3.1. Môi trường cài đặt hệ thống', '53'],
    ['   3.2. Một số giao diện chính và phân tích chức năng (10 giao diện)', '54'],
    ['   3.3. Các đoạn mã nguồn lập trình & thuật toán tiêu biểu', '68'],
    ['   3.4. Kiểm thử chất lượng hệ thống (10 Test Cases)', '74'],
    ['Chương 4. KẾT LUẬN VÀ HƯỚNG PHÁT TRIỂN', '80'],
    ['   4.1. Đánh giá kết quả đồ án', '80'],
    ['   4.2. Định hướng và lộ trình phát triển trong tương lai', '82'],
    ['PHỤ LỤC', '83'],
    ['DANH MỤC TÀI LIỆU THAM KHẢO', '86'],
];
$gen->addTable(['NỘI DUNG MỤC LỤC', 'TRANG'], $tocItems, [7500, 1571]);
$gen->addPageBreak();

// DANH MỤC HÌNH
$gen->addHeading1("DANH MỤC SƠ ĐỒ, LƯỢC ĐỒ, HÌNH");
$figures = [
    ['Hình 2.1', 'Sơ đồ luồng nghiệp vụ 5 bước cốt lõi của hệ thống IC3 Quest', '14'],
    ['Hình 2.2', 'Sơ đồ phân rã chức năng phân hệ Quản trị viên (Admin)', '17'],
    ['Hình 2.3', 'Sơ đồ phân rã chức năng phân hệ Giáo viên (Teacher)', '18'],
    ['Hình 2.4', 'Sơ đồ phân rã chức năng phân hệ Học sinh và Phụ huynh (Student/Parent)', '18'],
    ['Hình 2.5', 'Lược đồ Use Case tổng thể toàn hệ thống IC3 Quest', '19'],
    ['Hình 2.6', 'Lược đồ hoạt động UC01: Đăng nhập phân quyền', '30'],
    ['Hình 2.7', 'Lược đồ hoạt động UC03: Soạn thảo câu hỏi trong Question Studio', '32'],
    ['Hình 2.8', 'Lược đồ hoạt động UC04: Import trích xuất tự động 509 câu hỏi iSpring', '34'],
    ['Hình 2.9', 'Lược đồ hoạt động UC06: Học sinh tham gia phòng thi ảo và làm bài', '36'],
    ['Hình 2.10', 'Lược đồ hoạt động UC07: Chấm điểm tự động và tính thưởng Sao', '38'],
    ['Hình 2.11', 'Sơ đồ thực thể mối quan hệ cơ sở dữ liệu (ERD) 12 bảng MySQL', '40'],
    ['Hình 3.1', 'Giao diện Đăng nhập phân quyền hệ thống (Auth Login)', '55'],
    ['Hình 3.2', 'Giao diện Bảng điều khiển Quản trị viên (Admin Dashboard)', '56'],
    ['Hình 3.3', 'Giao diện Quản lý Người dùng, Phân quyền Giáo viên và Lớp học', '58'],
    ['Hình 3.4', 'Giao diện IC3 Question Studio - Ngân hàng 509 câu hỏi', '60'],
    ['Hình 3.5', 'Giao diện Soạn thảo & Biên tập chi tiết câu hỏi (Question Editor)', '62'],
    ['Hình 3.6', 'Giao diện Cổng học sinh - Khối lớp & Bộ đề luyện thi', '63'],
    ['Hình 3.7', 'Giao diện Phòng thi ảo tương tác (Native IC3 Quiz Player)', '65'],
    ['Hình 3.8', 'Giao diện Bảng xếp hạng thi đua và Lịch sử làm bài thi', '66'],
    ['Hình 3.9', 'Giao diện Khu trò chơi giải trí và Đổi Sao tích lũy', '67'],
    ['Hình 3.10', 'Giao diện Bảng điều khiển Phụ huynh giám sát tiến độ (Parent Dashboard)', '68'],
];
$gen->addTable(['KÍ HIỆU', 'TÊN SƠ ĐỒ / LƯỢC ĐỒ / HÌNH', 'TRANG'], $figures, [1400, 6471, 1200]);
$gen->addPageBreak();

// DANH MỤC BẢNG
$gen->addHeading1("DANH MỤC BẢNG");
$tablesList = [
    ['Bảng 1.1', 'So sánh đối chiếu giữa IC3 Quest và các phần mềm thi trực tuyến hiện nay', '6'],
    ['Bảng 2.1', 'Đặc tả chi tiết Use Case UC01: Đăng nhập vào hệ thống', '20'],
    ['Bảng 2.2', 'Đặc tả chi tiết Use Case UC02: Quản lý người dùng và lớp học', '21'],
    ['Bảng 2.3', 'Đặc tả chi tiết Use Case UC03: Quản trị và soạn thảo câu hỏi trong Studio', '22'],
    ['Bảng 2.4', 'Đặc tả chi tiết Use Case UC04: Import tự động ngân hàng câu hỏi iSpring', '23'],
    ['Bảng 2.5', 'Đặc tả chi tiết Use Case UC05: Quản lý bộ đề thi theo khối lớp', '24'],
    ['Bảng 2.6', 'Đặc tả chi tiết Use Case UC06: Học sinh làm bài thi trắc nghiệm', '25'],
    ['Bảng 2.7', 'Đặc tả chi tiết Use Case UC07: Tự động chấm điểm và ghi nhận kết quả thi', '26'],
    ['Bảng 2.8', 'Đặc tả chi tiết Use Case UC08: Đổi sao thưởng lấy thời gian chơi game', '27'],
    ['Bảng 2.9', 'Đặc tả chi tiết Use Case UC09: Phụ huynh tra cứu lịch sử và nhận cảnh báo', '28'],
    ['Bảng 2.10', 'Đặc tả chi tiết Use Case UC10: Xuất báo cáo thống kê thi cử ra CSV', '29'],
    ['Bảng 2.11', 'Đặc tả cấu trúc bảng dữ liệu `users`', '41'],
    ['Bảng 2.12', 'Đặc tả cấu trúc bảng dữ liệu `classrooms`', '42'],
    ['Bảng 2.13', 'Đặc tả cấu trúc bảng dữ liệu `programs`', '43'],
    ['Bảng 2.14', 'Đặc tả cấu trúc bảng dữ liệu `levels`', '44'],
    ['Bảng 2.15', 'Đặc tả cấu trúc bảng dữ liệu `topics`', '45'],
    ['Bảng 2.16', 'Đặc tả cấu trúc bảng dữ liệu `practice_tests`', '46'],
    ['Bảng 2.17', 'Đặc tả cấu trúc bảng dữ liệu `questions`', '47'],
    ['Bảng 2.18', 'Đặc tả cấu trúc bảng dữ liệu `question_options`', '48'],
    ['Bảng 2.19', 'Đặc tả cấu trúc bảng dữ liệu `question_assets`', '49'],
    ['Bảng 2.20', 'Đặc tả cấu trúc bảng dữ liệu `test_attempts`', '50'],
    ['Bảng 2.21', 'Đặc tả cấu trúc bảng dữ liệu `game_settings`', '51'],
    ['Bảng 2.22', 'Đặc tả cấu trúc bảng dữ liệu `game_transactions`', '52'],
    ['Bảng 3.1', 'Bảng cấu hình thông số môi trường cài đặt phần mềm', '53'],
    ['Bảng 3.2', 'Bảng ma trận kịch bản kiểm thử chất lượng 10 Test Cases', '74'],
];
$gen->addTable(['KÍ HIỆU', 'TÊN BẢNG BIỂU ĐẶC TẢ', 'TRANG'], $tablesList, [1400, 6471, 1200]);
$gen->addPageBreak();

// TỪ VIẾT TẮT
$gen->addHeading1("KÍ HIỆU CÁC CỤM TỪ VIẾT TẮT");
$acronyms = [
    ['CNTT', 'Công nghệ Thông tin'],
    ['CSDL', 'Cơ sở dữ liệu (Database)'],
    ['GVHD', 'Giảng viên hướng dẫn'],
    ['GVPB', 'Giảng viên phản biện'],
    ['SVTH', 'Sinh viên thực hiện'],
    ['IC3', 'Internet and Computing Core Certification (Chứng chỉ Tin học Quốc tế)'],
    ['MOS', 'Microsoft Office Specialist (Chứng chỉ Tin học Văn phòng Quốc tế)'],
    ['MVC', 'Model – View – Controller (Mô hình kiến trúc phần mềm)'],
    ['ORM', 'Object Relational Mapping (Ánh xạ cơ sở dữ liệu quan hệ đối tượng)'],
    ['HTML5', 'HyperText Markup Language version 5 (Ngôn ngữ đánh dấu siêu văn bản)'],
    ['CSS3', 'Cascading Style Sheets version 3 (Ngôn ngữ định kiểu tài liệu)'],
    ['JS', 'JavaScript (Ngôn ngữ lập trình kịch bản phía Client)'],
    ['CLI', 'Command Line Interface (Giao diện dòng lệnh - Artisan Console)'],
    ['JSON', 'JavaScript Object Notation (Định dạng trao đổi dữ liệu)'],
    ['CSV', 'Comma-Separated Values (Định dạng tệp dữ liệu phân tách dấu phẩy)'],
    ['UI/UX', 'User Interface / User Experience (Giao diện và Trải nghiệm người dùng)'],
    ['ERD', 'Entity Relationship Diagram (Sơ đồ thực thể mối quan hệ)'],
    ['UML', 'Unified Modeling Language (Ngôn ngữ mô hình hóa thống nhất)'],
];
$gen->addTable(['VIẾT TẮT', 'Ý NGHĨA ĐẦY ĐỦ'], $acronyms, [2200, 6871]);
$gen->addPageBreak();

// -------------------------------------------------------------
// 9. LỜI MỞ ĐẦU
// -------------------------------------------------------------
$gen->addHeading1("LỜI MỞ ĐẦU");
$gen->addParagraph("Trong bối cảnh cuộc Cách mạng Công nghiệp 4.0 và tiến trình chuyển đổi số quốc gia đang diễn ra sâu rộng trên mọi lĩnh vực, ngành Giáo dục và Đào tạo xác định mục tiêu trọng tâm là trang bị năng lực số cho thế hệ trẻ ngay từ bậc học tiểu học. Tin học không còn đơn thuần là một môn học tự chọn kỹ năng máy tính, mà đã trở thành năng lực công dân số cốt lõi giúp học sinh tự tin tiếp cận tri thức khoa học toàn cầu.", 'both', 26, false, false, '000000', 80, 80, 720);

$gen->addParagraph("Hệ thống các chứng chỉ tin học quốc tế chuẩn hóa như IC3 Spark (Internet and Computing Core Certification dành cho học sinh Tiểu học khối 3, 4, 5) và MOS (Microsoft Office Specialist) được đánh giá là những thước đo chuẩn mực quốc tế uy tín nhất hiện nay. Tuy nhiên, việc giảng dạy và tổ chức ôn luyện tại các trường học hiện nay đang đối mặt với nhiều rào cản lớn: thiếu thốn phòng máy thực hành đạt chuẩn bản quyền, dữ liệu câu hỏi ôn tập bị phân tán, đóng gói trong các tệp tin iSpring tĩnh cồng kềnh, không hỗ trợ theo dõi tiến trình và phụ huynh hoàn toàn bị cô lập khỏi quá trình học tập của con em.", 'both', 26, false, false, '000000', 80, 80, 720);

$gen->addParagraph("Xuất phát từ yêu cầu thực tiễn cấp thiết trên, nhóm sinh viên chúng em đã lựa chọn đề tài: “Xây dựng hệ thống học tập và luyện thi chứng chỉ tin học quốc tế IC3 Spark & MOS trực tuyến (IC3 Quest)” làm đồ án môn học chuyên đề PHP. Đề tài nhằm nghiên cứu toàn diện quy trình tổ chức ôn luyện trắc nghiệm tin học chuẩn quốc tế, ứng dụng các công nghệ hiện đại nhất của hệ sinh thái PHP 8.3 và Laravel Framework 11/13 để số hóa toàn bộ 509 câu hỏi và 35 bộ đề thi iSpring vào cơ sở dữ liệu quan hệ MySQL độc lập, đồng thời phát triển cơ chế thi tương tác thời gian thực, cơ chế Gamification thưởng Sao đổi quà và phân hệ báo cáo phụ huynh minh bạch.", 'both', 26, false, false, '000000', 80, 80, 720);

$gen->addParagraph("Bố cục của báo cáo đồ án được kết cấu chặt chẽ thành 4 chương chính:", 'both', 26, false, false, '000000', 80, 40, 720);
$gen->addBullet("Chương 1: Tổng quan tài liệu – Khảo sát thực trạng, đặt vấn đề, xác định mục tiêu và trình bày cơ sở lý thuyết các công nghệ sử dụng.");
$gen->addBullet("Chương 2: Phương pháp thực hiện – Phân tích quy trình nghiệp vụ 5 bước, các lược đồ thiết kế UML (Use Case, Activity) và thiết kế chi tiết cơ sở dữ liệu quan hệ 12 bảng.");
$gen->addBullet("Chương 3: Cài đặt thực nghiệm và kết quả – Mô tả chi tiết môi trường cài đặt, phân tích 10 giao diện chính, giải thích 3 đoạn mã nguồn thuật toán cốt lõi và đánh giá qua 10 kịch bản kiểm thử.");
$gen->addBullet("Chương 4: Kết luận và hướng phát triển – Đánh giá các kết quả đạt được, kỹ năng tích lũy, các mặt hạn chế và vạch ra lộ trình mở rộng hệ thống.");
$gen->addPageBreak();

// -------------------------------------------------------------
// CHƯƠNG 1. TỔNG QUAN TÀI LIỆU
// -------------------------------------------------------------
$gen->addHeading1("Chương 1. TỔNG QUAN TÀI LIỆU");

$gen->addHeading2("1.1. Khảo sát bài toán thực tế");

$gen->addHeading3("1.1.1. Thực trạng đào tạo, luyện thi chứng chỉ tin học quốc tế IC3/MOS");
$gen->addParagraph("Tại các trường tiểu học và trung học cơ sở trên địa bàn TP. Hồ Chí Minh cũng như cả nước, nhu cầu cho học sinh tham gia học tập và thi lấy chứng chỉ tin học quốc tế IC3 Spark (khối 3, 4, 5) tăng trưởng mạnh mẽ qua từng năm học. Đây là điều kiện xét tuyển ưu tiên đầu cấp và là tiêu chí đánh giá năng lực số của các trường chuẩn quốc tế. Tuy nhiên, thực trạng tổ chức ôn luyện hiện nay bộc lộ nhiều điểm nghẽn nghiêm trọng:", 'both', 26, false, false, '000000', 60, 60, 720);

$gen->addBullet("Dữ liệu phân tán và đóng gói cô lập: Các trung tâm và giáo viên thường sử dụng bộ đề soạn sẵn từ phần mềm iSpring QuizMaker được xuất bản ra định dạng HTML5. Toàn bộ mã nguồn và dữ liệu câu hỏi bị nhúng cứng (hard-coded) vào các file JavaScript nặng nề, mỗi bài thi là một thư mục riêng biệt với hàng trăm file media phân mảnh. Khi cần chỉnh sửa một câu hỏi, giáo viên buộc phải mở lại file nguồn iSpring gốc để biên dịch lại toàn bộ gói đề.");
$gen->addBullet("Hạ tầng lưu trữ bị lãng phí: Một gói đề thi HTML5 đóng gói trung bình chiếm từ 2 MB đến 5 MB dung lượng máy chủ, khiến thư mục lưu trữ phình to hàng trăm Megabyte chứa đầy các thư mục trùng lặp thư viện JS runtime, gây quá tải cho thư mục web public.");
$gen->addBullet("Thiếu vắng tính năng giám sát và lưu vết: Các bài trắc nghiệm HTML5 độc lập chỉ hiển thị kết quả tại máy học sinh khi kết thúc phiên thi. Nhà trường, giáo viên không lưu trữ được lịch sử làm bài, không có bảng xếp hạng phân loại học lực và phụ huynh hoàn toàn không biết con mình đã làm bao nhiêu bài thi, đạt điểm số ra sao.");
$gen->addBullet("Tâm lý nhàm chán của học sinh lứa tuổi tiểu học: Việc làm các bài trắc nghiệm khô khan lặp đi lặp lại khiến học sinh tiểu học dễ mất tập trung, không có động lực thi đua và thiếu tính gắn kết học đường.");

$gen->addHeading3("1.1.2. Đề xuất giải pháp xây dựng hệ thống trực tuyến IC3 Quest");
$gen->addParagraph("Trước thực trạng trên, nhóm nghiên cứu đề xuất giải pháp xây dựng hệ sinh thái phần mềm luyện thi trực tuyến tập trung mang tên IC3 Quest với các định hướng kiến trúc đột phá:", 'both', 26, false, false, '000000', 60, 60, 720);
$gen->addBullet("Số hóa và trích xuất độc lập ngân hàng câu hỏi: Xây dựng cơ chế trích xuất tự động toàn bộ câu hỏi, đáp án, cấu hình điểm và hình ảnh từ kho lưu trữ zip iSpring HTML5 gốc (`storage/legacy_backup/legacy_ic3_source.zip`) vào cơ sở dữ liệu quan hệ MySQL chuẩn mực, giải phóng hoàn toàn dung lượng lưu trữ của thư mục public.");
$gen->addBullet("Xây dựng phòng thi ảo tương tác (Native IC3 Quiz Player): Thay vì nhúng iframe iSpring nặng nề, hệ thống tự phát triển trình hiển thị câu hỏi trắc nghiệm hiện đại bằng Blade/JS, tích hợp đồng hồ đếm ngược, thanh tiến trình, hiển thị ảnh trực quan các thao tác trên hệ điều hành Windows, Word, Excel.");
$gen->addBullet("Ứng dụng mô hình Gamification kích thích tự giác học tập: Tích hợp cơ chế điểm thưởng Sao (`reward_stars`) cho mỗi bài luyện hoàn thành đạt điểm chuẩn, cho phép học sinh dùng Sao để đổi thời gian chơi minigame giáo dục trong Khu trò chơi (Game Zone) với tỷ lệ quy đổi do Quản trị viên cấu hình.");
$gen->addBullet("Minh bạch hóa kết quả với Bảng điều khiển Phụ huynh: Thiết lập phân hệ Parent Dashboard giúp phụ huynh tra cứu điểm số, thời gian làm bài thực tế, số bài thi đạt/hỏng và nhận cảnh báo sớm để phối hợp với giáo viên chủ nhiệm kèm cặp học sinh.");

$gen->addHeading3("1.1.3. Mục tiêu cụ thể và phạm vi của đề tài");
$gen->addParagraph("Mục tiêu tổng quát của đề tài là xây dựng hoàn thiện một ứng dụng web luyện thi chứng chỉ tin học quốc tế IC3 Spark & MOS đạt chuẩn chuyên nghiệp, đáp ứng tốt cho nhà trường, giáo viên, học sinh và phụ huynh:", 'both', 26, false, false, '000000', 60, 60, 720);
$gen->addBullet("Về quy mô dữ liệu: Quản lý tối thiểu 500 câu hỏi chuẩn hóa (thực tế hệ thống đã trích xuất hoàn tất 509 câu hỏi và 35 bộ đề thi) phân bổ đầy đủ cho 3 khối lớp tiểu học (Khối 3, 4, 5) theo 21 chủ đề chuẩn IC3 GS6.");
$gen->addBullet("Về mặt chức năng: Cung cấp đầy đủ 4 phân hệ tương tác gồm Quản trị viên (Admin Studio), Giáo viên bộ môn (Teacher Portal), Học sinh luyện thi (Student Portal) và Phụ huynh theo dõi (Parent Dashboard).");
$gen->addBullet("Về hiệu năng và bảo mật: Thời gian tải trang dưới 1.5 giây, bảo vệ dữ liệu bằng Middleware phân quyền nhiều cấp, mật khẩu mã hóa Bcrypt 12 vòng, phòng chống các lỗ hổng phổ biến như SQL Injection và CSRF.");

$gen->addHeading3("1.1.4. Khảo sát và so sánh các hệ thống thi trắc nghiệm hiện nay");
$gen->addParagraph("Nhóm đã tiến hành khảo sát, phân tích ưu nhược điểm của các hệ thống thi trắc nghiệm phổ biến trên thị trường để đúc kết điểm mạnh áp dụng cho IC3 Quest:", 'both', 26, false, false, '000000', 60, 60, 720);

$gen->addTable(
    ['TIÊU CHÍ SO SÁNH', 'AZOTA / SUBSTUDY', 'GÓI ĐỀ iSPRING TĨNH', 'HỆ THỐNG IC3 QUEST (ĐỒ ÁN)'],
    [
        ['Nền tảng kiến trúc', 'SaaS đa người dùng đám mây', 'Các tệp HTML5 phân tán cục bộ', 'Hệ thống web tập trung (Laravel MVC)'],
        ['Ngân hàng câu hỏi IC3', 'Chưa chuyên biệt hóa chuẩn IC3', 'Có nội dung IC3 nhưng bị nhúng cứng file', '509 câu hỏi chuẩn hóa trong CSDL MySQL'],
        ['Trình làm bài thi ảo', 'Trắc nghiệm thông thường', 'Giao diện iSpring flash/HTML5 cũ', 'Native Quiz Player hiện đại, tối ưu di động'],
        ['Quản lý dữ liệu tập trung', 'Có, lưu trên server nhà cung cấp', 'Không, lưu rời rạc từng máy', 'Có, quản trị toàn diện trong MySQL cục bộ'],
        ['Cơ chế Gamification đổi thưởng', 'Không hỗ trợ hoặc rất hạn chế', 'Hoàn toàn không có', 'Thưởng Sao, đổi phút chơi Minigame giáo dục'],
        ['Phân hệ giám sát của Phụ huynh', 'Có trên ứng dụng di động', 'Không có', 'Parent Dashboard chuyên biệt, cảnh báo hỏng thi'],
        ['Khả năng tùy biến và mở rộng', 'Không có mã nguồn (mã nguồn đóng)', 'Phải có file nguồn .quiz iSpring', 'Toàn quyền làm chủ mã nguồn PHP/Laravel'],
    ],
    [2200, 2200, 2200, 2471]
);

$gen->addHeading2("1.2. Cơ sở lý thuyết và công nghệ áp dụng");

$gen->addHeading3("1.2.1. Ngôn ngữ lập trình PHP 8.3");
$gen->addParagraph("PHP (Hypertext Preprocessor) là ngôn ngữ kịch bản phía máy chủ (server-side) phổ biến nhất thế giới cho phát triển ứng dụng web, chiếm hơn 77% thị phần các website có xử lý mã nguồn backend. Phiên bản PHP 8.3 được nhóm lựa chọn sử dụng trong đồ án mang lại hàng loạt cải tiến vượt bậc:", 'both', 26, false, false, '000000', 60, 60, 720);
$gen->addBullet("JIT Compiler (Just-In-Time) nâng cao: Tối ưu hóa tốc độ thực thi các thuật toán xử lý dữ liệu mảng, trích xuất tệp nén và xử lý JSON nhanh hơn đáng kể so với các phiên bản PHP 7.x.");
$gen->addBullet("Typed Class Constants và Readonly Classes: Cho phép định kiểu tường minh cho hằng số lớp và các lớp bất biến, gia tăng tính chặt chẽ và an toàn dữ liệu hướng đối tượng (OOP).");
$gen->addBullet("Cải tiến kiểm tra kiểu động (Type Safety): Hỗ trợ Union Types, Intersection Types giúp bắt lỗi ngay tại thời điểm biên dịch logic xử lý.");

$gen->addHeading3("1.2.2. Framework phát triển Laravel 11/13");
$gen->addParagraph("Laravel là PHP Framework mã nguồn mở hàng đầu hiện nay, xây dựng theo mô hình MVC (Model - View - Controller). Laravel mang đến hệ sinh thái phát triển thanh lịch, an toàn và có khả năng mở rộng cao:", 'both', 26, false, false, '000000', 60, 60, 720);
$gen->addBullet("Eloquent ORM (Object-Relational Mapping): Cung cấp cú pháp hướng đối tượng trực quan để thao tác với cơ sở dữ liệu MySQL thông qua các Model (`User`, `PracticeTest`, `Question`, `Classroom`...). Toàn bộ câu truy vấn đều được tự động tham số hóa (Prepared Statements) giúp triệt tiêu hoàn toàn nguy cơ SQL Injection.");
$gen->addBullet("Blade Template Engine: Công cụ dựng giao diện phía máy chủ mạnh mẽ, hỗ trợ kế thừa layout (`@extends`, `@section`), nhúng component tái sử dụng và kiểm soát logic điều kiện mà không làm suy giảm tốc độ kết xuất trang.");
$gen->addBullet("Artisan CLI Console: Hệ thống dòng lệnh cho phép tạo Migration, Seeder, Controller và đặc biệt là hỗ trợ xây dựng các lệnh tùy biến (Custom Console Commands) như `php artisan ic3:import-questions` để tự động hóa nghiệp vụ trích xuất câu hỏi nền tảng.");
$gen->addBullet("Middleware & Authentication: Cơ chế kiểm soát luồng truy cập chặt chẽ (`guest`, `auth`, `admin`, `student`), giúp bảo vệ tuyệt đối các URL nội bộ.");

$gen->addHeading3("1.2.3. Hệ quản trị cơ sở dữ liệu MySQL 8.x");
$gen->addParagraph("MySQL là hệ quản trị cơ sở dữ liệu quan hệ (RDBMS) mạnh mẽ, tin cậy và tương thích hoàn hảo với Laravel Framework thông qua PDO driver. Trong đồ án IC3 Quest, MySQL đảm bảo toàn vẹn dữ liệu thông qua các ràng buộc khóa ngoại (Foreign Key Constraints), hỗ trợ kiểu dữ liệu `JSON` để lưu trữ linh hoạt các cấu hình tương tác của câu hỏi và chỉ mục tối ưu hóa tốc độ truy vấn lịch sử thi cử.", 'both', 26, false, false, '000000', 60, 60, 720);

$gen->addHeading3("1.2.4. Công nghệ Frontend (HTML5, TailwindCSS, JavaScript ES6)");
$gen->addParagraph("Giao diện người dùng của IC3 Quest được xây dựng hướng đến đối tượng chính là học sinh tiểu học và giáo viên, đòi hỏi tính sinh động, trực quan và tốc độ phản hồi tức thì:", 'both', 26, false, false, '000000', 60, 60, 720);
$gen->addBullet("HTML5 Semantic: Cấu trúc trang web theo chuẩn ngữ nghĩa (`header`, `main`, `section`, `article`), tăng khả năng tiếp cận và tối ưu hóa SEO.");
$gen->addBullet("TailwindCSS & Vanilla CSS: Framework CSS theo triết lý Utility-First kết hợp bảng màu sắc hiện đại (vibrant colors), hỗ trợ thiết kế Responsive đa kích thước màn hình từ máy tính để bàn phòng lab đến máy tính bảng cá nhân.");
$gen->addBullet("JavaScript ES6+: Xử lý đồng hồ đếm ngược phòng thi ảo (Timer Countdown), tự động khóa bài khi hết giờ, chuyển đổi trạng thái câu hỏi (Next/Prev) và gửi kết quả bài thi qua cơ chế nộp bài tự động.");

$gen->addHeading3("1.2.5. Môi trường máy chủ cục bộ Laragon & Visual Studio Code");
$gen->addParagraph("Toàn bộ quy trình phát triển và kiểm thử đồ án được vận hành trên máy chủ Laragon – giải pháp môi trường web development cô lập hàng đầu dành cho Windows, tích hợp Apache 2.4, PHP 8.3, MySQL 8.0, Composer và NodeJS. Công cụ soạn thảo mã nguồn chính là Visual Studio Code với các extension hỗ trợ chuyên sâu cho Laravel (PHP Intelephense, Laravel Blade Snippets, Tailwind CSS IntelliSense).", 'both', 26, false, false, '000000', 60, 60, 720);
$gen->addPageBreak();

// -------------------------------------------------------------
// CHƯƠNG 2. PHƯƠNG PHÁP THỰC HIỆN
// -------------------------------------------------------------
$gen->addHeading1("Chương 2. PHƯƠNG PHÁP THỰC HIỆN (PHÂN TÍCH VÀ THIẾT KẾ HỆ THỐNG)");

$gen->addHeading2("2.1. Phân tích hệ thống nghiệp vụ");

$gen->addHeading3("2.1.1. Quy trình nghiệp vụ 5 bước cốt lõi của IC3 Quest");
$gen->addParagraph("Hệ thống luyện thi IC3 Quest vận hành khép kín và liên kết chặt chẽ giữa 4 nhóm đối tượng (Admin, Giáo viên, Học sinh, Phụ huynh) thông qua chuỗi quy trình nghiệp vụ 5 bước chuẩn hóa:", 'both', 26, false, false, '000000', 60, 60, 720);

$gen->addBullet("Bước 1: Thiết lập cấu trúc học tập và Quản trị người dùng (Admin)
Quản trị viên tối cao đăng nhập hệ thống, thực hiện tạo Chương trình (`programs`), các Khối lớp (`levels` - Khối 3, 4, 5), cấu hình Chủ đề kiến thức (`topics`) theo chuẩn khung năng lực IC3 GS6. Admin tạo và cấp tài khoản cho Giáo viên bộ môn, phân quyền giáo viên phụ trách từng khối lớp cụ thể (bảng `teacher_level`), khởi tạo danh sách Lớp học (`classrooms`) và cấu hình tỷ lệ đổi thưởng Sao trong bảng `game_settings`.");

$gen->addBullet("Bước 2: Xây dựng và Trích xuất Ngân hàng câu hỏi IC3 (Admin / Giáo viên)
Hệ thống cho phép nạp tài nguyên câu hỏi bằng 2 phương thức linh hoạt:
- Cơ chế tự động trích xuất gói đề iSpring HTML5: Chạy lệnh `php artisan ic3:import-questions` để hệ thống tự động giải nén `storage/legacy_backup/legacy_ic3_source.zip`, phân tích cú pháp manifest và cấu trúc câu hỏi, đưa toàn bộ 509 câu hỏi và ảnh minh họa vào bảng `questions`, `question_options`, `question_assets` trong MySQL.
- Cơ chế thủ công tại IC3 Question Studio: Giáo viên và Admin truy cập giao diện Studio để soạn thảo câu hỏi mới, cấu hình loại câu trắc nghiệm (1 đáp án, nhiều đáp án), tải ảnh minh họa giao diện Windows/Word, nhập giải thích đáp án và thiết lập cơ chế đảo ngẫu nhiên câu hỏi / đảo đáp án.");

$gen->addBullet("Bước 3: Lựa chọn lộ trình học và Tham gia phòng thi ảo (Học sinh)
Học sinh sử dụng mã học sinh hoặc email được cấp để đăng nhập vào phân hệ Student Portal. Học sinh chọn khối lớp mình đang học (Khối 3, Khối 4 hoặc Khối 5) và duyệt danh sách 35 bài luyện thi. Khi chọn bài luyện thi, hệ thống hiển thị thông tin bài thi (thời gian làm bài, số lượng câu, điểm chuẩn đạt) và cấp quyền khởi chạy Phòng thi ảo (Native IC3 Quiz Player). Khi vào phòng thi, hệ thống kích hoạt thuật toán xáo trộn câu hỏi và đáp án ngẫu nhiên, khởi động đồng hồ đếm ngược JavaScript.");

$gen->addBullet("Bước 4: Chấm điểm tự động, Ghi nhận kết quả và Thưởng Sao (Hệ thống & Học sinh)
Học sinh trả lời các câu hỏi và bấm nút “Nộp bài” (hoặc hệ thống tự động nộp bài khi đồng hồ đếm ngược chạm mốc 0 giây). Controller của Laravel (`AttemptController`) tiếp nhận dữ liệu bài làm, đối soát với đáp án chuẩn trong CSDL, tính toán điểm số chính xác và xác định trạng thái Đạt (Pass) hay Chưa đạt (Fail). Nếu học sinh đạt điểm chuẩn, hệ thống tự động cộng điểm thưởng Sao (`reward_stars`) vào tài khoản học sinh và hiển thị trang tổng kết kết quả trực quan kèm biểu đồ phân tích.");

$gen->addBullet("Bước 5: Đổi thưởng Minigame, Thống kê và Giám sát tiến độ (Học sinh, Phụ huynh, Admin)
- Học sinh có thể sử dụng Sao tích lũy được từ các bài thi điểm cao để truy cập Khu trò chơi (Game Zone), đổi lấy thời gian chơi các minigame giáo dục rèn luyện phản xạ và tư duy.
- Phụ huynh truy cập Bảng điều khiển Phụ huynh (Parent Dashboard) để xem biểu đồ kết quả bài thi của con, theo dõi số phút chơi game và nhận cảnh báo nếu con thi trượt nhiều lần.
- Admin và Giáo viên xem biểu đồ phân phối điểm số trên Dashboard, theo dõi tỷ lệ hoàn thành đề thi và bấm nút “Xuất báo cáo” để tải về file CSV thống kê toàn diện điểm số học sinh.");

$gen->addHeading3("2.1.2. Yêu cầu chức năng chi tiết theo từng vai trò");
$gen->addParagraph("Hệ thống đáp ứng các nhóm yêu cầu chức năng nghiệp vụ cụ thể cho từng đối tượng người dùng:", 'both', 26, false, false, '000000', 60, 60, 720);
$gen->addBullet("Phân hệ Quản trị viên (Admin): Đăng nhập/đăng xuất hệ thống; Xem Dashboard thống kê tổng quan (số học sinh, số đề thi, số lượt thi, biểu đồ điểm); Quản lý người dùng (thêm, sửa, xóa, khóa tài khoản Admin, Giáo viên, Học sinh); Quản lý Lớp học và phân công giáo viên chủ nhiệm; Quản lý Chương trình học (`programs`) và Khối lớp (`levels`); Sử dụng IC3 Question Studio để quản lý danh mục 509 câu hỏi và 35 bộ đề; Soạn thảo, chỉnh sửa câu hỏi chi tiết; Cấu hình hệ số đổi Sao và thời gian chơi game; Xuất báo cáo điểm thi toàn trường ra định dạng file CSV.");
$gen->addBullet("Phân hệ Giáo viên (Teacher): Đăng nhập hệ thống theo quyền giáo viên; Quản lý danh sách học sinh thuộc lớp học mình phụ trách; Phụ trách bộ đề và ngân hàng câu hỏi thuộc khối lớp được phân quyền; Theo dõi tiến độ làm bài thi của từng học sinh trong lớp; Tra cứu lịch sử làm bài và thống kê tỷ lệ đạt/chưa đạt của lớp.");
$gen->addBullet("Phân hệ Học sinh (Student): Đăng nhập bằng mã học sinh hoặc email; Xem tổng quan thành tích cá nhân, số bài đã làm, tổng số Sao thưởng tích lũy; Chọn khối lớp và duyệt danh mục các bài luyện thi IC3 GS6; Làm bài thi trắc nghiệm trong phòng thi ảo tương tác; Xem kết quả điểm số và đáp án giải thích ngay sau khi nộp bài; Xem bảng xếp hạng thi đua toàn trường; Đổi sao thưởng lấy thời gian chơi minigame giáo dục trong Khu trò chơi.");
$gen->addBullet("Phân hệ Phụ huynh (Parent): Tra cứu kết quả học tập của con em; Xem bảng lịch sử chi tiết các lần thi (ngày thi, đề thi, thời gian làm bài, điểm số, kết quả Đạt/Không đạt); Xem thống kê số dư Sao thưởng và thời gian con chơi game; Nhận các thông báo nhắc nhở khi học sinh có dấu hiệu thi trượt nhiều bài liên tiếp.");

$gen->addHeading2("2.2. Các biểu đồ thiết kế hệ thống (UML Diagrams)");

$gen->addHeading3("2.2.1. Lược đồ Use Case tổng thể toàn hệ thống");
$gen->addParagraph("Hệ thống bao gồm 4 tác nhân chính tương tác với các nhóm chức năng thông qua lược đồ Use Case tổng thể: Quản trị viên (Admin), Giáo viên (Teacher), Học sinh (Student) và Phụ huynh (Parent). Admin có toàn quyền điều hành hệ thống và cấu hình ngân hàng câu hỏi. Giáo viên quản lý lớp và khối lớp phụ trách. Học sinh tham gia thi và giải trí. Phụ huynh giám sát tiến trình học tập.", 'both', 26, false, false, '000000', 60, 60, 720);

$gen->addHeading3("2.2.2. Bảng đặc tả chi tiết 10 Use Cases nghiệp vụ trọng tâm");

// UC01
$gen->addParagraph("Bảng 2.1: Đặc tả chi tiết Use Case UC01: Đăng nhập vào hệ thống", 'left', 24, true, false, '002060', 60, 40, 0);
$gen->addTable(
    ['THUỘC TÍNH', 'NỘI DUNG ĐẶC TẢ'],
    [
        ['Tên Use Case', 'UC01: Đăng nhập vào hệ thống (Authentication Login)'],
        ['Tác nhân (Actors)', 'Quản trị viên (Admin), Giáo viên (Teacher), Học sinh (Student)'],
        ['Mô tả tóm tắt', 'Cho phép người dùng xác thực danh tính bằng Email / Mã học sinh và Mật khẩu để truy cập vào phân hệ tương ứng với vai trò của mình.'],
        ['Điều kiện tiên quyết', 'Người dùng đã được cấp tài khoản hợp lệ trong hệ thống (trạng thái active).'],
        ['Điều kiện sau', 'Hệ thống khởi tạo Session đăng nhập và chuyển hướng người dùng đến đúng trang Dashboard tương ứng với vai trò (Admin -> /quan-tri, Student -> /).'],
        ['Luồng sự kiện chính', "1. Người dùng truy cập đường dẫn /dang-nhap.\n2. Hệ thống hiển thị form nhập Email/Mã học sinh và Mật khẩu.\n3. Người dùng nhập thông tin đăng nhập và bấm 'Đăng nhập'.\n4. Hệ thống kiểm tra tính hợp lệ dữ liệu (Form Request Validation).\n5. Hệ thống xác thực thông tin với CSDL MySQL qua hàm Auth::attempt().\n6. Hệ thống tạo session an toàn, cập nhật thời gian đăng nhập và chuyển hướng người dùng về trang chủ theo vai trò."],
        ['Luồng sự kiện rẽ nhánh', "4a. Nếu trường email hoặc mật khẩu bị bỏ trống: Hệ thống hiển thị thông báo lỗi yêu cầu điền đầy đủ.\n5a. Nếu thông tin tài khoản hoặc mật khẩu không chính xác: Hệ thống báo lỗi 'Thông tin đăng nhập không hợp lệ' và giữ lại email đã nhập.\n5b. Nếu tài khoản bị vô hiệu hóa (status = inactive): Hệ thống báo lỗi tài khoản bị khóa."],
    ],
    [2600, 6471]
);

// UC02
$gen->addParagraph("Bảng 2.2: Đặc tả chi tiết Use Case UC02: Quản lý người dùng và lớp học", 'left', 24, true, false, '002060', 60, 40, 0);
$gen->addTable(
    ['THUỘC TÍNH', 'NỘI DUNG ĐẶC TẢ'],
    [
        ['Tên Use Case', 'UC02: Quản lý người dùng và lớp học (User & Classroom Management)'],
        ['Tác nhân (Actors)', 'Quản trị viên (Admin)'],
        ['Mô tả tóm tắt', 'Admin thực hiện tạo mới, cập nhật thông tin, kích hoạt hoặc khóa tài khoản giáo viên/học sinh, phân chia lớp học và gán quyền phụ trách khối lớp.'],
        ['Điều kiện tiên quyết', 'Admin đã đăng nhập thành công vào hệ thống và có quyền quản trị tối cao.'],
        ['Điều kiện sau', 'Thông tin người dùng và phân bổ lớp học được cập nhật thành công trong MySQL.'],
        ['Luồng sự kiện chính', "1. Admin truy cập mục 'Quản lý' (/quan-tri/quan-ly).\n2. Hệ thống hiển thị danh sách người dùng, lớp học và khối lớp.\n3. Admin chọn thêm mới hoặc chỉnh sửa tài khoản/lớp học.\n4. Admin điền thông tin (Tên, Email, Vai trò, Lớp học, Phân quyền khối) và bấm 'Lưu'.\n5. Hệ thống xác thực dữ liệu và thực hiện ghi vào bảng `users`, `classrooms`, `teacher_level`.\n6. Hệ thống hiển thị thông báo thao tác thành công và cập nhật lại bảng danh sách."],
        ['Luồng sự kiện rẽ nhánh', "4a. Email hoặc Mã học sinh bị trùng lặp: Hệ thống thông báo lỗi trùng dữ liệu duy nhất (Unique constraint).\n4b. Mật khẩu ngắn hơn 6 ký tự: Hệ thống yêu cầu nhập mật khẩu bảo mật hơn."],
    ],
    [2600, 6471]
);

// UC03
$gen->addParagraph("Bảng 2.3: Đặc tả chi tiết Use Case UC03: Quản trị và soạn thảo câu hỏi trong Studio", 'left', 24, true, false, '002060', 60, 40, 0);
$gen->addTable(
    ['THUỘC TÍNH', 'NỘI DUNG ĐẶC TẢ'],
    [
        ['Tên Use Case', 'UC03: Quản trị và soạn thảo câu hỏi trong Studio (Question Studio Editor)'],
        ['Tác nhân (Actors)', 'Quản trị viên (Admin), Giáo viên (Teacher)'],
        ['Mô tả tóm tắt', 'Cho phép biên tập, tạo mới hoặc chỉnh sửa câu hỏi trắc nghiệm, hình ảnh minh họa, các tùy chọn đáp án đúng/sai và cấu hình điểm số.'],
        ['Điều kiện tiên quyết', 'Người dùng có quyền quản trị bộ đề thi.'],
        ['Điều kiện sau', 'Câu hỏi được lưu vào CSDL kèm theo các tùy chọn đáp án và tài nguyên hình ảnh.'],
        ['Luồng sự kiện chính', "1. Người dùng chọn 'Studio Soạn đề' (/quan-tri/bo-de-cau-hoi).\n2. Chọn Bộ đề thi cần thao tác hoặc chọn tạo câu hỏi mới.\n3. Nhập tiêu đề câu hỏi, chọn loại câu hỏi (Trắc nghiệm đơn/nhiều lựa chọn), điểm số.\n4. Tải lên hình ảnh minh họa (nếu câu hỏi có ảnh chụp màn hình Word/Excel).\n5. Nhập nội dung các phương án trả lời A, B, C, D và đánh dấu phương án đúng (`is_correct = 1`).\n6. Bấm nút 'Lưu câu hỏi', hệ thống thực thi Transaction lưu dữ liệu an toàn."],
        ['Luồng sự kiện rẽ nhánh', "5a. Chưa có phương án nào được đánh dấu là đúng: Hệ thống cảnh báo yêu cầu chọn ít nhất 1 đáp án đúng.\n4a. Tệp ảnh tải lên không đúng định dạng (jpg, png) hoặc vượt quá 2MB: Hệ thống từ chối tải tệp."],
    ],
    [2600, 6471]
);

// UC04
$gen->addParagraph("Bảng 2.4: Đặc tả chi tiết Use Case UC04: Import tự động ngân hàng câu hỏi iSpring", 'left', 24, true, false, '002060', 60, 40, 0);
$gen->addTable(
    ['THUỘC TÍNH', 'NỘI DUNG ĐẶC TẢ'],
    [
        ['Tên Use Case', 'UC04: Import tự động ngân hàng câu hỏi iSpring (Import Legacy Questions)'],
        ['Tác nhân (Actors)', 'Quản trị viên (Admin)'],
        ['Mô tả tóm tắt', 'Hệ thống tự động quét và giải nén gói đề thi zip gốc `legacy_ic3_source.zip`, phân tích cú pháp HTML5 và trích xuất 509 câu hỏi độc lập vào CSDL.'],
        ['Điều kiện tiên quyết', 'Tệp tin `legacy_ic3_source.zip` tồn tại trong thư mục `storage/legacy_backup/`.'],
        ['Điều kiện sau', '509 câu hỏi và các tài nguyên ảnh được nạp đầy đủ vào bảng `questions` trong MySQL.'],
        ['Luồng sự kiện chính', "1. Admin thực thi lệnh Artisan: `php artisan ic3:import-questions`.\n2. Lệnh kiểm tra và tự động giải nén file nguồn zip nếu chưa có thư mục tạm.\n3. Quét tệp `manifest.json` để xác định danh mục 35 bài luyện thi theo 3 Khối lớp.\n4. Với mỗi bài thi, phân tích cú pháp dữ liệu câu hỏi iSpring trích xuất văn bản đề bài và các tùy chọn.\n5. Sao chép các tệp ảnh minh họa vào thư mục chuẩn `storage/app/public/question_assets/`.\n6. Tạo mới bản ghi trong CSDL và thông báo hoàn tất quá trình trích xuất."],
        ['Luồng sự kiện rẽ nhánh', "1a. Không tìm thấy file zip: Hệ thống báo lỗi và hướng dẫn kiểm tra thư mục backup.\n4a. Câu hỏi đã tồn tại trong CSDL: Hệ thống tự động cập nhật nội dung thay vì tạo trùng lặp."],
    ],
    [2600, 6471]
);

// UC05
$gen->addParagraph("Bảng 2.5: Đặc tả chi tiết Use Case UC05: Quản lý bộ đề thi theo khối lớp", 'left', 24, true, false, '002060', 60, 40, 0);
$gen->addTable(
    ['THUỘC TÍNH', 'NỘI DUNG ĐẶC TẢ'],
    [
        ['Tên Use Case', 'UC05: Quản lý bộ đề thi theo khối lớp (Practice Test Management)'],
        ['Tác nhân (Actors)', 'Quản trị viên (Admin), Giáo viên (Teacher)'],
        ['Mô tả tóm tắt', 'Quản lý 35 bài luyện thi phân chia cho 3 Khối lớp, cấu hình thời gian thi, điểm đạt và trạng thái xuất bản.'],
        ['Điều kiện tiên quyết', 'Người dùng đã đăng nhập quyền Admin hoặc Giáo viên.'],
        ['Điều kiện sau', 'Thông tin bài luyện thi được cập nhật hiển thị cho học sinh làm bài.'],
        ['Luồng sự kiện chính', "1. Người dùng chọn danh mục đề thi theo Khối lớp (Khối 3, 4 hoặc 5).\n2. Chọn chỉnh sửa thông tin bài thi (Tên bài, Chủ đề liên kết, Thời gian làm bài, Điểm đạt, Bật/tắt đảo câu hỏi).\n3. Bấm 'Cập nhật bài thi', hệ thống lưu vào bảng `practice_tests`."],
        ['Luồng sự kiện rẽ nhánh', "2a. Thời gian làm bài âm hoặc bằng 0: Hệ thống thông báo thời gian làm bài không hợp lệ."],
    ],
    [2600, 6471]
);

// UC06
$gen->addParagraph("Bảng 2.6: Đặc tả chi tiết Use Case UC06: Học sinh làm bài thi trắc nghiệm", 'left', 24, true, false, '002060', 60, 40, 0);
$gen->addTable(
    ['THUỘC TÍNH', 'NỘI DUNG ĐẶC TẢ'],
    [
        ['Tên Use Case', 'UC06: Học sinh làm bài thi trắc nghiệm (Student Exam Taking)'],
        ['Tác nhân (Actors)', 'Học sinh (Student)'],
        ['Mô tả tóm tắt', 'Học sinh khởi chạy bài thi trong phòng thi ảo, tương tác trả lời câu hỏi trắc nghiệm kèm hình ảnh minh họa và kiểm soát thời gian đếm ngược.'],
        ['Điều kiện tiên quyết', 'Học sinh đã đăng nhập và được cấp quyền truy cập khối lớp tương ứng.'],
        ['Điều kiện sau', 'Kết quả các câu trả lời được ghi nhận và gửi về máy chủ để chấm điểm.'],
        ['Luồng sự kiện chính', "1. Học sinh truy cập mục 'Học tập' -> chọn Khối lớp -> chọn Bài luyện thi.\n2. Bấm 'Bắt đầu làm bài', hệ thống mở giao diện Native Quiz Player.\n3. Hệ thống nạp ngẫu nhiên các câu hỏi và khởi động đồng hồ đếm ngược.\n4. Học sinh đọc câu hỏi, xem ảnh minh họa và chọn đáp án tương ứng.\n5. Học sinh bấm nút 'Nộp bài' khi đã hoàn thành các câu hỏi."],
        ['Luồng sự kiện rẽ nhánh', "5a. Hết giờ làm bài trước khi học sinh bấm nộp bài: Trình duyệt tự động kích hoạt sự kiện gửi bài thi lên server."],
    ],
    [2600, 6471]
);

// UC07
$gen->addParagraph("Bảng 2.7: Đặc tả chi tiết Use Case UC07: Tự động chấm điểm và ghi nhận kết quả thi", 'left', 24, true, false, '002060', 60, 40, 0);
$gen->addTable(
    ['THUỘC TÍNH', 'NỘI DUNG ĐẶC TẢ'],
    [
        ['Tên Use Case', 'UC07: Tự động chấm điểm và ghi nhận kết quả thi (Grading & Reward)'],
        ['Tác nhân (Actors)', 'Hệ sinh thái hệ thống (System Engine)'],
        ['Mô tả tóm tắt', 'Hệ thống tự động chấm điểm bài làm của học sinh, lưu vết vào bảng `test_attempts` và phân bổ Sao thưởng nếu đạt điểm chuẩn.'],
        ['Điều kiện tiên quyết', 'Dữ liệu bài thi được gửi lên endpoint `/bai-luyen/{slug}/ket-qua`.'],
        ['Điều kiện sau', 'Điểm số được lưu trữ, số sao tài khoản học sinh được cập nhật.'],
        ['Luồng sự kiện chính', "1. Backend tiếp nhận danh sách câu trả lời của học sinh.\n2. Vòng lặp đối soát từng câu với phương án đúng trong bảng `question_options`.\n3. Tính tổng điểm đạt được và tỷ lệ phần trăm số câu đúng.\n4. So sánh với `pass_score` của đề thi để xác định kết quả Đạt / Chưa đạt.\n5. Tạo bản ghi mới trong bảng `test_attempts` ghi nhận điểm, số câu đúng và thời gian làm bài.\n6. Nếu Đạt điểm chuẩn, cộng 10 Sao vào trường `reward_stars` của học sinh và tạo giao dịch trong `game_transactions`.\n7. Trả về kết quả hiển thị cho học sinh."],
        ['Luồng sự kiện rẽ nhánh', "4a. Học sinh không đạt điểm chuẩn: Hệ thống ghi nhận kết quả Fail, không cộng sao và khuyến khích học sinh ôn tập lại."],
    ],
    [2600, 6471]
);

// UC08
$gen->addParagraph("Bảng 2.8: Đặc tả chi tiết Use Case UC08: Đổi sao thưởng lấy thời gian chơi game", 'left', 24, true, false, '002060', 60, 40, 0);
$gen->addTable(
    ['THUỘC TÍNH', 'NỘI DUNG ĐẶC TẢ'],
    [
        ['Tên Use Case', 'UC08: Đổi sao thưởng lấy thời gian chơi game (Gamification Exchange)'],
        ['Tác nhân (Actors)', 'Học sinh (Student)'],
        ['Mô tả tóm tắt', 'Học sinh dùng số Sao tích lũy được từ các bài thi để đổi lấy thời gian chơi các minigame giáo dục trong Khu trò chơi.'],
        ['Điều kiện tiên quyết', 'Tài khoản học sinh có số dư Sao lớn hơn hoặc bằng gói đổi thưởng tối thiểu.'],
        ['Điều kiện sau', 'Số Sao bị trừ tương ứng và số phút chơi game (`game_time_seconds`) được cộng thêm.'],
        ['Luồng sự kiện chính', "1. Học sinh truy cập mục 'Khu trò chơi' (/tro-choi).\n2. Hệ thống hiển thị số Sao hiện có và các gói đổi thời gian (VD: 10 Sao = 15 phút chơi).\n3. Học sinh chọn gói đổi và bấm 'Xác nhận đổi'.\n4. Hệ thống kiểm tra số dư Sao, trừ số Sao tương ứng và cộng thời gian chơi.\n5. Tạo bản ghi giao dịch trong `game_transactions` và mở khóa minigame."],
        ['Luồng sự kiện rẽ nhánh', "4a. Số dư Sao không đủ: Hệ thống thông báo 'Bạn không đủ Sao để đổi gói này, hãy tích cực làm bài thi để nhận thêm Sao!'."],
    ],
    [2600, 6471]
);

// UC09
$gen->addParagraph("Bảng 2.9: Đặc tả chi tiết Use Case UC09: Phụ huynh tra cứu lịch sử và nhận cảnh báo", 'left', 24, true, false, '002060', 60, 40, 0);
$gen->addTable(
    ['THUỘC TÍNH', 'NỘI DUNG ĐẶC TẢ'],
    [
        ['Tên Use Case', 'UC09: Phụ huynh tra cứu lịch sử và nhận cảnh báo (Parent Dashboard)'],
        ['Tác nhân (Actors)', 'Phụ huynh (Parent)'],
        ['Mô tả tóm tắt', 'Cung cấp góc nhìn toàn diện cho phụ huynh về kết quả bài thi, số lần làm bài, tiến độ học tập và cảnh báo thi hỏng của con em.'],
        ['Điều kiện tiên quyết', 'Phụ huynh truy cập vào phân hệ `/phu-huynh` gắn liền với tài khoản học sinh.'],
        ['Điều kiện sau', 'Hiển thị đầy đủ bảng điểm, biểu đồ tiến trình và cảnh báo nhắc nhở.'],
        ['Luồng sự kiện chính', "1. Phụ huynh truy cập mục 'Phụ huynh theo dõi'.\n2. Hệ thống tổng hợp tất cả các lần thi của học sinh trong bảng `test_attempts`.\n3. Hiển thị biểu đồ phổ điểm và tỷ lệ đạt của con.\n4. Hiển thị bảng chi tiết các lần thi: Tên bài, Ngày giờ thi, Điểm số, Đạt/Hỏng.\n5. Nếu học sinh có trên 3 lần thi không đạt, hiển thị hộp cảnh báo màu đỏ nhắc nhở phụ huynh phối hợp đôn đốc."],
        ['Luồng sự kiện rẽ nhánh', "Không có."],
    ],
    [2600, 6471]
);

// UC10
$gen->addParagraph("Bảng 2.10: Đặc tả chi tiết Use Case UC10: Xuất báo cáo thống kê thi cử ra CSV", 'left', 24, true, false, '002060', 60, 40, 0);
$gen->addTable(
    ['THUỘC TÍNH', 'NỘI DUNG ĐẶC TẢ'],
    [
        ['Tên Use Case', 'UC10: Xuất báo cáo thống kê thi cử ra CSV (Export Report CSV)'],
        ['Tác nhân (Actors)', 'Quản trị viên (Admin), Giáo viên (Teacher)'],
        ['Mô tả tóm tắt', 'Cho phép xuất toàn bộ dữ liệu lịch sử thi cử của học sinh ra tệp tin CSV phục vụ công tác lưu trữ học bạ và báo cáo lên Ban giám hiệu.'],
        ['Điều kiện tiên quyết', 'Người dùng có quyền quản trị hoặc giáo viên.'],
        ['Điều kiện sau', 'Trình duyệt tự động tải xuống tệp tin CSV chứa dữ liệu tổng hợp thi cử.'],
        ['Luồng sự kiện chính', "1. Người dùng truy cập Dashboard (/quan-tri).\n2. Bấm nút 'Xuất báo cáo CSV'.\n3. Backend truy vấn kết hợp các bảng `test_attempts`, `users`, `practice_tests`, `classrooms`.\n4. Định dạng dữ liệu dạng dòng phân cách dấu phẩy, mã hóa UTF-8 with BOM.\n5. Trả về phản hồi tải file với header `Content-Type: text/csv`."],
        ['Luồng sự kiện rẽ nhánh', "3a. Chưa có dữ liệu làm bài nào trong hệ thống: Tệp tin CSV xuất ra chỉ có hàng tiêu đề."],
    ],
    [2600, 6471]
);
$gen->addPageBreak();

// 2.3 CƠ SỞ DỮ LIỆU
$gen->addHeading2("2.3. Thiết kế Cơ sở dữ liệu chi tiết");

$gen->addHeading3("2.3.1. Sơ đồ thực thể mối quan hệ (ERD)");
$gen->addParagraph("Cơ sở dữ liệu của hệ thống IC3 Quest được chuẩn hóa theo dạng chuẩn 3NF, bao gồm 12 bảng quan hệ thực thể chính liên kết chặt chẽ qua các khóa ngoại, đảm bảo tính toàn vẹn và tối ưu hóa tốc độ truy vấn:", 'both', 26, false, false, '000000', 60, 60, 720);
$gen->addBullet("Mối quan hệ 1 - N giữa `programs` và `levels`: Một chương trình đào tạo (IC3 GS6) bao gồm nhiều Khối lớp (Khối 3, 4, 5).");
$gen->addBullet("Mối quan hệ 1 - N giữa `levels` và `topics`: Mỗi khối lớp chia thành nhiều chủ đề kiến thức chuẩn quốc tế.");
$gen->addBullet("Mối quan hệ 1 - N giữa `topics` và `practice_tests`: Mỗi chủ đề có nhiều bài luyện thi trắc nghiệm.");
$gen->addBullet("Mối quan hệ 1 - N giữa `practice_tests` và `questions`: Mỗi bài thi chứa nhiều câu hỏi (trung bình 15 - 30 câu).");
$gen->addBullet("Mối quan hệ 1 - N giữa `questions` và `question_options`: Mỗi câu hỏi có nhiều tùy chọn trả lời A, B, C, D.");
$gen->addBullet("Mối quan hệ 1 - N giữa `questions` và `question_assets`: Mỗi câu hỏi liên kết với các tài nguyên ảnh minh họa.");
$gen->addBullet("Mối quan hệ 1 - N giữa `users` và `test_attempts`: Mỗi học sinh có nhiều lượt làm bài thi được lưu vết.");
$gen->addBullet("Mối quan hệ N - N giữa `users` (giáo viên) và `levels`: Quản lý thông qua bảng pivot `teacher_level`.");
$gen->addBullet("Mối quan hệ 1 - N giữa `users` và `game_transactions`: Theo dõi chi tiết lịch sử cộng/trừ sao thưởng.");

$gen->addHeading3("2.3.2. Bảng đặc tả cấu trúc chi tiết 12 bảng CSDL trong MySQL");

// Table 1: users
$gen->addParagraph("Bảng 2.11: Đặc tả cấu trúc bảng dữ liệu `users` (Quản lý người dùng hệ thống)", 'left', 24, true, false, '002060', 60, 40, 0);
$gen->addTable(
    ['TÊN TRƯỜNG', 'KIỂU DỮ LIỆU', 'NULL', 'KHÓA', 'MÔ TẢ CHI TIẾT'],
    [
        ['id', 'bigint unsigned', 'No', 'PK', 'Khóa chính tự tăng định danh người dùng'],
        ['name', 'varchar(255)', 'No', '', 'Họ và tên đầy đủ của người dùng'],
        ['email', 'varchar(255)', 'No', 'UQ', 'Địa chỉ email đăng nhập duy nhất'],
        ['password', 'varchar(255)', 'No', '', 'Mật khẩu băm an toàn chuẩn Bcrypt'],
        ['role', 'varchar(255)', 'No', '', 'Vai trò người dùng: admin, teacher, student'],
        ['student_code', 'varchar(255)', 'Yes', 'UQ', 'Mã định danh học sinh (HS001, HS002...)'],
        ['classroom_id', 'bigint unsigned', 'Yes', 'FK', 'Mã lớp học tham gia (liên kết classrooms.id)'],
        ['reward_stars', 'int unsigned', 'No', '', 'Số Sao tích lũy được từ các bài thi đạt chuẩn'],
        ['game_time_seconds', 'int unsigned', 'No', '', 'Số giây thời gian chơi game còn lại'],
        ['created_by', 'bigint unsigned', 'Yes', 'FK', 'ID giáo viên hoặc admin đã tạo tài khoản'],
        ['max_students', 'smallint unsigned', 'No', '', 'Số lượng học sinh tối đa giáo viên được quản lý'],
        ['expires_at', 'date', 'Yes', '', 'Ngày hết hạn tài khoản giáo viên/học sinh'],
        ['status', 'varchar(30)', 'No', '', 'Trạng thái hoạt động: active, inactive, pending'],
        ['created_at', 'timestamp', 'Yes', '', 'Thời điểm khởi tạo bản ghi'],
        ['updated_at', 'timestamp', 'Yes', '', 'Thời điểm cập nhật bản ghi gần nhất'],
    ],
    [1800, 1800, 900, 900, 3671]
);

// Table 2: classrooms
$gen->addParagraph("Bảng 2.12: Đặc tả cấu trúc bảng dữ liệu `classrooms` (Quản lý lớp học)", 'left', 24, true, false, '002060', 60, 40, 0);
$gen->addTable(
    ['TÊN TRƯỜNG', 'KIỂU DỮ LIỆU', 'NULL', 'KHÓA', 'MÔ TẢ CHI TIẾT'],
    [
        ['id', 'bigint unsigned', 'No', 'PK', 'Khóa chính tự tăng của lớp học'],
        ['name', 'varchar(255)', 'No', '', 'Tên lớp học (Lớp 3A1, 4A1, 5A1...)'],
        ['grade', 'tinyint unsigned', 'No', '', 'Khối lớp học tập (3, 4, hoặc 5)'],
        ['school_year', 'varchar(255)', 'No', '', 'Niên khóa đào tạo (Ví dụ: 2026-2027)'],
        ['teacher_id', 'bigint unsigned', 'Yes', 'FK', 'ID giáo viên chủ nhiệm (liên kết users.id)'],
        ['created_at', 'timestamp', 'Yes', '', 'Thời điểm tạo lớp'],
        ['updated_at', 'timestamp', 'Yes', '', 'Thời điểm cập nhật thông tin lớp'],
    ],
    [1800, 1800, 900, 900, 3671]
);

// Table 3: programs
$gen->addParagraph("Bảng 2.13: Đặc tả cấu trúc bảng dữ liệu `programs` (Chương trình đào tạo)", 'left', 24, true, false, '002060', 60, 40, 0);
$gen->addTable(
    ['TÊN TRƯỜNG', 'KIỂU DỮ LIỆU', 'NULL', 'KHÓA', 'MÔ TẢ CHI TIẾT'],
    [
        ['id', 'bigint unsigned', 'No', 'PK', 'Khóa chính của chương trình'],
        ['name', 'varchar(255)', 'No', '', 'Tên chương trình (IC3 GS6 Tiểu học)'],
        ['slug', 'varchar(255)', 'No', 'UQ', 'Đường dẫn tĩnh thân thiện SEO URL'],
        ['description', 'text', 'Yes', '', 'Mô tả chi tiết mục tiêu chương trình'],
        ['accent', 'varchar(255)', 'No', '', 'Mã màu sắc chủ đạo đại diện (#635bff)'],
        ['created_at', 'timestamp', 'Yes', '', 'Thời điểm khởi tạo'],
        ['updated_at', 'timestamp', 'Yes', '', 'Thời điểm cập nhật'],
    ],
    [1800, 1800, 900, 900, 3671]
);

// Table 4: levels
$gen->addParagraph("Bảng 2.14: Đặc tả cấu trúc bảng dữ liệu `levels` (Khối lớp - Cấp độ)", 'left', 24, true, false, '002060', 60, 40, 0);
$gen->addTable(
    ['TÊN TRƯỜNG', 'KIỂU DỮ LIỆU', 'NULL', 'KHÓA', 'MÔ TẢ CHI TIẾT'],
    [
        ['id', 'bigint unsigned', 'No', 'PK', 'Khóa chính cấp độ'],
        ['program_id', 'bigint unsigned', 'No', 'FK', 'Liên kết chương trình (programs.id)'],
        ['name', 'varchar(255)', 'No', '', 'Tên cấp độ (IC3 GS6 Spark Level 1 — Khối 3)'],
        ['slug', 'varchar(255)', 'No', '', 'Slug định danh trên URL'],
        ['grade', 'tinyint unsigned', 'No', '', 'Khối lớp học sinh (3, 4, 5)'],
        ['position', 'smallint unsigned', 'No', '', 'Thứ tự sắp xếp hiển thị'],
        ['created_at', 'timestamp', 'Yes', '', 'Thời điểm khởi tạo'],
        ['updated_at', 'timestamp', 'Yes', '', 'Thời điểm cập nhật'],
    ],
    [1800, 1800, 900, 900, 3671]
);

// Table 5: topics
$gen->addParagraph("Bảng 2.15: Đặc tả cấu trúc bảng dữ liệu `topics` (Chủ đề kiến thức)", 'left', 24, true, false, '002060', 60, 40, 0);
$gen->addTable(
    ['TÊN TRƯỜNG', 'KIỂU DỮ LIỆU', 'NULL', 'KHÓA', 'MÔ TẢ CHI TIẾT'],
    [
        ['id', 'bigint unsigned', 'No', 'PK', 'Khóa chính chủ đề'],
        ['level_id', 'bigint unsigned', 'No', 'FK', 'Khối lớp trực thuộc (levels.id)'],
        ['name', 'varchar(255)', 'No', '', 'Tên chủ đề (Căn bản công nghệ, Công dân số...)'],
        ['slug', 'varchar(255)', 'No', '', 'Slug định danh URL'],
        ['description', 'text', 'Yes', '', 'Mô tả chi tiết chuẩn kiến thức chủ đề'],
        ['icon', 'varchar(255)', 'No', '', 'Tên biểu tượng đại diện giao diện'],
        ['position', 'smallint unsigned', 'No', '', 'Thứ tự sắp xếp chủ đề'],
        ['created_at', 'timestamp', 'Yes', '', 'Thời điểm khởi tạo'],
        ['updated_at', 'timestamp', 'Yes', '', 'Thời điểm cập nhật'],
    ],
    [1800, 1800, 900, 900, 3671]
);

// Table 6: practice_tests
$gen->addParagraph("Bảng 2.16: Đặc tả cấu trúc bảng dữ liệu `practice_tests` (Bài luyện thi)", 'left', 24, true, false, '002060', 60, 40, 0);
$gen->addTable(
    ['TÊN TRƯỜNG', 'KIỂU DỮ LIỆU', 'NULL', 'KHÓA', 'MÔ TẢ CHI TIẾT'],
    [
        ['id', 'bigint unsigned', 'No', 'PK', 'Khóa chính bài luyện thi'],
        ['topic_id', 'bigint unsigned', 'No', 'FK', 'Chủ đề trực thuộc (topics.id)'],
        ['name', 'varchar(255)', 'No', '', 'Tên bài luyện thi (Bài luyện 1, Bài luyện 2...)'],
        ['slug', 'varchar(255)', 'No', '', 'Đường dẫn tĩnh thân thiện'],
        ['launch_path', 'varchar(255)', 'Yes', '', 'Đường dẫn khởi chạy nếu nhúng legacy'],
        ['access_code', 'text', 'Yes', '', 'Mã bảo vệ hoặc mật khẩu truy cập đề thi'],
        ['resource_manifest', 'json', 'Yes', '', 'Dữ liệu manifest JSON mô tả tài nguyên gốc'],
        ['duration_minutes', 'smallint unsigned', 'No', '', 'Thời gian làm bài quy định (phút)'],
        ['question_count', 'smallint unsigned', 'No', '', 'Tổng số câu hỏi trong bài luyện'],
        ['pass_score', 'smallint unsigned', 'No', '', 'Điểm số tối thiểu để được công nhận Đạt'],
        ['max_score', 'smallint unsigned', 'No', '', 'Điểm số tối đa của đề thi (thang điểm 1000)'],
        ['difficulty', 'enum', 'No', '', 'Mức độ khó: Cơ bản, Trung bình, Nâng cao'],
        ['is_published', 'tinyint(1)', 'No', '', 'Trạng thái phát hành bài thi (1: Bật, 0: Ẩn)'],
        ['shuffle_questions', 'tinyint(1)', 'No', '', 'Cờ bật đảo ngẫu nhiên thứ tự câu hỏi'],
        ['shuffle_options', 'tinyint(1)', 'No', '', 'Cờ bật đảo ngẫu nhiên các phương án A-B-C-D'],
        ['position', 'smallint unsigned', 'No', '', 'Thứ tự hiển thị trong chủ đề'],
        ['created_at', 'timestamp', 'Yes', '', 'Thời điểm khởi tạo'],
        ['updated_at', 'timestamp', 'Yes', '', 'Thời điểm cập nhật'],
    ],
    [1800, 1800, 900, 900, 3671]
);

// Table 7: questions
$gen->addParagraph("Bảng 2.17: Đặc tả cấu trúc bảng dữ liệu `questions` (Ngân hàng câu hỏi)", 'left', 24, true, false, '002060', 60, 40, 0);
$gen->addTable(
    ['TÊN TRƯỜNG', 'KIỂU DỮ LIỆU', 'NULL', 'KHÓA', 'MÔ TẢ CHI TIẾT'],
    [
        ['id', 'bigint unsigned', 'No', 'PK', 'Khóa chính câu hỏi'],
        ['practice_test_id', 'bigint unsigned', 'No', 'FK', 'Bài luyện thi trực thuộc (practice_tests.id)'],
        ['external_id', 'varchar(255)', 'Yes', '', 'Mã định danh gốc từ gói đề iSpring'],
        ['type', 'varchar(60)', 'No', '', 'Loại câu hỏi (single_choice, multiple_choice...)'],
        ['title', 'text', 'Yes', '', 'Nội dung văn bản câu hỏi'],
        ['configuration', 'json', 'Yes', '', 'Cấu hình JSON tương tác nâng cao của câu hỏi'],
        ['position', 'smallint unsigned', 'No', '', 'Thứ tự câu hỏi trong bài thi'],
        ['points', 'smallint unsigned', 'No', '', 'Số điểm thưởng khi trả lời đúng'],
        ['is_published', 'tinyint(1)', 'No', '', 'Trạng thái hiển thị câu hỏi'],
        ['created_at', 'timestamp', 'Yes', '', 'Thời điểm khởi tạo câu hỏi'],
        ['updated_at', 'timestamp', 'Yes', '', 'Thời điểm cập nhật'],
    ],
    [1800, 1800, 900, 900, 3671]
);

// Table 8: question_options
$gen->addParagraph("Bảng 2.18: Đặc tả cấu trúc bảng dữ liệu `question_options` (Tùy chọn đáp án)", 'left', 24, true, false, '002060', 60, 40, 0);
$gen->addTable(
    ['TÊN TRƯỜNG', 'KIỂU DỮ LIỆU', 'NULL', 'KHÓA', 'MÔ TẢ CHI TIẾT'],
    [
        ['id', 'bigint unsigned', 'No', 'PK', 'Khóa chính phương án đáp án'],
        ['question_id', 'bigint unsigned', 'No', 'FK', 'Câu hỏi trực thuộc (questions.id)'],
        ['content', 'text', 'Yes', '', 'Nội dung văn bản phương án trả lời'],
        ['image_path', 'varchar(255)', 'Yes', '', 'Đường dẫn ảnh minh họa cho phương án'],
        ['is_correct', 'tinyint(1)', 'No', '', 'Đánh dấu đáp án đúng (1: Đúng, 0: Sai)'],
        ['position', 'smallint unsigned', 'No', '', 'Vị trí thứ tự đáp án (A, B, C, D)'],
        ['metadata', 'json', 'Yes', '', 'Thuộc tính bổ trợ định dạng JSON'],
        ['created_at', 'timestamp', 'Yes', '', 'Thời điểm khởi tạo'],
        ['updated_at', 'timestamp', 'Yes', '', 'Thời điểm cập nhật'],
    ],
    [1800, 1800, 900, 900, 3671]
);

// Table 9: question_assets
$gen->addParagraph("Bảng 2.19: Đặc tả cấu trúc bảng dữ liệu `question_assets` (Tài nguyên ảnh câu hỏi)", 'left', 24, true, false, '002060', 60, 40, 0);
$gen->addTable(
    ['TÊN TRƯỜNG', 'KIỂU DỮ LIỆU', 'NULL', 'KHÓA', 'MÔ TẢ CHI TIẾT'],
    [
        ['id', 'bigint unsigned', 'No', 'PK', 'Khóa chính tệp tài nguyên'],
        ['question_id', 'bigint unsigned', 'No', 'FK', 'Câu hỏi sở hữu tệp (questions.id)'],
        ['kind', 'varchar(30)', 'No', '', 'Loại tài nguyên: image, diagram, audio'],
        ['path', 'varchar(255)', 'No', '', 'Đường dẫn lưu trữ trên đĩa web server'],
        ['original_name', 'varchar(255)', 'Yes', '', 'Tên tệp tin gốc khi trích xuất'],
        ['mime_type', 'varchar(255)', 'Yes', '', 'Kiểu định dạng MIME (image/png, image/jpeg)'],
        ['metadata', 'json', 'Yes', '', 'Thông số kích thước chiều rộng, chiều cao ảnh'],
        ['created_at', 'timestamp', 'Yes', '', 'Thời điểm trích xuất lưu trữ'],
        ['updated_at', 'timestamp', 'Yes', '', 'Thời điểm cập nhật'],
    ],
    [1800, 1800, 900, 900, 3671]
);

// Table 10: test_attempts
$gen->addParagraph("Bảng 2.20: Đặc tả cấu trúc bảng dữ liệu `test_attempts` (Lịch sử làm bài thi)", 'left', 24, true, false, '002060', 60, 40, 0);
$gen->addTable(
    ['TÊN TRƯỜNG', 'KIỂU DỮ LIỆU', 'NULL', 'KHÓA', 'MÔ TẢ CHI TIẾT'],
    [
        ['id', 'bigint unsigned', 'No', 'PK', 'Khóa chính lượt làm bài thi'],
        ['user_id', 'bigint unsigned', 'No', 'FK', 'Học sinh thực hiện bài thi (users.id)'],
        ['practice_test_id', 'bigint unsigned', 'No', 'FK', 'Đề thi đã thực hiện (practice_tests.id)'],
        ['score', 'smallint unsigned', 'No', '', 'Điểm số bài thi đạt được (thang điểm 1000)'],
        ['correct_answers', 'smallint unsigned', 'No', '', 'Số lượng câu trả lời chính xác'],
        ['total_questions', 'smallint unsigned', 'No', '', 'Tổng số câu hỏi của bài thi'],
        ['duration_seconds', 'int unsigned', 'No', '', 'Tổng thời gian học sinh đã làm (giây)'],
        ['completed_at', 'timestamp', 'Yes', '', 'Thời điểm hoàn thành nộp bài thi'],
        ['created_at', 'timestamp', 'Yes', '', 'Thời điểm bắt đầu làm bài'],
        ['updated_at', 'timestamp', 'Yes', '', 'Thời điểm lưu kết quả'],
    ],
    [1800, 1800, 900, 900, 3671]
);

// Table 11: game_settings
$gen->addParagraph("Bảng 2.21: Đặc tả cấu trúc bảng dữ liệu `game_settings` (Cấu hình đổi thưởng Sao)", 'left', 24, true, false, '002060', 60, 40, 0);
$gen->addTable(
    ['TÊN TRƯỜNG', 'KIỂU DỮ LIỆU', 'NULL', 'KHÓA', 'MÔ TẢ CHI TIẾT'],
    [
        ['id', 'bigint unsigned', 'No', 'PK', 'Khóa chính cấu hình'],
        ['key', 'varchar(255)', 'No', 'UQ', 'Từ khóa định danh cấu hình (stars_per_minute...)'],
        ['value', 'text', 'Yes', '', 'Giá trị thiết lập tương ứng dạng chuỗi/JSON'],
        ['description', 'varchar(255)', 'Yes', '', 'Mô tả giải thích ý nghĩa cấu hình'],
        ['created_at', 'timestamp', 'Yes', '', 'Thời điểm khởi tạo'],
        ['updated_at', 'timestamp', 'Yes', '', 'Thời điểm cập nhật'],
    ],
    [1800, 1800, 900, 900, 3671]
);

// Table 12: game_transactions
$gen->addParagraph("Bảng 2.22: Đặc tả cấu trúc bảng dữ liệu `game_transactions` (Lịch sử đổi quà Gamification)", 'left', 24, true, false, '002060', 60, 40, 0);
$gen->addTable(
    ['TÊN TRƯỜNG', 'KIỂU DỮ LIỆU', 'NULL', 'KHÓA', 'MÔ TẢ CHI TIẾT'],
    [
        ['id', 'bigint unsigned', 'No', 'PK', 'Khóa chính giao dịch'],
        ['user_id', 'bigint unsigned', 'No', 'FK', 'Học sinh thực hiện giao dịch (users.id)'],
        ['type', 'varchar(255)', 'No', '', 'Loại giao dịch: reward_test, exchange_game'],
        ['stars_change', 'int', 'No', '', 'Biến động số Sao (+10 sao thi đạt, -20 sao đổi game)'],
        ['time_seconds_change', 'int', 'No', '', 'Biến động số giây chơi game (+900 giây chơi)'],
        ['description', 'varchar(255)', 'Yes', '', 'Nội dung chi tiết giao dịch'],
        ['created_at', 'timestamp', 'Yes', '', 'Thời điểm phát sinh giao dịch'],
        ['updated_at', 'timestamp', 'Yes', '', 'Thời điểm cập nhật'],
    ],
    [1800, 1800, 900, 900, 3671]
);
$gen->addPageBreak();

// -------------------------------------------------------------
// CHƯƠNG 3. CÀI ĐẶT THỰC NGHIỆM VÀ KẾT QUẢ
// -------------------------------------------------------------
$gen->addHeading1("Chương 3. CÀI ĐẶT THỰC NGHIỆM VÀ KẾT QUẢ");

$gen->addHeading2("3.1. Môi trường cài đặt hệ thống");
$gen->addParagraph("Để triển khai hệ thống IC3 Quest đạt hiệu năng tối ưu và độ ổn định cao, nhóm sinh viên đã thiết lập môi trường máy chủ phát triển cục bộ và thông số kiểm nghiệm như sau:", 'both', 26, false, false, '000000', 60, 60, 720);

$gen->addTable(
    ['THÀNH PHẦN', 'THÔNG SỐ MÔI TRƯỜNG THỰC NGHIỆM'],
    [
        ['Hệ điều hành máy chủ', 'Microsoft Windows 11 Pro 64-bit'],
        ['Môi trường máy chủ web', 'Laragon Local Server v6.0.0 (Apache 2.4.54 Win64)'],
        ['Bộ thông dịch PHP', 'PHP phiên bản 8.3.28 (Architecture: x64, Zend Engine v4.3.28)'],
        ['Hệ quản trị CSDL', 'MySQL Community Server 8.0.30 (Port: 3306, Charset: utf8mb4)'],
        ['Khung phát triển (Framework)', 'Laravel Framework v13.17 (Tương thích chuẩn PSR-12)'],
        ['Quản lý gói phụ thuộc', 'Composer phiên bản 2.7.2'],
        ['Công cụ Frontend & Bundler', 'Vite v5.x, TailwindCSS v3.4.x, NodeJS v20.x'],
        ['Công cụ kiểm thử API', 'Postman, trình duyệt Google Chrome DevTools'],
    ],
    [3200, 5871]
);

$gen->addHeading2("3.2. Một số giao diện chính và phân tích chức năng (10 giao diện)");

$gen->addHeading3("3.2.1. Giao diện Đăng nhập phân quyền (Auth Login)");
$gen->addParagraph("Giao diện Đăng nhập được thiết kế tinh tế với bảng màu tím hiện đại, tối ưu hóa cho cả 3 đối tượng người dùng. Học sinh có thể đăng nhập nhanh bằng Mã học sinh (student_code) hoặc địa chỉ email. Form đăng nhập tích hợp cơ chế bảo vệ chống tấn công CSRF thông qua directive `@csrf` của Laravel và ghi nhớ đăng nhập an toàn (`remember_token`). Khi đăng nhập thành công, Middleware chuyển hướng tự động người dùng về đúng phân hệ được cấp phép.", 'both', 26, false, false, '000000', 60, 60, 720);

$gen->addHeading3("3.2.2. Phân hệ Admin - Bảng điều khiển tổng quan (Dashboard)");
$gen->addParagraph("Bảng điều khiển cung cấp cái nhìn toàn cảnh về tình hình vận hành của hệ thống: hiển thị 4 thẻ KPI thống kê tổng số lượng học sinh đang hoạt động (28 tài khoản mẫu), tổng số bộ đề thi (35 bài), tổng số câu hỏi đã nạp (509 câu) và tổng số lượt thi đã thực hiện (197 lượt). Hệ thống tích hợp biểu đồ phổ điểm trực quan, bảng xếp hạng các bài thi có nhiều lượt tham gia nhất và nút chức năng “Xuất báo cáo CSV” hỗ trợ tải toàn bộ lịch sử thi cử về máy tính.", 'both', 26, false, false, '000000', 60, 60, 720);

$gen->addHeading3("3.2.3. Phân hệ Admin - Quản lý Người dùng, Phân quyền Giáo viên và Lớp học");
$gen->addParagraph("Giao diện Quản lý tập trung (/quan-tri/quan-ly) cho phép Admin điều hành toàn bộ nhân sự và cấu trúc trường học: Thêm mới tài khoản giáo viên, thiết lập giới hạn số học sinh tối đa (`max_students`), ngày hết hạn tài khoản; Phân công giáo viên chủ nhiệm cho từng lớp học (Lớp 3A1, 4A1, 5A1); Gán quyền giáo viên được phép quản lý đề thi của khối lớp nào thông qua bảng `teacher_level`; Thêm mới và phân loại danh sách học sinh theo từng lớp.", 'both', 26, false, false, '000000', 60, 60, 720);

$gen->addHeading3("3.2.4. Phân hệ Admin - IC3 Question Studio (Ngân hàng 509 câu hỏi)");
$gen->addParagraph("IC3 Question Studio là module quản trị ngân hàng đề thi chuyên nghiệp. Giao diện cho phép lọc câu hỏi theo Khối lớp (Khối 3, 4, 5), theo Chủ đề (Topics) và theo từng Bộ đề thi cụ thể. Mỗi câu hỏi trong danh sách hiển thị rõ ràng nội dung câu hỏi, loại câu trắc nghiệm, số điểm, ảnh minh họa đính kèm và các nút thao tác nhanh: Sửa nội dung, Xóa câu hỏi hoặc Xem trước cách hiển thị trên giao diện học sinh.", 'both', 26, false, false, '000000', 60, 60, 720);

$gen->addHeading3("3.2.5. Phân hệ Admin - Biên soạn và cấu hình chi tiết câu hỏi (Question Editor)");
$gen->addParagraph("Giao diện Soạn thảo chi tiết (/quan-tri/questions/{id}) hỗ trợ giáo viên chỉnh sửa sâu từng thành phần của câu hỏi: Biên tập nội dung đề bài; Tải lên tệp ảnh minh họa trực quan các thao tác trên phần mềm Word, Excel, PowerPoint; Thêm/bớt số lượng phương án trả lời; Tích chọn phương án đúng; Nhập lời giải thích cặn kẽ để học sinh đọc sau khi nộp bài; Thiết lập trạng thái xuất bản câu hỏi.", 'both', 26, false, false, '000000', 60, 60, 720);

$gen->addHeading3("3.2.6. Phân hệ Học sinh - Cổng học tập, chọn Khối lớp và Bài luyện");
$gen->addParagraph("Khi học sinh đăng nhập thành công, Cổng học tập hiển thị lộ trình học tập trực quan theo chương trình IC3 GS6 Tiểu học. Học sinh có thể chọn Khối 3 (Spark Level 1), Khối 4 (Spark Level 2) hoặc Khối 5 (Spark Level 3). Trong mỗi khối lớp, hệ thống phân chia thành 7 chủ đề kiến thức lớn (Căn bản công nghệ, Công dân số, Quản lý thông tin, Sáng tạo nội dung, Truyền thông số, Cộng tác, An toàn bảo mật) với tổng cộng 35 bài luyện thi được đánh số rõ ràng.", 'both', 26, false, false, '000000', 60, 60, 720);

$gen->addHeading3("3.2.7. Phân hệ Học sinh - Trình làm bài thi tương tác (Native IC3 Quiz Player)");
$gen->addParagraph("Đây là phòng thi ảo được nhóm sinh viên lập trình hoàn toàn độc lập thay thế cho iSpring cũ. Giao diện tối ưu hóa trải nghiệm làm bài: Đồng hồ đếm ngược thời gian làm bài ở góc trên; Thanh tiến trình thể hiện số câu đã làm trên tổng số câu hỏi; Khung hiển thị câu hỏi và hình ảnh chụp màn hình minh họa sắc nét; Các nút tùy chọn đáp án to rõ, dễ bấm trên màn hình cảm ứng; Nút điều hướng 'Câu trước' / 'Câu tiếp' và nút 'Nộp bài' nổi bật.", 'both', 26, false, false, '000000', 60, 60, 720);

$gen->addHeading3("3.2.8. Phân hệ Học sinh - Trang tổng kết, thành tích và xếp hạng thi đua");
$gen->addParagraph("Ngay sau khi bấm nộp bài, hệ thống hiển thị trang Kết quả thi đua với hiệu ứng chúc mừng sinh động: Điểm số chính xác trên thang 1000, số câu trả lời đúng, thời gian làm bài thực tế và chứng nhận Đạt / Không đạt. Nếu đạt điểm chuẩn, học sinh được cộng 10 Sao thưởng. Phía dưới là Bảng xếp hạng vinh danh Top học sinh có điểm số cao nhất và số sao tích lũy nhiều nhất toàn trường, khơi dậy tinh thần thi đua học tập.", 'both', 26, false, false, '000000', 60, 60, 720);

$gen->addHeading3("3.2.9. Phân hệ Học sinh - Khu trò chơi giải trí và đổi thưởng Sao");
$gen->addParagraph("Khu trò chơi (Game Zone) là tính năng Gamification độc đáo của đồ án. Học sinh có thể dùng số Sao tích lũy được từ việc làm bài thi chăm chỉ để đổi lấy thời gian chơi các trò chơi giáo dục rèn luyện tư duy máy tính. Khi chơi game, đồng hồ đếm ngược thời gian chơi game (`game_time_seconds`) sẽ tiêu hao dần và tự động khóa màn hình game khi hết giờ, giúp học sinh cân bằng giữa học tập và giải trí lành mạnh.", 'both', 26, false, false, '000000', 60, 60, 720);

$gen->addHeading3("3.2.10. Phân hệ Phụ huynh - Bảng điều khiển theo dõi tiến độ học tập (Parent Dashboard)");
$gen->addParagraph("Bảng điều khiển Phụ huynh (/phu-huynh) cung cấp công cụ giám sát minh bạch cho cha mẹ học sinh: Theo dõi tổng số lần con đã làm bài thi, điểm trung bình các bài luyện, tỷ lệ đậu/rớt; Danh sách nhật ký từng lần thi kèm thời gian chính xác; Số dư Sao và số phút con đã chơi game; Đặc biệt hệ thống có cơ chế cảnh báo sớm nếu con thi hỏng quá 3 lần để phụ huynh kịp thời động viên, kèm cặp.", 'both', 26, false, false, '000000', 60, 60, 720);

$gen->addHeading2("3.3. Các đoạn mã nguồn lập trình & thuật toán tiêu biểu");

$gen->addHeading3("3.3.1. Thuật toán trích xuất và phân tích cú pháp 509 câu hỏi từ gói iSpring");
$gen->addParagraph("Đoạn mã trích đoạn trong lệnh Artisan `ImportLegacyQuestions.php` phụ trách tự động giải nén `storage/legacy_backup/legacy_ic3_source.zip`, phân tích cú pháp manifest và nạp 509 câu hỏi độc lập vào CSDL MySQL:", 'both', 26, false, false, '000000', 60, 60, 720);

$codeImport = <<<'CODE'
// app/Console/Commands/ImportLegacyQuestions.php (Trích đoạn cốt lõi)
public function handle(): int
{
    $backupZip = storage_path('legacy_backup/legacy_ic3_source.zip');
    $extractPath = public_path('legacy');

    // 1. Kiểm tra và tự động giải nén kho lưu trữ dữ liệu gốc nếu cần
    if (!File::exists($extractPath) && File::exists($backupZip)) {
        $zip = new \ZipArchive();
        if ($zip->open($backupZip) === true) {
            $zip->extractTo($extractPath);
            $zip->close();
            $this->info("Đã giải nén thành công kho tài nguyên iSpring vào public/legacy/");
        }
    }

    // 2. Đọc file danh mục manifest xác định 35 bộ đề theo 3 Khối lớp
    $manifestFile = public_path('legacy/manifest.json');
    $tests = json_decode(File::get($manifestFile), true);

    // 3. Vòng lặp phân tích cú pháp từng câu hỏi và lưu vào MySQL độc lập
    foreach ($tests as $testData) {
        $practiceTest = PracticeTest::firstOrCreate(['slug' => $testData['slug']], [
            'name' => $testData['name'],
            'topic_id' => $testData['topic_id'],
            'pass_score' => 1000,
            'duration_minutes' => 30
        ]);

        foreach ($testData['questions'] as $qIndex => $qData) {
            $question = Question::updateOrCreate(
                ['practice_test_id' => $practiceTest->id, 'external_id' => $qData['id']],
                [
                    'type' => $qData['type'] ?? 'single_choice',
                    'title' => cleanHtmlTitle($qData['prompt']),
                    'points' => 10,
                    'position' => $qIndex + 1,
                    'is_published' => true
                ]
            );

            // Nạp các phương án trả lời đúng/sai
            foreach ($qData['options'] as $oIndex => $option) {
                QuestionOption::updateOrCreate(
                    ['question_id' => $question->id, 'position' => $oIndex + 1],
                    [
                        'content' => $option['text'],
                        'is_correct' => (bool)$option['is_correct']
                    ]
                );
            }
        }
    }
    $this->info("Hoàn tất nạp thành công 509 câu hỏi IC3 vào CSDL MySQL!");
    return Command::SUCCESS;
}
CODE;
$gen->addCodeBlock($codeImport);

$gen->addHeading3("3.3.2. Thuật toán xáo trộn câu hỏi và đáp án ngẫu nhiên trong phòng thi");
$gen->addParagraph("Đoạn mã xử lý trong Controller đảm bảo mỗi học sinh khi vào phòng thi ảo sẽ nhận được một bộ thứ tự câu hỏi và thứ tự các đáp án A-B-C-D ngẫu nhiên khác nhau nhằm triệt tiêu hoàn toàn khả năng nhìn bài hoặc ghi nhớ vị trí đáp án:", 'both', 26, false, false, '000000', 60, 60, 720);

$codeShuffle = <<<'CODE'
// app/Http/Controllers/LearningController.php (Khởi chạy phòng thi ảo)
public function launch(PracticeTest $practiceTest)
{
    // Nạp danh sách câu hỏi kèm các tùy chọn đáp án và ảnh minh họa
    $questions = $practiceTest->questions()
        ->with(['options', 'assets'])
        ->where('is_published', true)
        ->get();

    // 1. Áp dụng thuật toán xáo trộn câu hỏi nếu đề thi bật tính năng shuffle_questions
    if ($practiceTest->shuffle_questions) {
        $questions = $questions->shuffle();
    }

    // 2. Áp dụng xáo trộn các phương án trả lời A, B, C, D của từng câu hỏi
    if ($practiceTest->shuffle_options) {
        $questions->each(function ($question) {
            $question->setRelation('options', $question->options->shuffle());
        });
    }

    return view('learning.launch', [
        'practiceTest' => $practiceTest,
        'questions' => $questions,
        'durationSeconds' => $practiceTest->duration_minutes * 60,
        'totalQuestions' => $questions->count()
    ]);
}
CODE;
$gen->addCodeBlock($codeShuffle);

$gen->addHeading3("3.3.3. Logic chấm điểm tự động, lưu vết bài thi và phân bổ điểm thưởng Sao");
$gen->addParagraph("Đoạn mã xử lý trong `AttemptController.php` chấm điểm thời gian thực, lưu kết quả và tích lũy điểm thưởng Gamification cho học sinh:", 'both', 26, false, false, '000000', 60, 60, 720);

$codeGrading = <<<'CODE'
// app/Http/Controllers/AttemptController.php (Chấm điểm & Thưởng Sao)
public function store(Request $request, PracticeTest $practiceTest)
{
    $user = $request->user();
    $answers = $request->input('answers', []); // Mảng [question_id => selected_option_id]
    $timeSpent = (int)$request->input('time_spent_seconds', 0);

    $questions = $practiceTest->questions()->with('options')->get();
    $correctCount = 0;
    $totalCount = $questions->count();

    // Đối soát đáp án người dùng gửi lên với phương án đúng trong CSDL
    foreach ($questions as $question) {
        $selectedOptionId = $answers[$question->id] ?? null;
        $correctOption = $question->options->firstWhere('is_correct', true);

        if ($correctOption && (int)$selectedOptionId === (int)$correctOption->id) {
            $correctCount++;
        }
    }

    // Quy đổi điểm số theo thang chuẩn 1000 điểm của IC3
    $score = $totalCount > 0 ? (int)round(($correctCount / $totalCount) * 1000) : 0;
    $isPassed = $score >= $practiceTest->pass_score;

    // Lưu vết lượt thi vào bảng test_attempts
    $attempt = TestAttempt::create([
        'user_id' => $user->id,
        'practice_test_id' => $practiceTest->id,
        'score' => $score,
        'correct_answers' => $correctCount,
        'total_questions' => $totalCount,
        'duration_seconds' => $timeSpent,
        'completed_at' => now()
    ]);

    // Nếu đạt chuẩn, cộng thưởng 10 Sao và ghi nhận nhật ký giao dịch
    if ($isPassed) {
        $user->increment('reward_stars', 10);
        GameTransaction::create([
            'user_id' => $user->id,
            'type' => 'reward_test',
            'stars_change' => 10,
            'time_seconds_change' => 0,
            'description' => "Thưởng vượt qua bài thi: {$practiceTest->name}"
        ]);
    }

    return response()->json([
        'status' => 'success',
        'score' => $score,
        'correct' => $correctCount,
        'total' => $totalCount,
        'is_passed' => $isPassed,
        'earned_stars' => $isPassed ? 10 : 0
    ]);
}
CODE;
$gen->addCodeBlock($codeGrading);

$gen->addHeading2("3.4. Kiểm thử chất lượng hệ thống (10 Test Cases)");
$gen->addParagraph("Nhóm sinh viên đã tiến hành kiểm thử hộp đen (Black-box Testing) toàn diện trên 10 ca kiểm thử nghiệp vụ trọng yếu của hệ thống. Kết quả thực nghiệm cho thấy toàn bộ các kịch bản đều đạt trạng thái PASS:", 'both', 26, false, false, '000000', 60, 60, 720);

$testCases = [
    ['TC01', 'Đăng nhập tài khoản Admin', 'Nhập email: admin@ic3.test, pass: 123456', 'Hệ thống chuyển hướng vào trang Dashboard quản trị', 'Chuyển hướng đúng /quan-tri, hiển thị đầy đủ menu Admin', 'PASS'],
    ['TC02', 'Đăng nhập sai mật khẩu', 'Nhập email: admin@ic3.test, pass: sai_pass', 'Báo lỗi thông tin đăng nhập không hợp lệ, không vào hệ thống', 'Hiển thị thông báo lỗi màu đỏ, giữ lại email đã nhập', 'PASS'],
    ['TC03', 'Tạo tài khoản học sinh mới', 'Nhập Tên: Nguyễn Văn A, Mã: HS099, Lớp: 3A1', 'Thêm mới bản ghi vào CSDL, học sinh đăng nhập được', 'Bản ghi xuất hiện trong bảng `users`, mã HS099 hoạt động', 'PASS'],
    ['TC04', 'Thực thi lệnh trích xuất 509 câu hỏi', 'Chạy `php artisan ic3:import-questions`', 'Giải nén zip, đọc manifest và nạp đủ 509 câu vào MySQL', 'Lệnh báo success, kiểm tra CSDL có đủ 509 câu hỏi', 'PASS'],
    ['TC05', 'Soạn thảo câu hỏi mới trong Studio', 'Tạo câu hỏi trắc nghiệm kèm ảnh và 4 đáp án', 'Lưu thành công vào `questions` và `question_options`', 'Câu hỏi mới xuất hiện trong danh sách đề thi', 'PASS'],
    ['TC06', 'Khởi chạy phòng thi ảo kiểm tra xáo trộn', 'Học sinh mở bài luyện thi có bật shuffle', 'Thứ tự câu hỏi và thứ tự A-B-C-D ngẫu nhiên', 'Hai học sinh cùng mở 1 đề nhận thứ tự hoàn toàn khác nhau', 'PASS'],
    ['TC07', 'Nộp bài thi đạt điểm chuẩn (Pass)', 'Học sinh trả lời đúng 100% các câu hỏi', 'Chấm 1000 điểm, báo Pass, cộng 10 Sao thưởng vào tài khoản', 'Cộng đúng 10 sao, lịch sử ghi nhận kết quả Đạt', 'PASS'],
    ['TC08', 'Tự động khóa bài khi hết giờ', 'Đồng hồ đếm ngược chạy về mốc 00:00', 'Giao diện tự động gửi bài thi lên máy chủ để chấm', 'Máy chủ tiếp nhận và chấm điểm bình thường, khóa form', 'PASS'],
    ['TC09', 'Đổi Sao thưởng lấy thời gian chơi game', 'Học sinh có 50 Sao, đổi gói 10 Sao (15 phút chơi)', 'Tài khoản còn 40 Sao, cộng thêm 900 giây chơi game', 'Số Sao giảm 10, mở khóa trò chơi, đồng hồ game chạy đúng', 'PASS'],
    ['TC10', 'Xuất báo cáo kết quả thi ra file CSV', 'Admin bấm nút Xuất báo cáo trên Dashboard', 'Tải xuống tệp CSV định dạng chuẩn UTF-8 chứa điểm thi', 'Tải file csv mở bằng Microsoft Excel hiển thị tiếng Việt chuẩn', 'PASS'],
];
$gen->addTable(['MÃ TC', 'TÊN CA KIỂM THỬ', 'DỮ LIỆU ĐẦU VÀO', 'KẾT QUẢ MONG ĐỢI', 'KẾT QUẢ THỰC TẾ', 'KẾT QUẢ'], $testCases, [900, 1800, 1800, 1800, 2071, 700]);
$gen->addPageBreak();

// -------------------------------------------------------------
// CHƯƠNG 4. KẾT LUẬN VÀ HƯỚNG PHÁT TRIỂN
// -------------------------------------------------------------
$gen->addHeading1("Chương 4. KẾT LUẬN VÀ HƯỚNG PHÁT TRIỂN");

$gen->addHeading2("4.1. Đánh giá kết quả đồ án");

$gen->addHeading3("4.1.1. Các kết quả chức năng đã đạt được");
$gen->addParagraph("Sau 10 tuần nỗ lực nghiên cứu, phân tích thiết kế và lập trình thực nghiệm dưới sự chỉ dẫn tận tình của Thầy Huỳnh Luân, nhóm sinh viên đã hoàn thành xuất sắc toàn bộ các mục tiêu đề ra cho đồ án:", 'both', 26, false, false, '000000', 60, 60, 720);
$gen->addBullet("Hoàn thiện hệ thống CSDL quan hệ 12 bảng chuẩn hóa trong MySQL, lưu trữ độc lập và bảo mật toàn bộ dữ liệu người dùng, lớp học, đề thi và lịch sử thi cử.");
$gen->addBullet("Trích xuất và chuẩn hóa thành công 509 câu hỏi và 35 bộ đề thi từ các gói iSpring đóng gói gốc vào CSDL, giải phóng triệt để tài nguyên rác trên thư mục web server.");
$gen->addBullet("Xây dựng hoàn chỉnh trình làm bài thi trực tuyến tương tác (Native IC3 Quiz Player) với đồng hồ đếm ngược, thuật toán xáo trộn câu hỏi/đáp án và chấm điểm tự động.");
$gen->addBullet("Hiện thực hóa thành công mô hình Gamification thưởng Sao đổi thời gian minigame giáo dục và bảng điều khiển Phụ huynh giám sát tiến độ học tập minh bạch.");
$gen->addBullet("Hệ thống đạt tốc độ tải trang nhanh, giao diện Responsive thân thiện với lứa tuổi học sinh tiểu học và vượt qua 10/10 kịch bản kiểm thử chất lượng.");

$gen->addHeading3("4.1.2. Các kỹ năng chuyên môn tích lũy");
$gen->addParagraph("Thông qua quá trình thực hiện đồ án, các thành viên trong nhóm đã tích lũy được nhiều kinh nghiệm thực tiễn vô giá:", 'both', 26, false, false, '000000', 60, 60, 720);
$gen->addBullet("Nắm vững phương pháp phân tích thiết kế hệ thống thông tin theo chuẩn UML (Use Case, Activity, Sequence, ERD).");
$gen->addBullet("Làm chủ kiến trúc MVC, Eloquent ORM, Blade Engine, Middleware và Artisan Console trong Laravel Framework.");
$gen->addBullet("Nâng cao kỹ năng thiết kế cơ sở dữ liệu quan hệ, tối ưu hóa câu truy vấn và sử dụng Database Transactions.");
$gen->addBullet("Rèn luyện kỹ năng làm việc nhóm, phân chia công việc theo tiến độ và xử lý xung đột mã nguồn trên hệ thống quản lý phiên bản Git.");

$gen->addHeading3("4.1.3. Những hạn chế và thiếu sót còn tồn tại");
$gen->addParagraph("Bên cạnh những kết quả tích cực đã đạt được, hệ thống vẫn còn một số điểm hạn chế do rào cản thời gian nghiên cứu:", 'both', 26, false, false, '000000', 60, 60, 720);
$gen->addBullet("Ngân hàng câu hỏi chủ yếu tập trung vào dạng trắc nghiệm chọn phương án (Single/Multiple Choice), chưa hỗ trợ các dạng tương tác kéo thả phức tạp hoặc điền khuyết trực tiếp.");
$gen->addBullet("Phần thi thực hành chứng chỉ tin học văn phòng MOS mới dừng lại ở các câu hỏi mô phỏng tình huống trên ảnh, chưa tích hợp được add-in chấm điểm trực tiếp trên ứng dụng Microsoft Office máy trạm.");

$gen->addHeading2("4.2. Định hướng và lộ trình phát triển trong tương lai");
$gen->addParagraph("Để phát triển IC3 Quest thành một nền tảng luyện thi thương mại và phục vụ mở rộng cho các trường học trên toàn địa bàn thành phố, nhóm định hướng các giải pháp nâng cấp sau:", 'both', 26, false, false, '000000', 60, 60, 720);
$gen->addBullet("Tích hợp Trí tuệ nhân tạo (AI Tutor): Sử dụng mô hình AI để tự động phân tích điểm yếu của học sinh qua các bài thi, đưa ra lời giải thích chi tiết và tự động sinh đề thi ôn tập trọng tâm vào các kỹ năng còn yếu.");
$gen->addBullet("Phát triển Module chấm thi thực hành MOS: Xây dựng ứng dụng Desktop Client (hoặc tiện ích mở rộng Office Add-in) cho phép học sinh tải file bài tập Word/Excel thực tế về máy, thao tác và nộp lại file để hệ thống tự động kiểm tra định dạng và chấm điểm.");
$gen->addBullet("Xây dựng ứng dụng di động đa nền tảng (Mobile App): Ứng dụng Flutter/React Native dành riêng cho phụ huynh và học sinh để nhận thông báo đẩy tức thì về kết quả thi và thời khóa biểu ôn tập.");
$gen->addPageBreak();

// -------------------------------------------------------------
// PHỤ LỤC & TÀI LIỆU THAM KHẢO
// -------------------------------------------------------------
$gen->addHeading1("PHỤ LỤC");

$gen->addHeading2("Phụ lục A: Nội dung tệp cấu hình môi trường hệ thống (.env)");
$envContent = <<<'ENV'
APP_NAME="IC3 Quest"
APP_ENV=local
APP_KEY=base64:5HoHYVPhPjHMPVsAvC9zuO7p4BuDtZujZMXkNGzfBr8=
APP_DEBUG=true
APP_URL=http://localhost/MOS/public

LOG_CHANNEL=stack
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=MOS
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=database
SESSION_LIFETIME=120
ENV;
$gen->addCodeBlock($envContent);

$gen->addHeading2("Phụ lục B: Hướng dẫn chi tiết cài đặt và vận hành hệ thống cục bộ");
$gen->addParagraph("Quy trình các bước thiết lập và chạy thử nghiệm đồ án trên máy chủ Laragon:", 'both', 26, false, false, '000000', 60, 60, 720);
$gen->addBullet("Bước 1: Sao chép thư mục dự án `MOS` vào thư mục gốc `C:/laragon/www/MOS`.");
$gen->addBullet("Bước 2: Khởi động phần mềm Laragon, bật các dịch vụ Apache và MySQL.");
$gen->addBullet("Bước 3: Mở trình quản lý HeidiSQL hoặc phpMyAdmin, tạo mới một CSDL tên là `MOS` với bảng mã `utf8mb4_unicode_ci`.");
$gen->addBullet("Bước 4: Mở Terminal tại thư mục dự án, chạy lệnh khởi tạo cấu trúc bảng và nạp dữ liệu mẫu:");
$gen->addCodeBlock("php artisan migrate:fresh --seed");
$gen->addBullet("Bước 5: Chạy lệnh tự động trích xuất và nạp 509 câu hỏi IC3 vào CSDL:");
$gen->addCodeBlock("php artisan ic3:import-questions");
$gen->addBullet("Bước 6: Truy cập hệ thống trên trình duyệt qua địa chỉ: `http://localhost/MOS/public/dang-nhap`.");

$gen->addHeading2("Phụ lục C: Danh sách tài khoản thử nghiệm hệ thống");
$gen->addTable(
    ['VAI TRÒ TRUY CẬP', 'TÀI KHOẢN (EMAIL / MÃ HS)', 'MẬT KHẨU', 'GHI CHÚ QUYỀN HẠN'],
    [
        ['Quản trị viên (Tổng)', 'admin@ic3.test', '123456', 'Toàn quyền quản trị hệ thống, Studio câu hỏi'],
        ['Giáo viên Khối 3', 'teacher@ic3.test', '123456', 'Quản lý Lớp 3A1, phụ trách bộ đề Khối 3'],
        ['Giáo viên Khối 4', 'teacher4@ic3.test', '123456', 'Quản lý Lớp 4A1, phụ trách bộ đề Khối 3 & 4'],
        ['Giáo viên Khối 5', 'teacher5@ic3.test', '123456', 'Quản lý Lớp 5A1, phụ trách bộ đề Khối 5'],
        ['Học sinh Khối 3', 'hs001@student.ic3.local (HS001)', '123456', 'Học sinh An Nhiên (Lớp 3A1), thi đề Khối 3'],
        ['Học sinh Khối 4', 'hs002@student.ic3.local (HS002)', '123456', 'Học sinh Bảo Nam (Lớp 4A1), thi đề Khối 4'],
        ['Học sinh Khối 5', 'hs003@student.ic3.local (HS003)', '123456', 'Học sinh Minh Khôi (Lớp 5A1), thi đề Khối 5'],
    ],
    [2200, 3200, 1200, 2471]
);
$gen->addPageBreak();

// TÀI LIỆU THAM KHẢO
$gen->addHeading1("DANH MỤC TÀI LIỆU THAM KHẢO");
$gen->addParagraph("Tiếng Việt:", 'both', 26, true, false, '002060', 60, 40, 0);
$gen->addParagraph("[1] Huỳnh Luân (2024), Giáo trình Lập trình Web với PHP và Hệ quản trị Cơ sở dữ liệu MySQL, Lưu hành nội bộ, Khoa CNTT, Trường Cao đẳng Công nghệ Thông tin TP.HCM.", 'both', 26, false, false, '000000', 40, 40, 720);
$gen->addParagraph("[2] Đặng Văn Đức (2018), Phân tích thiết kế hệ thống hướng đối tượng bằng UML, Nhà xuất bản Khoa học và Kỹ thuật, Hà Nội.", 'both', 26, false, false, '000000', 40, 40, 720);
$gen->addParagraph("[3] IIG Việt Nam (2023), Hướng dẫn chuẩn kỹ năng số quốc tế IC3 Digital Literacy Master Standard GS6, Nhà xuất bản Thông tin và Truyền thông.", 'both', 26, false, false, '000000', 40, 60, 720);

$gen->addParagraph("Tiếng Anh:", 'both', 26, true, false, '002060', 60, 40, 0);
$gen->addParagraph("[4] Taylor Otwell (2024), Laravel Documentation – The PHP Framework for Web Artisans, Truy cập tại: https://laravel.com/docs.", 'both', 26, false, false, '000000', 40, 40, 720);
$gen->addParagraph("[5] Matt Stauffer (2019), Laravel: Up & Running: A Framework for Building Modern PHP Apps (2nd Edition), O'Reilly Media, ISBN: 978-1492041214.", 'both', 26, false, false, '000000', 40, 40, 720);
$gen->addParagraph("[6] Certiport Inc. (2022), IC3 Spark Certification Exam Objectives & Standards, Pearson VUE Business, USA.", 'both', 26, false, false, '000000', 40, 60, 720);

// BUILD WORD XML AND OUTPUT FILES
$docXml = $gen->getDocumentXml();

// 1. Tạo file Word .docx bằng cách clone vip_php.docx
$templateDocx = "E:/Desktop/trikun/CD_php/vip_php.docx";
$outputDocx1 = "E:/Desktop/trikun/CD_php/BAO_CAO_DO_AN_IC3_QUEST.docx";
$outputDocx2 = "c:/laragon/www/MOS/BAO_CAO_DO_AN_IC3_QUEST.docx";

if (!copy($templateDocx, $outputDocx1)) {
    echo "Lỗi: Không thể sao chép template docx vào E:\Desktop\trikun\CD_php!\n";
} else {
    $zip = new ZipArchive();
    if ($zip->open($outputDocx1) === true) {
        $zip->addFromString('word/document.xml', $docXml);
        
        // Cập nhật Header: Đồ án IC3 Quest | GVHD Thầy Huỳnh Luân
        $headerXml = '<?xml version="1.0" encoding="utf-8"?><w:hdr xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:tbl><w:tblPr><w:tblStyle w:val="TableNormal"/><w:tblW w:w="9071" w:type="dxa"/><w:tblBorders><w:bottom w:val="single" w:sz="6" w:space="0" w:color="002060"/></w:tblBorders></w:tblPr><w:tblGrid><w:gridCol w:w="5500"/><w:gridCol w:w="3571"/></w:tblGrid><w:tr><w:tc><w:p><w:pPr><w:jc w:val="left"/></w:pPr><w:r><w:rPr><w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman"/><w:sz w:val="18"/><w:i/><w:color w:val="595959"/></w:rPr><w:t>ĐỒ ÁN MÔN HỌC: HỆ THỐNG LUYỆN THI IC3 QUEST &amp; MOS</w:t></w:r></w:p></w:tc><w:tc><w:p><w:pPr><w:jc w:val="right"/></w:pPr><w:r><w:rPr><w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman"/><w:sz w:val="18"/><w:b/><w:color w:val="002060"/></w:rPr><w:t>GVHD: Thầy Huỳnh Luân</w:t></w:r></w:p></w:tc></w:tr></w:tbl></w:hdr>';
        $zip->addFromString('word/header.xml', $headerXml);

        // Cập nhật Footer: SVTH: Lê Minh Trí - Âu Lê Thành Tài - Võ Trung Kiều Diễm | Trang PAGE
        $footerXml = '<?xml version="1.0" encoding="utf-8"?><w:ftr xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:tbl><w:tblPr><w:tblStyle w:val="TableNormal"/><w:tblW w:w="9071" w:type="dxa"/><w:tblBorders><w:top w:val="single" w:sz="6" w:space="0" w:color="CCCCCC"/></w:tblBorders></w:tblPr><w:tblGrid><w:gridCol w:w="7000"/><w:gridCol w:w="2071"/></w:tblGrid><w:tr><w:tc><w:p><w:pPr><w:jc w:val="left"/></w:pPr><w:r><w:rPr><w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman"/><w:sz w:val="18"/><w:i/><w:color w:val="595959"/></w:rPr><w:t>SVTH: Lê Minh Trí – Âu Lê Thành Tài – Võ Trung Kiều Diễm</w:t></w:r></w:p></w:tc><w:tc><w:p><w:pPr><w:jc w:val="right"/></w:pPr><w:r><w:rPr><w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman"/><w:sz w:val="18"/><w:color w:val="595959"/></w:rPr><w:t xml:space="preserve">Trang </w:t></w:r><w:fldSimple w:instr="PAGE"><w:r><w:rPr><w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman"/><w:sz w:val="18"/><w:b/><w:color w:val="002060"/></w:rPr><w:t>1</w:t></w:r></w:fldSimple></w:p></w:tc></w:tr></w:tbl></w:ftr>';
        $zip->addFromString('word/footer.xml', $footerXml);

        // Cập nhật Metadata docProps
        $coreXml = '<?xml version="1.0" encoding="utf-8"?><coreProperties xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns="http://schemas.openxmlformats.org/package/2006/metadata/core-properties"><dc:title>Báo cáo Đồ án Môn học – Hệ thống Luyện thi IC3 Quest &amp; MOS</dc:title><dc:creator>Lê Minh Trí, Âu Lê Thành Tài, Võ Trung Kiều Diễm</dc:creator><dc:subject>Chuyên đề PHP - GVHD Thầy Huỳnh Luân</dc:subject><lastModifiedBy>Lê Minh Trí</lastModifiedBy><revision>1</revision></coreProperties>';
        $zip->addFromString('docProps/core.xml', $coreXml);

        $zip->close();
        echo "Đã tạo thành công file Word tại: $outputDocx1\n";

        copy($outputDocx1, $outputDocx2);
        echo "Đã sao chép file Word vào mã nguồn: $outputDocx2\n";
    } else {
        echo "Lỗi khi mở file zip docx!\n";
    }
}

echo "Hoàn thành toàn bộ quy trình sinh tài liệu báo cáo!\n";
