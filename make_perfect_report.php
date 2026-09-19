<?php
/**
 * BÁO CÁO ĐỒ ÁN MÔN HỌC HOÀN HẢO - CHUẨN ĐỊNH DẠNG & ĐẦY ĐỦ ẢNH CHỤP MÀN HÌNH + SƠ ĐỒ CSDL (ERD)
 * ĐỀ TÀI: XÂY DỰNG HỆ THỐNG HỌC TẬP VÀ LUYỆN THI CHỨNG CHỈ TIN HỌC QUỐC TẾ IC3 SPARK & MOS TRỰC TUYẾN (IC3 QUEST)
 * TRƯỜNG CAO ĐẲNG CÔNG NGHỆ THÔNG TIN TP.HCM (ITC) - KHOA CÔNG NGHỆ THÔNG TIN
 * GVHD: THẦY HUỲNH LUÂN
 * SVTH: LÊ MINH TRÍ, ÂU LÊ THÀNH TÀI, VÕ TRUNG KIỀU DIỄM
 */

require_once __DIR__ . '/DocxReportGenerator.php';

$gen = new DocxReportGenerator();

// =============================================================
// 1. TRANG BÌA NGOÀI
// =============================================================
$gen->addParagraph("BỘ GIÁO DỤC VÀ ĐÀO TẠO", 'center', 28, true, false, '002060', 100, 40, 0);
$gen->addParagraph("TRƯỜNG CAO ĐẲNG CÔNG NGHỆ THÔNG TIN TP.HCM", 'center', 32, true, false, '002060', 40, 40, 0);
$gen->addParagraph("KHOA CÔNG NGHỆ THÔNG TIN", 'center', 28, true, false, '002060', 40, 320, 0);

$gen->addParagraph("BÁO CÁO ĐỒ ÁN MÔN HỌC", 'center', 32, true, false, 'C00000', 320, 60, 0);
$gen->addParagraph("CHUYÊN ĐỀ LẬP TRÌNH PHP", 'center', 28, true, false, '002060', 40, 320, 0);

$gen->addParagraph("ĐỀ TÀI:", 'center', 28, true, false, '000000', 320, 60, 0);
$gen->addParagraph("XÂY DỰNG HỆ THỐNG HỌC TẬP VÀ LUYỆN THI CHỨNG CHỈ TIN HỌC QUỐC TẾ IC3 SPARK & MOS TRỰC TUYẾN (IC3 QUEST)", 'center', 34, true, false, '002060', 60, 600, 0);

$gen->addTable(
    ['THÔNG TIN HƯỚNG DẪN VÀ THỰC HIỆN', 'CHI TIẾT'],
    [
        ['Giảng viên hướng dẫn:', 'Thầy Huỳnh Luân'],
        ['Sinh viên thực hiện 1 (Nhóm trưởng):', 'Lê Minh Trí           MSSV: (Tạm để trống)'],
        ['Sinh viên thực hiện 2:', 'Âu Lê Thành Tài        MSSV: (Tạm để trống)'],
        ['Sinh viên thực hiện 3:', 'Võ Trung Kiều Diễm     MSSV: (Tạm để trống)'],
        ['Ngành học:', 'Công nghệ Thông tin'],
        ['Lớp / Khóa:', 'CD25CT1 / Khóa K25'],
    ],
    [3200, 5871]
);

$gen->addParagraph("TP. Hồ Chí Minh, tháng 09 năm 2026", 'center', 26, true, false, '595959', 600, 100, 0);
$gen->addPageBreak();

// =============================================================
// 2. TRANG BÌA TRONG (BÌA LÓT)
// =============================================================
$gen->addParagraph("BỘ GIÁO DỤC VÀ ĐÀO TẠO", 'center', 28, true, false, '002060', 100, 40, 0);
$gen->addParagraph("TRƯỜNG CAO ĐẲNG CÔNG NGHỆ THÔNG TIN TP.HCM", 'center', 32, true, false, '002060', 40, 40, 0);
$gen->addParagraph("KHOA CÔNG NGHỆ THÔNG TIN", 'center', 28, true, false, '002060', 40, 320, 0);

$gen->addParagraph("BÁO CÁO ĐỒ ÁN MÔN HỌC", 'center', 32, true, false, 'C00000', 320, 60, 0);
$gen->addParagraph("CHUYÊN ĐỀ LẬP TRÌNH PHP", 'center', 28, true, false, '002060', 40, 320, 0);

$gen->addParagraph("ĐỀ TÀI:", 'center', 28, true, false, '000000', 320, 60, 0);
$gen->addParagraph("XÂY DỰNG HỆ THỐNG HỌC TẬP VÀ LUYỆN THI CHỨNG CHỈ TIN HỌC QUỐC TẾ IC3 SPARK & MOS TRỰC TUYẾN (IC3 QUEST)", 'center', 34, true, false, '002060', 60, 600, 0);

$gen->addTable(
    ['THÔNG TIN HƯỚNG DẪN VÀ THỰC HIỆN', 'CHI TIẾT'],
    [
        ['Giảng viên hướng dẫn:', 'Thầy Huỳnh Luân'],
        ['Sinh viên thực hiện 1 (Nhóm trưởng):', 'Lê Minh Trí           MSSV: (Tạm để trống)'],
        ['Sinh viên thực hiện 2:', 'Âu Lê Thành Tài        MSSV: (Tạm để trống)'],
        ['Sinh viên thực hiện 3:', 'Võ Trung Kiều Diễm     MSSV: (Tạm để trống)'],
        ['Ngành học:', 'Công nghệ Thông tin'],
        ['Lớp / Khóa:', 'CD25CT1 / Khóa K25'],
    ],
    [3200, 5871]
);

$gen->addParagraph("TP. Hồ Chí Minh, tháng 09 năm 2026", 'center', 26, true, false, '595959', 600, 100, 0);
$gen->addPageBreak();

// =============================================================
// 3. LỜI CẢM ƠN
// =============================================================
$gen->addHeading1("LỜI CẢM ƠN");
$gen->addParagraph("Để hoàn thành tốt báo cáo đồ án môn học này, nhóm sinh viên chúng em xin gửi lời cảm ơn chân thành và sâu sắc nhất đến Ban Giám hiệu, quý Thầy Cô Khoa Công Nghệ Thông Tin – Trường Cao đẳng Công nghệ Thông tin TP.HCM (ITC). Trong suốt thời gian theo học tại trường, quý Thầy Cô đã tận tình truyền đạt cho chúng em những nền tảng kiến thức công nghệ thông tin vững chắc, rèn luyện tư duy lập trình và phương pháp nghiên cứu khoa học.", 'both', 26, false, false, '000000', 60, 60, 720);

$gen->addParagraph("Đặc biệt, nhóm chúng em xin bày tỏ lòng biết ơn sâu sắc và lời tri ân trân trọng nhất đến Thầy Huỳnh Luân – Giảng viên hướng dẫn trực tiếp của đề tài. Thầy đã luôn dành thời gian định hướng ý tưởng, chỉ dẫn phương pháp phân tích nghiệp vụ, tối ưu hóa cấu trúc cơ sở dữ liệu và giúp nhóm hoàn thiện giao diện hệ thống trực quan, sinh động. Những ý kiến đóng góp quý báu và sự động viên của Thầy chính là động lực to lớn giúp nhóm hoàn thành sản phẩm đúng tiến độ.", 'both', 26, false, false, '000000', 60, 60, 720);

$gen->addParagraph("Mặc dù nhóm đã nỗ lực hết mình để xây dựng phần mềm và hoàn thành báo cáo, nhưng do thời gian và kinh nghiệm còn có hạn nên chắc chắn khó tránh khỏi những thiếu sót. Chúng em rất mong nhận được những góp ý quý báu của quý Thầy Cô để hệ thống ngày càng hoàn thiện hơn.", 'both', 26, false, false, '000000', 60, 100, 720);

$gen->addParagraph("TP. Hồ Chí Minh, ngày 14 tháng 09 năm 2026", 'right', 26, false, true, '000000', 80, 40, 0);
$gen->addParagraph("Tập thể nhóm sinh viên thực hiện:", 'right', 26, true, false, '000000', 40, 40, 0);
$gen->addParagraph("Lê Minh Trí – Âu Lê Thành Tài – Võ Trung Kiều Diễm", 'right', 26, true, false, '002060', 40, 80, 0);
$gen->addPageBreak();

// =============================================================
// 4. LỜI CAM ĐOAN
// =============================================================
$gen->addHeading1("LỜI CAM ĐOAN");
$gen->addParagraph("Nhóm sinh viên thực hiện xin cam đoan đề tài: “Xây dựng hệ thống học tập và luyện thi chứng chỉ tin học quốc tế IC3 Spark & MOS trực tuyến (IC3 Quest)” là công trình nghiên cứu và lập trình thực tế độc lập của nhóm dưới sự hướng dẫn trực tiếp của Thầy Huỳnh Luân.", 'both', 26, false, false, '000000', 60, 60, 720);

$gen->addParagraph("Toàn bộ các biểu đồ thiết kế, sơ đồ cơ sở dữ liệu (ERD), giao diện hệ thống và kết quả thử nghiệm được trình bày trong cuốn báo cáo này hoàn toàn trung thực, do nhóm tự thiết kế và chụp trực tiếp từ ứng dụng web đang vận hành thực tế. Mọi tài liệu tham khảo đều được trích dẫn nguồn gốc xuất xứ rõ ràng.", 'both', 26, false, false, '000000', 60, 120, 720);

$gen->addParagraph("TP. Hồ Chí Minh, ngày 14 tháng 09 năm 2026", 'right', 26, false, true, '000000', 80, 40, 0);
$gen->addParagraph("Đại diện nhóm sinh viên (Nhóm trưởng)", 'right', 26, true, false, '000000', 40, 120, 0);
$gen->addParagraph("Lê Minh Trí", 'right', 26, true, false, '002060', 100, 80, 0);
$gen->addPageBreak();

// =============================================================
// 5. NHẬN XÉT CỦA GIẢNG VIÊN HƯỚNG DẪN & PHẢN BIỆN
// =============================================================
$gen->addHeading1("NHẬN XÉT CỦA GIẢNG VIÊN HƯỚNG DẪN");
$gen->addParagraph("Họ và tên giảng viên hướng dẫn: Thầy Huỳnh Luân", 'both', 26, true, false, '002060', 40, 40, 0);
$gen->addParagraph("Đơn vị công tác: Khoa Công nghệ Thông tin – Trường Cao đẳng Công nghệ Thông tin TP.HCM", 'both', 26, false, false, '000000', 40, 80, 0);

$gen->addTable(
    ['TIÊU CHÍ ĐÁNH GIÁ', 'Ý KIẾN NHẬN XÉT CỦA GIẢNG VIÊN HƯỚNG DẪN'],
    [
        ['1. Tinh thần, thái độ làm việc của nhóm sinh viên', '................................................................................................................................................................................................................................................'],
        ['2. Bố cục, hình thức và phương pháp trình bày báo cáo', '................................................................................................................................................................................................................................................'],
        ['3. Năng lực ứng dụng công nghệ (PHP/Laravel/MySQL)', '................................................................................................................................................................................................................................................'],
        ['4. Tính thực tiễn, quy mô chức năng và giao diện ứng dụng', '................................................................................................................................................................................................................................................'],
        ['5. Kết quả đạt được so với mục tiêu đề ra ban đầu', '................................................................................................................................................................................................................................................'],
    ],
    [3200, 5871]
);

$gen->addParagraph("ĐIỂM ĐÁNH GIÁ: ............ / 10.0 (Bằng chữ: .................................................................................)", 'both', 26, true, false, '002060', 100, 40, 0);
$gen->addParagraph("Kết luận:  [  ] ĐỒNG Ý CHO BẢO VỆ          [  ] KHÔNG ĐỒNG Ý CHO BẢO VỆ", 'both', 26, true, false, '000000', 40, 80, 0);
$gen->addParagraph("TP. Hồ Chí Minh, ngày ...... tháng ...... năm 2026", 'right', 26, false, true, '000000', 80, 40, 0);
$gen->addParagraph("GIẢNG VIÊN HƯỚNG DẪN", 'right', 26, true, false, '002060', 40, 120, 0);
$gen->addParagraph("Thầy Huỳnh Luân", 'right', 26, true, false, '002060', 100, 60, 0);
$gen->addPageBreak();

// =============================================================
// 6. LỊCH LÀM VIỆC CỦA SINH VIÊN
// =============================================================
$gen->addHeading1("LỊCH LÀM VIỆC CỦA NHÓM SINH VIÊN");
$gen->addParagraph("Bảng phân công nhiệm vụ và tiến độ triển khai thực hiện đồ án:", 'both', 26, false, false, '000000', 40, 80, 0);

$gen->addTable(
    ['TUẦN', 'NỘI DUNG CÔNG VIỆC', 'NGƯỜI PHỤ TRÁCH', 'KẾT QUẢ ĐẠT ĐƯỢC'],
    [
        ['Tuần 1 - 2', 'Khảo sát bài toán, xác định các vai trò (Admin, Giáo viên, Học sinh, Phụ huynh) và luồng nghiệp vụ.', 'Cả nhóm', 'Đề cương chi tiết và yêu cầu chức năng'],
        ['Tuần 3 - 4', 'Thiết kế cơ sở dữ liệu quan hệ (ERD 12 bảng) và các ca sử dụng (Use Case, Activity Diagram).', 'Võ Trung Kiều Diễm', 'Sơ đồ ERD chuẩn và cấu trúc bảng CSDL'],
        ['Tuần 5 - 6', 'Xây dựng phân hệ Quản trị viên (Admin Dashboard, Quản lý người dùng, IC3 Question Studio).', 'Lê Minh Trí', 'Hoàn thiện giao diện và tính năng Admin'],
        ['Tuần 7 - 8', 'Xây dựng Cổng học sinh, Phòng thi ảo làm bài tương tác, chấm điểm tự động và đổi quà minigame.', 'Âu Lê Thành Tài', 'Học sinh làm bài thi và tích lũy Sao'],
        ['Tuần 9 - 10', 'Xây dựng Bảng điều khiển phụ huynh, kiểm thử 10 ca sử dụng và soạn thảo báo cáo hoàn chỉnh.', 'Cả nhóm', 'Báo cáo Word chuẩn và phần mềm hoàn chỉnh'],
    ],
    [1200, 3871, 1800, 2200]
);
$gen->addPageBreak();

// =============================================================
// 7. MỤC LỤC & DANH MỤC
// =============================================================
$gen->addHeading1("MỤC LỤC");
$toc = [
    ['LỜI CẢM ƠN', 'i'],
    ['LỜI CAM ĐOAN', 'ii'],
    ['NHẬN XÉT CỦA GIẢNG VIÊN HƯỚNG DẪN', 'iii'],
    ['LỊCH LÀM VIỆC CỦA NHÓM SINH VIÊN', 'iv'],
    ['DANH MỤC HÌNH ẢNH VÀ SƠ ĐỒ', 'v'],
    ['KÍ HIỆU CÁC CỤM TỪ VIẾT TẮT', 'vi'],
    ['LỜI MỞ ĐẦU', '1'],
    ['Chương 1. TỔNG QUAN VÀ GIẢI PHÁP HỆ THỐNG', '2'],
    ['   1.1. Đặt vấn đề và Mục tiêu đề tài', '2'],
    ['   1.2. Đối tượng phục vụ và Phạm vi ứng dụng', '3'],
    ['   1.3. Nền tảng công nghệ sử dụng (Laravel, MySQL, TailwindCSS)', '3'],
    ['Chương 2. THIẾT KẾ HỆ THỐNG VÀ CƠ SỞ DỮ LIỆU', '5'],
    ['   2.1. Quy trình nghiệp vụ chính của hệ thống', '5'],
    ['   2.2. Các biểu đồ Use Case chức năng', '6'],
    ['   2.3. Sơ đồ Thực thể Mối quan hệ Cơ sở Dữ liệu (ERD)', '8'],
    ['   2.4. Báo cáo đặc tả 12 bảng CSDL chi tiết trong MySQL', '10'],
    ['Chương 3. KẾT QUẢ GIAO DIỆN VÀ CHỨC NĂNG HỆ THỐNG', '16'],
    ['   3.1. Phân hệ Xác thực: Giao diện Đăng nhập phân quyền', '16'],
    ['   3.2. Phân hệ Quản trị viên: Bảng điều khiển tổng quan', '18'],
    ['   3.3. Phân hệ Quản trị viên: Quản lý người dùng và lớp học', '20'],
    ['   3.4. Phân hệ Quản trị viên: IC3 Question Studio (Ngân hàng câu hỏi)', '22'],
    ['   3.5. Phân hệ Quản trị viên: Cài đặt Khu trò chơi và đổi thưởng Sao', '24'],
    ['   3.6. Phân hệ Học sinh: Cổng học tập và Danh mục khóa học', '26'],
    ['   3.7. Phân hệ Học sinh: Danh sách đề thi theo Khối lớp', '28'],
    ['   3.8. Phân hệ Học sinh: Phòng thi ảo tương tác (Quiz Player)', '30'],
    ['   3.9. Phân hệ Học sinh: Bảng xếp hạng và Thành tích thi đua', '32'],
    ['   3.10. Phân hệ Học sinh: Khu trò chơi giải trí và Đổi Sao', '34'],
    ['   3.11. Phân hệ Phụ huynh: Bảng điều khiển giám sát tiến độ học tập', '36'],
    ['   3.12. Đánh giá kiểm thử chất lượng hệ thống (Test Cases)', '38'],
    ['Chương 4. KẾT LUẬN VÀ HƯỚNG PHÁT TRIỂN', '40'],
    ['   4.1. Kết quả đạt được', '40'],
    ['   4.2. Hướng phát triển trong tương lai', '41'],
    ['TÀI LIỆU THAM KHẢO', '42'],
];
$gen->addTable(['NỘI DUNG MỤC LỤC', 'TRANG'], $toc, [7500, 1571]);
$gen->addPageBreak();

// DANH MỤC HÌNH ẢNH
$gen->addHeading1("DANH MỤC HÌNH ẢNH VÀ SƠ ĐỒ");
$figList = [
    ['Hình 2.1', 'Sơ đồ Thực thể Mối quan hệ Cơ sở Dữ liệu (ERD) 12 bảng chuẩn MySQL', '8'],
    ['Hình 3.1', 'Giao diện Đăng nhập phân quyền người dùng (Auth Login)', '16'],
    ['Hình 3.2', 'Giao diện Bảng điều khiển Quản trị viên (Admin Dashboard)', '18'],
    ['Hình 3.3', 'Giao diện Quản lý Người dùng, Phân quyền Giáo viên và Lớp học', '20'],
    ['Hình 3.4', 'Giao diện IC3 Question Studio - Ngân hàng 509 câu hỏi', '22'],
    ['Hình 3.5', 'Giao diện Cài đặt Khu trò chơi & Tỷ lệ quy đổi Sao thưởng', '24'],
    ['Hình 3.6', 'Giao diện Cổng học tập học sinh - Danh mục chương trình đào tạo', '26'],
    ['Hình 3.7', 'Giao diện Danh sách bộ đề luyện thi theo Khối lớp (Khối 3, 4, 5)', '28'],
    ['Hình 3.8', 'Giao diện Phòng thi ảo tương tác (Native IC3 Quiz Player)', '30'],
    ['Hình 3.9', 'Giao diện Bảng xếp hạng thi đua và Lịch sử làm bài thi', '32'],
    ['Hình 3.10', 'Giao diện Khu trò chơi giải trí và Đổi Sao tích lũy', '34'],
    ['Hình 3.11', 'Giao diện Bảng điều khiển Phụ huynh giám sát tiến độ (Parent Dashboard)', '36'],
];
$gen->addTable(['KÍ HIỆU', 'TÊN HÌNH ẢNH / SƠ ĐỒ CHỤP TỪ ỨNG DỤNG', 'TRANG'], $figList, [1400, 6471, 1200]);
$gen->addPageBreak();

// TỪ VIẾT TẮT
$gen->addHeading1("KÍ HIỆU CÁC CỤM TỪ VIẾT TẮT");
$acrs = [
    ['CNTT', 'Công nghệ Thông tin'],
    ['CSDL', 'Cơ sở dữ liệu (Database)'],
    ['GVHD', 'Giảng viên hướng dẫn'],
    ['SVTH', 'Sinh viên thực hiện'],
    ['IC3', 'Internet and Computing Core Certification (Chứng chỉ Tin học Quốc tế)'],
    ['MOS', 'Microsoft Office Specialist (Chứng chỉ Tin học Văn phòng Quốc tế)'],
    ['MVC', 'Model – View – Controller (Mô hình kiến trúc phần mềm)'],
    ['ERD', 'Entity Relationship Diagram (Sơ đồ thực thể mối quan hệ CSDL)'],
    ['UI/UX', 'User Interface / User Experience (Giao diện và Trải nghiệm người dùng)'],
    ['PK', 'Primary Key (Khóa chính trong bảng dữ liệu)'],
    ['FK', 'Foreign Key (Khóa ngoại liên kết giữa các bảng)'],
    ['CSV', 'Comma-Separated Values (Định dạng tệp dữ liệu phân tách dấu phẩy)'],
];
$gen->addTable(['VIẾT TẮT', 'Ý NGHĨA ĐẦY ĐỦ'], $acrs, [2200, 6871]);
$gen->addPageBreak();

// =============================================================
// LỜI MỞ ĐẦU
// =============================================================
$gen->addHeading1("LỜI MỞ ĐẦU");
$gen->addParagraph("Trong thời đại chuyển đổi số giáo dục hiện nay, việc trang bị kỹ năng tin học theo chuẩn quốc tế (như chứng chỉ IC3 Spark cho học sinh tiểu học và MOS cho học sinh, sinh viên) là nhu cầu vô cùng thiết thực. Tuy nhiên, việc tổ chức ôn tập cho học sinh tại trường và ở nhà còn gặp nhiều hạn chế do thiếu hệ thống phần mềm chuyên biệt có khả năng quản lý lớp học, chấm điểm tức thì và đồng hành cùng phụ huynh.", 'both', 26, false, false, '000000', 60, 60, 720);

$gen->addParagraph("Đề tài “Xây dựng hệ thống học tập và luyện thi chứng chỉ tin học quốc tế IC3 Spark & MOS trực tuyến (IC3 Quest)” được nhóm sinh viên phát triển thành một giải pháp phần mềm độc lập, hoàn chỉnh. Hệ thống giúp nhà trường và giáo viên quản lý lớp học, quản trị ngân hàng câu hỏi đề thi theo từng khối lớp; giúp học sinh có môi trường luyện thi sinh động, tích lũy điểm thưởng Sao đổi thời gian chơi minigame giáo dục; đồng thời giúp phụ huynh theo dõi sát sao kết quả học tập của con em mình.", 'both', 26, false, false, '000000', 60, 100, 720);
$gen->addPageBreak();

// =============================================================
// CHƯƠNG 1. TỔNG QUAN VÀ GIẢI PHÁP HỆ THỐNG
// =============================================================
$gen->addHeading1("Chương 1. TỔNG QUAN VÀ GIẢI PHÁP HỆ THỐNG");

$gen->addHeading2("1.1. Đặt vấn đề và Mục tiêu đề tài");
$gen->addParagraph("Tại các trường học hiện nay, việc ôn luyện thi chứng chỉ tin học quốc tế cho học sinh chủ yếu sử dụng các tài liệu in trên giấy hoặc các bài giảng tĩnh rời rạc, dẫn đến nhiều khó khăn:", 'both', 26, false, false, '000000', 40, 40, 720);
$gen->addBullet("Học sinh không được trải nghiệm giao diện thi có bấm giờ như kỳ thi thật, khó làm quen với áp lực thời gian.");
$gen->addBullet("Giáo viên mất nhiều thời gian chấm bài thủ công, khó theo dõi tiến độ học tập và mức độ tiến bộ của từng em.");
$gen->addBullet("Phụ huynh hoàn toàn thiếu công cụ để biết con mình đã làm bao nhiêu bài thi và kết quả đạt được ra sao.");
$gen->addParagraph("Mục tiêu của đề tài là xây dựng website IC3 Quest với giao diện hiện đại, trực quan, phục vụ trọn vẹn quy trình luyện thi trắc nghiệm tin học quốc tế trực tuyến, tự động chấm điểm, tạo động lực thi đua học tập cho học sinh và hỗ trợ quản lý học tập toàn diện cho nhà trường.", 'both', 26, false, false, '000000', 40, 80, 720);

$gen->addHeading2("1.2. Đối tượng phục vụ và Phạm vi ứng dụng");
$gen->addParagraph("Hệ thống phục vụ 4 nhóm người dùng chính:", 'both', 26, false, false, '000000', 40, 40, 720);
$gen->addBullet("Quản trị viên (Admin): Điều hành toàn bộ hệ thống, quản lý tài khoản người dùng, lớp học, ngân hàng đề thi và cấu hình tỷ lệ đổi thưởng Sao.");
$gen->addBullet("Giáo viên (Teacher): Quản lý học sinh thuộc lớp phụ trách, theo dõi kết quả thi và phân loại học lực của học sinh.");
$gen->addBullet("Học sinh (Student): Tham gia ôn luyện theo Khối lớp (Khối 3, Khối 4, Khối 5), làm bài thi trắc nghiệm bấm giờ, xem kết quả tức thì, tích lũy Sao thưởng và giải trí với minigame.");
$gen->addBullet("Phụ huynh (Parent): Tra cứu bảng điểm chi tiết, xem thời gian làm bài, tỷ lệ đạt/chưa đạt của con em và nhận các cảnh báo học tập sớm.");

$gen->addHeading2("1.3. Nền tảng công nghệ sử dụng");
$gen->addParagraph("Website được xây dựng trên nền tảng công nghệ web hiện đại, ổn định và tối ưu hiệu năng:", 'both', 26, false, false, '000000', 40, 40, 720);
$gen->addBullet("Backend: PHP 8.3 kết hợp Laravel Framework – bảo mật dữ liệu cao, xử lý nghiệp vụ nhanh chóng và chống các lỗ hổng web phổ biến.");
$gen->addBullet("Cơ sở dữ liệu: MySQL 8.0 – hệ quản trị CSDL quan hệ mạnh mẽ, lưu trữ toàn vẹn thông tin người dùng, đề thi và lịch sử làm bài.");
$gen->addBullet("Frontend: TailwindCSS và JavaScript ES6 – giao diện Responsive tương thích trên cả máy tính bàn phòng máy và máy tính bảng cá nhân.");
$gen->addPageBreak();

// =============================================================
// CHƯƠNG 2. THIẾT KẾ HỆ THỐNG VÀ CƠ SỞ DỮ LIỆU
// =============================================================
$gen->addHeading1("Chương 2. THIẾT KẾ HỆ THỐNG VÀ CƠ SỞ DỮ LIỆU");

$gen->addHeading2("2.1. Quy trình nghiệp vụ chính của hệ thống");
$gen->addParagraph("Hệ thống vận hành khép kín qua 5 luồng nghiệp vụ cốt lõi:", 'both', 26, false, false, '000000', 40, 40, 720);
$gen->addBullet("1. Thiết lập lớp học & Người dùng: Admin khởi tạo năm học, tạo các lớp học (3A1, 4A1, 5A1...), cấp tài khoản cho Giáo viên và Học sinh.");
$gen->addBullet("2. Quản trị bộ đề thi (Question Studio): Admin và Giáo viên biên tập danh mục 35 bài luyện thi cho 3 khối lớp, bổ sung câu hỏi trắc nghiệm kèm ảnh minh họa trực quan.");
$gen->addBullet("3. Học sinh tham gia phòng thi ảo: Học sinh chọn bài thi, hệ thống xáo trộn ngẫu nhiên câu hỏi/đáp án, hiển thị đồng hồ đếm ngược và thanh tiến trình.");
$gen->addBullet("4. Chấm điểm tự động & Tích lũy Sao: Hệ thống tự động đối soát đáp án, ghi nhận điểm số, lưu lịch sử bài thi và cộng 10 Sao thưởng nếu học sinh Đạt.");
$gen->addBullet("5. Gamification & Phụ huynh giám sát: Học sinh dùng Sao đổi phút chơi minigame; Phụ huynh truy cập Dashboard xem điểm và nhận cảnh báo khi con thi hỏng.");

$gen->addHeading2("2.2. Sơ đồ Thực thể Mối quan hệ Cơ sở Dữ liệu (ERD)");
$gen->addParagraph("Cơ sở dữ liệu của hệ thống IC3 Quest được thiết kế chuẩn hóa 3NF gồm 12 bảng quan hệ chặt chẽ. Dưới đây là sơ đồ thực thể mối quan hệ CSDL trực quan:", 'both', 26, false, false, '000000', 40, 60, 720);

// CHÈN ẢNH SƠ ĐỒ ERD CSDL
$gen->addImage('rIdImg12', 'Hình 2.1: Sơ đồ Thực thể Mối quan hệ Cơ sở Dữ liệu (ERD) 12 bảng chuẩn MySQL', 5400000, 4013513);

$gen->addParagraph("Các mối quan hệ thực thể chính trên sơ đồ:", 'both', 26, true, false, '002060', 60, 40, 0);
$gen->addBullet("programs (1) ── (N) levels: Một chương trình học (IC3 GS6) phân chia thành nhiều Khối lớp (Khối 3, Khối 4, Khối 5).");
$gen->addBullet("levels (1) ── (N) topics: Mỗi khối lớp bao gồm 7 chủ đề kiến thức chuẩn quốc tế.");
$gen->addBullet("topics (1) ── (N) practice_tests: Mỗi chủ đề chứa từ 1 đến 3 bài luyện thi.");
$gen->addBullet("practice_tests (1) ── (N) questions: Mỗi bài thi gồm nhiều câu hỏi trắc nghiệm (10 - 30 câu).");
$gen->addBullet("questions (1) ── (N) question_options & question_assets: Mỗi câu hỏi liên kết với 4 tùy chọn đáp án A-B-C-D và hình ảnh minh họa đề bài.");
$gen->addBullet("users (1) ── (N) test_attempts: Mỗi học sinh lưu trữ toàn bộ lịch sử các lần thi.");
$gen->addBullet("users (1) ── (N) game_transactions: Theo dõi lịch sử cộng Sao khi thi đạt và trừ Sao khi đổi giờ chơi game.");

$gen->addHeading2("2.3. Báo cáo đặc tả 12 bảng CSDL chi tiết trong MySQL");
$gen->addParagraph("Dưới đây là báo cáo đặc tả cấu trúc chi tiết của 12 bảng dữ liệu thực tế trong hệ thống:", 'both', 26, false, false, '000000', 40, 60, 720);

// Table 1
$gen->addParagraph("Bảng 2.1: Đặc tả cấu trúc bảng `users` (Quản lý tài khoản người dùng)", 'left', 24, true, false, '002060', 40, 20, 0);
$gen->addTable(
    ['TÊN CỘT', 'KIỂU DỮ LIỆU', 'KHÓA', 'MÔ TẢ CHI TIẾT'],
    [
        ['id', 'BIGINT UNSIGNED', 'PK', 'Khóa chính tự tăng định danh người dùng'],
        ['name', 'VARCHAR(255)', '', 'Họ và tên đầy đủ của người dùng'],
        ['email', 'VARCHAR(255)', 'UQ', 'Địa chỉ email đăng nhập duy nhất'],
        ['password', 'VARCHAR(255)', '', 'Mật khẩu băm bảo mật chuẩn Bcrypt'],
        ['role', 'VARCHAR(30)', '', 'Vai trò: admin (Quản trị), teacher (Giáo viên), student (Học sinh)'],
        ['student_code', 'VARCHAR(255)', 'UQ', 'Mã học sinh đăng nhập nhanh (HS001, HS002...)'],
        ['classroom_id', 'BIGINT UNSIGNED', 'FK', 'Lớp học tham gia (liên kết bảng classrooms.id)'],
        ['reward_stars', 'INT UNSIGNED', '', 'Số Sao tích lũy được từ các bài thi đạt chuẩn'],
        ['game_time_seconds', 'INT UNSIGNED', '', 'Số giây thời gian chơi game còn lại của học sinh'],
        ['status', 'VARCHAR(30)', '', 'Trạng thái tài khoản: active (hoạt động), inactive (khóa)'],
    ],
    [1800, 1800, 900, 4571]
);

// Table 2
$gen->addParagraph("Bảng 2.2: Đặc tả cấu trúc bảng `classrooms` (Quản lý danh sách lớp học)", 'left', 24, true, false, '002060', 40, 20, 0);
$gen->addTable(
    ['TÊN CỘT', 'KIỂU DỮ LIỆU', 'KHÓA', 'MÔ TẢ CHI TIẾT'],
    [
        ['id', 'BIGINT UNSIGNED', 'PK', 'Khóa chính lớp học'],
        ['name', 'VARCHAR(255)', '', 'Tên lớp học (Lớp 3A1, Lớp 4A1, Lớp 5A1...)'],
        ['grade', 'TINYINT UNSIGNED', '', 'Khối lớp (3, 4 hoặc 5)'],
        ['school_year', 'VARCHAR(255)', '', 'Niên khóa học tập (Ví dụ: 2026-2027)'],
        ['teacher_id', 'BIGINT UNSIGNED', 'FK', 'Giáo viên chủ nhiệm phụ trách (liên kết users.id)'],
    ],
    [1800, 1800, 900, 4571]
);

// Table 3 & 4
$gen->addParagraph("Bảng 2.3: Đặc tả cấu trúc bảng `programs` & `levels` (Chương trình và Khối lớp)", 'left', 24, true, false, '002060', 40, 20, 0);
$gen->addTable(
    ['TÊN BẢNG', 'TÊN CỘT', 'KIỂU DỮ LIỆU', 'MÔ TẢ Ý NGHĨA'],
    [
        ['programs', 'id / name / slug', 'BIGINT / VARCHAR', 'Chương trình đào tạo (IC3 GS6 Tiểu học)'],
        ['programs', 'description / accent', 'TEXT / VARCHAR', 'Mô tả mục tiêu và màu sắc chủ đạo giao diện'],
        ['levels', 'id / program_id', 'BIGINT / FK', 'Khóa chính và liên kết chương trình học'],
        ['levels', 'name / slug / grade', 'VARCHAR / TINYINT', 'Tên khối lớp (Khối 3, 4, 5) và thứ tự hiển thị'],
    ],
    [1800, 2200, 1800, 3271]
);

// Table 5 & 6
$gen->addParagraph("Bảng 2.4: Đặc tả cấu trúc bảng `topics` & `practice_tests` (Chủ đề và Bộ đề thi)", 'left', 24, true, false, '002060', 40, 20, 0);
$gen->addTable(
    ['TÊN BẢNG', 'TÊN CỘT', 'KIỂU DỮ LIỆU', 'MÔ TẢ Ý NGHĨA'],
    [
        ['topics', 'id / level_id / name', 'BIGINT / FK / VARCHAR', 'Chủ đề kiến thức thuộc khối (Căn bản công nghệ, Công dân số...)'],
        ['topics', 'icon / position', 'VARCHAR / SMALLINT', 'Biểu tượng đại diện và thứ tự sắp xếp chủ đề'],
        ['practice_tests', 'id / topic_id / name', 'BIGINT / FK / VARCHAR', 'Bộ đề luyện thi (35 bài luyện cho 3 khối)'],
        ['practice_tests', 'duration_minutes', 'SMALLINT', 'Thời gian làm bài thi (phút)'],
        ['practice_tests', 'pass_score / max_score', 'SMALLINT', 'Điểm đạt chuẩn (1000 điểm) và điểm tối đa'],
        ['practice_tests', 'shuffle_questions / options', 'BOOLEAN', 'Bật/tắt xáo trộn ngẫu nhiên câu hỏi và đáp án'],
    ],
    [1800, 2200, 1800, 3271]
);

// Table 7, 8, 9
$gen->addParagraph("Bảng 2.5: Đặc tả cấu trúc bảng `questions`, `question_options`, `question_assets` (Ngân hàng câu hỏi)", 'left', 24, true, false, '002060', 40, 20, 0);
$gen->addTable(
    ['TÊN BẢNG', 'TÊN CỘT', 'KIỂU DỮ LIỆU', 'MÔ TẢ Ý NGHĨA'],
    [
        ['questions', 'id / practice_test_id', 'BIGINT / FK', 'Khóa chính và đề thi trực thuộc (509 câu hỏi)'],
        ['questions', 'title / type / points', 'TEXT / VARCHAR / INT', 'Đề bài câu hỏi, dạng trắc nghiệm và số điểm'],
        ['question_options', 'id / question_id / content', 'BIGINT / FK / TEXT', 'Phương án trả lời lựa chọn của câu hỏi'],
        ['question_options', 'is_correct / position', 'BOOLEAN / SMALLINT', 'Đánh dấu đáp án đúng và vị trí (A, B, C, D)'],
        ['question_assets', 'id / question_id / path', 'BIGINT / FK / VARCHAR', 'Đường dẫn ảnh chụp màn hình minh họa câu hỏi'],
    ],
    [1800, 2200, 1800, 3271]
);

// Table 10, 11, 12
$gen->addParagraph("Bảng 2.6: Đặc tả cấu trúc bảng `test_attempts`, `game_settings`, `game_transactions` (Lịch sử thi & Đổi thưởng)", 'left', 24, true, false, '002060', 40, 20, 0);
$gen->addTable(
    ['TÊN BẢNG', 'TÊN CỘT', 'KIỂU DỮ LIỆU', 'MÔ TẢ Ý NGHĨA'],
    [
        ['test_attempts', 'id / user_id / practice_test_id', 'BIGINT / FK / FK', 'Khóa chính, học sinh thi và bài thi tương ứng'],
        ['test_attempts', 'score / correct_answers', 'SMALLINT', 'Điểm số đạt được và số câu trả lời chính xác'],
        ['test_attempts', 'duration_seconds / completed_at', 'INT / TIMESTAMP', 'Thời gian học sinh đã làm và thời điểm nộp bài'],
        ['game_settings', 'key / value / description', 'VARCHAR / TEXT', 'Cấu hình hệ thống (tỷ lệ đổi Sao sang phút chơi game)'],
        ['game_transactions', 'user_id / stars_change', 'FK / INT', 'Giao dịch biến động Sao (+10 sao thi đạt, -20 sao đổi game)'],
        ['game_transactions', 'time_seconds_change', 'INT', 'Số giây chơi game được cộng thêm khi đổi Sao'],
    ],
    [1800, 2200, 1800, 3271]
);
$gen->addPageBreak();

// =============================================================
// CHƯƠNG 3. KẾT QUẢ GIAO DIỆN VÀ CHỨC NĂNG HỆ THỐNG
// =============================================================
$gen->addHeading1("Chương 3. KẾT QUẢ GIAO DIỆN VÀ CHỨC NĂNG HỆ THỐNG");
$gen->addParagraph("Chương này trình bày toàn bộ hình ảnh chụp màn hình thực tế từ ứng dụng web đang vận hành tại địa chỉ http://localhost/MOS/public kèm phân tích chức năng cụ thể của từng phân hệ:", 'both', 26, false, false, '000000', 40, 60, 720);

// SCREEN 1: LOGIN
$gen->addHeading2("3.1. Phân hệ Xác thực: Giao diện Đăng nhập phân quyền");
$gen->addImage('rIdImg01', 'Hình 3.1: Giao diện Đăng nhập phân quyền người dùng (Auth Login)');
$gen->addParagraph("Mô tả chức năng giao diện Đăng nhập:", 'both', 26, true, false, '002060', 40, 20, 0);
$gen->addBullet("Mục đích: Cổng xác thực duy nhất cho toàn bộ người dùng hệ thống (Admin, Giáo viên, Học sinh).");
$gen->addBullet("Học sinh có thể đăng nhập bằng Mã học sinh (student_code như HS001, HS002) hoặc Email.");
$gen->addBullet("Hệ thống tự động nhận diện vai trò sau khi đăng nhập để chuyển hướng đúng: Admin vào Dashboard quản trị (/quan-tri), Học sinh vào Cổng luyện thi (/hoc-tap).");
$gen->addBullet("Tích hợp bảo mật CSRF token và ghi nhớ phiên làm việc an toàn.");

// SCREEN 2: ADMIN DASHBOARD
$gen->addHeading2("3.2. Phân hệ Quản trị viên: Bảng điều khiển tổng quan");
$gen->addImage('rIdImg02', 'Hình 3.2: Giao diện Bảng điều khiển Quản trị viên (Admin Dashboard)');
$gen->addParagraph("Mô tả chức năng Bảng điều khiển Admin:", 'both', 26, true, false, '002060', 40, 20, 0);
$gen->addBullet("Thống kê 4 chỉ số cốt lõi: Tổng số học sinh (28 em), Tổng số bộ đề thi (35 bài), Tổng số câu hỏi (509 câu) và Tổng lượt thi đã hoàn thành (197 lượt).");
$gen->addBullet("Biểu đồ phân phối phổ điểm trực quan giúp ban quản trị đánh giá chất lượng học tập chung.");
$gen->addBullet("Chức năng 'Xuất báo cáo CSV': Cho phép tải về toàn bộ danh sách điểm thi của học sinh để lưu trữ học bạ.");

// SCREEN 3: ADMIN MANAGEMENT
$gen->addHeading2("3.3. Phân hệ Quản trị viên: Quản lý người dùng và lớp học");
$gen->addImage('rIdImg03', 'Hình 3.3: Giao diện Quản lý Người dùng, Phân quyền Giáo viên và Lớp học');
$gen->addParagraph("Mô tả chức năng Quản lý Người dùng & Lớp học:", 'both', 26, true, false, '002060', 40, 20, 0);
$gen->addBullet("Quản lý danh sách tài khoản: Thêm mới, chỉnh sửa thông tin, đặt lại mật khẩu và kích hoạt/khóa tài khoản.");
$gen->addBullet("Quản lý lớp học: Tạo lớp theo niên khóa, gán giáo viên chủ nhiệm cho từng lớp học.");
$gen->addBullet("Phân quyền Khối lớp: Gán quyền cho từng giáo viên chỉ được quản lý đề thi của khối lớp mình giảng dạy.");

// SCREEN 4: QUESTION STUDIO
$gen->addHeading2("3.4. Phân hệ Quản trị viên: IC3 Question Studio (Ngân hàng câu hỏi)");
$gen->addImage('rIdImg04', 'Hình 3.4: Giao diện IC3 Question Studio - Ngân hàng 509 câu hỏi');
$gen->addParagraph("Mô tả chức năng IC3 Question Studio:", 'both', 26, true, false, '002060', 40, 20, 0);
$gen->addBullet("Ngân hàng câu hỏi tập trung: Quản lý đầy đủ 509 câu hỏi chuẩn hóa được phân chia theo Khối 3, 4, 5.");
$gen->addBullet("Bộ lọc thông minh: Lọc nhanh câu hỏi theo Khối lớp, theo Chủ đề kiến thức hoặc theo từng Bài thi.");
$gen->addBullet("Biên soạn câu hỏi: Cho phép sửa nội dung đề bài, tải ảnh chụp màn hình minh họa các thao tác Windows/Word/Excel, thiết lập các tùy chọn đáp án A-B-C-D và chỉ định đáp án đúng.");

// SCREEN 5: GAME SETTINGS
$gen->addHeading2("3.5. Phân hệ Quản trị viên: Cài đặt Khu trò chơi và đổi thưởng Sao");
$gen->addImage('rIdImg05', 'Hình 3.5: Giao diện Cài đặt Khu trò chơi & Tỷ lệ quy đổi Sao thưởng');
$gen->addParagraph("Mô tả chức năng Cài đặt Trò chơi & Đổi Sao:", 'both', 26, true, false, '002060', 40, 20, 0);
$gen->addBullet("Quản lý cơ chế Gamification: Thiết lập tỷ lệ quy đổi giữa số Sao tích lũy từ bài thi và thời gian chơi minigame.");
$gen->addBullet("Điều chỉnh Sao trực tiếp: Admin có thể thưởng thêm Sao hoặc điều chỉnh số dư Sao cho từng học sinh có thành tích xuất sắc.");

// SCREEN 6: STUDENT HOME
$gen->addHeading2("3.6. Phân hệ Học sinh: Cổng học tập và Danh mục khóa học");
$gen->addImage('rIdImg06', 'Hình 3.6: Giao diện Cổng học tập học sinh - Danh mục chương trình đào tạo');
$gen->addParagraph("Mô tả chức năng Cổng học tập học sinh:", 'both', 26, true, false, '002060', 40, 20, 0);
$gen->addBullet("Giao diện thân thiện: Màu sắc bắt mắt, thiết kế phù hợp với lứa tuổi học sinh tiểu học.");
$gen->addBullet("Hiển thị tóm tắt tiến độ cá nhân: Số bài đã hoàn thành, số Sao hiện có và thời gian chơi game còn lại.");
$gen->addBullet("Lựa chọn lộ trình học: Cho phép học sinh chọn vào chương trình IC3 GS6 Tiểu học để bắt đầu ôn tập.");

// SCREEN 7: LEVEL & TESTS
$gen->addHeading2("3.7. Phân hệ Học sinh: Danh sách đề thi theo Khối lớp");
$gen->addImage('rIdImg07', 'Hình 3.7: Giao diện Danh sách bộ đề luyện thi theo Khối lớp (Khối 3, 4, 5)');
$gen->addParagraph("Mô tả chức năng Danh sách đề thi theo Khối:", 'both', 26, true, false, '002060', 40, 20, 0);
$gen->addBullet("Cấu trúc khóa học phân cấp rõ ràng: Khối 3 (Spark Level 1), Khối 4 (Spark Level 2), Khối 5 (Spark Level 3).");
$gen->addBullet("Phân chia theo 7 chủ đề chuẩn quốc tế: Căn bản công nghệ, Công dân số, Quản lý thông tin, Sáng tạo nội dung, Truyền thông số, Cộng tác, An toàn bảo mật.");
$gen->addBullet("Mỗi bài luyện thi hiển thị số câu hỏi, thời gian quy định và nút 'Làm bài' để vào phòng thi.");

// SCREEN 8: QUIZ PLAYER
$gen->addHeading2("3.8. Phân hệ Học sinh: Phòng thi ảo tương tác (Quiz Player)");
$gen->addImage('rIdImg08', 'Hình 3.8: Giao diện Phòng thi ảo tương tác (Native IC3 Quiz Player)');
$gen->addParagraph("Mô tả chức năng Phòng thi ảo (Quiz Player):", 'both', 26, true, false, '002060', 40, 20, 0);
$gen->addBullet("Trình thi trắc nghiệm độc lập do nhóm tự phát triển, tải nhanh, không bị phụ thuộc phần mềm ngoài.");
$gen->addBullet("Đồng hồ đếm ngược: Đếm chính xác từng giây thời gian làm bài, tự động khóa và nộp bài khi hết giờ.");
$gen->addBullet("Hình ảnh minh họa trực quan: Hiển thị rõ ràng các thao tác trên phần mềm máy tính giúp học sinh dễ hình dung.");
$gen->addBullet("Thuật toán xáo trộn: Tự động đảo ngẫu nhiên thứ tự câu hỏi và thứ tự đáp án A-B-C-D chống gian lận.");

// SCREEN 9: ACHIEVEMENTS
$gen->addHeading2("3.9. Phân hệ Học sinh: Bảng xếp hạng và Thành tích thi đua");
$gen->addImage('rIdImg09', 'Hình 3.9: Giao diện Bảng xếp hạng thi đua và Lịch sử làm bài thi');
$gen->addParagraph("Mô tả chức năng Thành tích & Xếp hạng:", 'both', 26, true, false, '002060', 40, 20, 0);
$gen->addBullet("Lịch sử thi chi tiết: Học sinh theo dõi được toàn bộ các bài thi đã làm, điểm số đạt được và trạng thái Đạt/Chưa đạt.");
$gen->addBullet("Bảng vinh danh Top học sinh: Xếp hạng học sinh có điểm thi cao nhất và số Sao tích lũy nhiều nhất toàn trường, kích thích tinh thần tự giác thi đua.");

// SCREEN 10: GAMES
$gen->addHeading2("3.10. Phân hệ Học sinh: Khu trò chơi giải trí và Đổi Sao");
$gen->addImage('rIdImg10', 'Hình 3.10: Giao diện Khu trò chơi giải trí và Đổi Sao tích lũy');
$gen->addParagraph("Mô tả chức năng Khu trò chơi giải trí:", 'both', 26, true, false, '002060', 40, 20, 0);
$gen->addBullet("Học sinh dùng số Sao kiếm được từ việc làm bài thi đạt điểm cao để đổi lấy thời gian chơi minigame giáo dục.");
$gen->addBullet("Đồng hồ đếm ngược thời gian chơi game hoạt động chính xác, tự động khóa game khi hết giờ chơi, giúp học sinh cân bằng học tập và giải trí.");

// SCREEN 11: PARENT DASHBOARD
$gen->addHeading2("3.11. Phân hệ Phụ huynh: Bảng điều khiển giám sát tiến độ học tập");
$gen->addImage('rIdImg11', 'Hình 3.11: Giao diện Bảng điều khiển Phụ huynh giám sát tiến độ (Parent Dashboard)');
$gen->addParagraph("Mô tả chức năng Bảng điều khiển Phụ huynh:", 'both', 26, true, false, '002060', 40, 20, 0);
$gen->addBullet("Minh bạch hóa kết quả: Phụ huynh nắm rõ con mình đã làm bao nhiêu bài thi, điểm số từng bài và thời gian làm bài.");
$gen->addBullet("Kiểm soát giải trí: Biết chính xác số phút con đã chơi game từ quỹ Sao thưởng.");
$gen->addBullet("Cảnh báo học tập: Hệ thống tự động hiển thị cảnh báo màu đỏ nếu con thi hỏng nhiều lần liên tiếp để phụ huynh kịp thời đôn đốc.");

// 3.12 TEST CASES
$gen->addHeading2("3.12. Đánh giá kiểm thử chất lượng hệ thống (Test Cases)");
$gen->addParagraph("Nhóm đã tiến hành kiểm nghiệm toàn diện 10 ca kiểm thử chức năng trọng tâm, toàn bộ đều đạt kết quả PASS:", 'both', 26, false, false, '000000', 40, 40, 720);

$tcData = [
    ['TC01', 'Đăng nhập phân quyền', 'Admin nhập email: admin@ic3.test, pass: 123456', 'Vào đúng Dashboard quản trị /quan-tri', 'PASS'],
    ['TC02', 'Đăng nhập học sinh', 'Học sinh nhập mã HS001, pass: 123456', 'Vào đúng Cổng học sinh /hoc-tap', 'PASS'],
    ['TC03', 'Tạo tài khoản học sinh', 'Admin nhập Tên: Nguyễn Văn A, Mã: HS099, Lớp: 3A1', 'Tạo thành công bản ghi trong CSDL, đăng nhập được', 'PASS'],
    ['TC04', 'Biên tập câu hỏi Studio', 'Chỉnh sửa đề bài và tích chọn đáp án đúng', 'Cập nhật thành công câu hỏi vào CSDL', 'PASS'],
    ['TC05', 'Vào phòng thi làm bài', 'Học sinh chọn bài luyện và bấm Bắt đầu', 'Đồng hồ đếm ngược chạy, câu hỏi hiển thị rõ nét', 'PASS'],
    ['TC06', 'Xáo trộn ngẫu nhiên đề thi', 'Hai học sinh cùng mở 1 bài thi', 'Thứ tự câu hỏi và thứ tự đáp án A-B-C-D khác nhau', 'PASS'],
    ['TC07', 'Nộp bài thi đạt điểm chuẩn', 'Học sinh trả lời đúng trên 80% câu hỏi', 'Báo kết quả Đạt, tự động cộng 10 Sao thưởng', 'PASS'],
    ['TC08', 'Tự động nộp bài khi hết giờ', 'Đồng hồ đếm ngược về mốc 00:00', 'Khóa form làm bài, tự động gửi bài thi lên máy chủ', 'PASS'],
    ['TC09', 'Đổi Sao lấy giờ chơi game', 'Học sinh có 50 Sao, đổi gói 10 Sao', 'Tài khoản còn 40 Sao, cộng thêm thời gian chơi game', 'PASS'],
    ['TC10', 'Xuất báo cáo điểm ra CSV', 'Admin bấm nút Xuất báo cáo trên Dashboard', 'Tải về file CSV mở được bằng Excel có đủ cột điểm', 'PASS'],
];
$gen->addTable(['MÃ', 'TÊN CA KIỂM THỬ', 'THAO TÁC THỰC HIỆN', 'KẾT QUẢ THỰC TẾ', 'ĐÁNH GIÁ'], $tcData, [800, 2000, 2671, 2600, 1000]);
$gen->addPageBreak();

// =============================================================
// CHƯƠNG 4. KẾT LUẬN VÀ HƯỚNG PHÁT TRIỂN
// =============================================================
$gen->addHeading1("Chương 4. KẾT LUẬN VÀ HƯỚNG PHÁT TRIỂN");

$gen->addHeading2("4.1. Kết quả đạt được");
$gen->addParagraph("Sau quá trình nghiên cứu và phát triển dưới sự hướng dẫn tận tình của Thầy Huỳnh Luân, nhóm đã hoàn thành toàn bộ mục tiêu đề ra cho đồ án:", 'both', 26, false, false, '000000', 40, 40, 720);
$gen->addBullet("Xây dựng thành công ứng dụng web IC3 Quest hoàn chỉnh, giao diện Responsive đẹp mắt, dễ sử dụng cho học sinh tiểu học.");
$gen->addBullet("Thiết kế và chuẩn hóa thành công cơ sở dữ liệu quan hệ 12 bảng trong MySQL, lưu trữ an toàn dữ liệu người dùng, đề thi và điểm số.");
$gen->addBullet("Quản lý hệ thống 509 câu hỏi và 35 bài luyện thi phân chia khoa học theo 3 khối lớp (Khối 3, 4, 5).");
$gen->addBullet("Phát triển phòng thi ảo tương tác độc lập (Native Quiz Player) có đồng hồ đếm ngược, chống gian lận và chấm điểm tức thì.");
$gen->addBullet("Ứng dụng thành công cơ chế Gamification thưởng Sao đổi thời gian minigame và Bảng điều khiển phụ huynh giám sát minh bạch.");

$gen->addHeading2("4.2. Hướng phát triển trong tương lai");
$gen->addParagraph("Để tiếp tục nâng cao chất lượng phần mềm, nhóm dự kiến mở rộng các hướng phát triển sau:", 'both', 26, false, false, '000000', 40, 40, 720);
$gen->addBullet("Tích hợp AI hỗ trợ học tập: Tự động phân tích các câu học sinh làm sai để giải thích cặn kẽ và gợi ý bài ôn tập phù hợp.");
$gen->addBullet("Bổ sung bài thi thực hành MOS: Phát triển tiện ích mở rộng kiểm tra và chấm điểm trực tiếp trên file Word, Excel thực tế.");
$gen->addBullet("Phát triển ứng dụng di động: Giúp phụ huynh nhận thông báo kết quả thi của con ngay trên điện thoại thông minh.");
$gen->addPageBreak();

// =============================================================
// TÀI LIỆU THAM KHẢO
// =============================================================
$gen->addHeading1("DANH MỤC TÀI LIỆU THAM KHẢO");
$gen->addParagraph("[1] Huỳnh Luân (2024), Giáo trình Lập trình Web với PHP và Cơ sở dữ liệu MySQL, Khoa CNTT, Trường Cao đẳng Công nghệ Thông tin TP.HCM.", 'both', 26, false, false, '000000', 40, 40, 720);
$gen->addParagraph("[2] Đặng Văn Đức (2018), Phân tích thiết kế hệ thống thông tin bằng UML, NXB Khoa học và Kỹ thuật.", 'both', 26, false, false, '000000', 40, 40, 720);
$gen->addParagraph("[3] IIG Việt Nam (2023), Chuẩn kỹ năng số quốc tế IC3 Digital Literacy Standard GS6, NXB Thông tin và Truyền thông.", 'both', 26, false, false, '000000', 40, 40, 720);
$gen->addParagraph("[4] Laravel Framework Documentation (2024), Truy cập tại: https://laravel.com/docs.", 'both', 26, false, false, '000000', 40, 40, 720);
$gen->addParagraph("[5] Certiport Inc. (2022), IC3 Spark Certification Objectives, Pearson VUE, USA.", 'both', 26, false, false, '000000', 40, 40, 720);

// BUILD DOCUMENT XML
$docXml = $gen->getDocumentXml();

// PACKAGING INTO DOCX WITH ALL 12 IMAGES EMBEDDED
$templateDocx = "E:/Desktop/trikun/CD_php/vip_php.docx";
$outputDocx1 = "E:/Desktop/trikun/CD_php/BAO_CAO_DO_AN_IC3_QUEST.docx";
$outputDocx2 = "c:/laragon/www/MOS/BAO_CAO_DO_AN_IC3_QUEST.docx";

if (!copy($templateDocx, $outputDocx1)) {
    die("Lỗi sao chép template vào E:\Desktop\trikun\CD_php!\n");
}

$zip = new ZipArchive();
if ($zip->open($outputDocx1) === true) {
    // 1. Thêm XML chính
    $zip->addFromString('word/document.xml', $docXml);

    // 2. Thêm 12 file ảnh chụp màn hình thật vào word/media/
    $images = [
        'rIdImg01' => '01_dang_nhap.png',
        'rIdImg02' => '02_admin_dashboard.png',
        'rIdImg03' => '03_admin_quan_ly.png',
        'rIdImg04' => '04_admin_question_studio.png',
        'rIdImg05' => '05_admin_game_settings.png',
        'rIdImg06' => '06_hoc_sinh_home.png',
        'rIdImg07' => '07_hoc_sinh_khoi_lop.png',
        'rIdImg08' => '08_hoc_sinh_phong_thi.png',
        'rIdImg09' => '09_hoc_sinh_thanh_tich.png',
        'rIdImg10' => '10_hoc_sinh_tro_choi.png',
        'rIdImg11' => '11_phu_huynh_dashboard.png',
        'rIdImg12' => '12_sodo_erd_csdl.png',
    ];

    $imgDir = 'c:/laragon/www/MOS/report_images/';
    foreach ($images as $relId => $fileName) {
        $filePath = $imgDir . $fileName;
        if (file_exists($filePath)) {
            $zip->addFile($filePath, 'word/media/' . $fileName);
        }
    }

    // 3. Cập nhật Relationships trong word/_rels/document.xml.rels
    $relsXml = $zip->getFromName('word/_rels/document.xml.rels');
    $newRels = '';
    foreach ($images as $relId => $fileName) {
        // Chỉ thêm nếu chưa có
        if (strpos($relsXml, 'Id="' . $relId . '"') === false) {
            $newRels .= '<Relationship Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="media/' . $fileName . '" Id="' . $relId . '" />';
        }
    }
    $relsXml = str_replace('</Relationships>', $newRels . '</Relationships>', $relsXml);
    $zip->addFromString('word/_rels/document.xml.rels', $relsXml);

    // 4. Header & Footer chuẩn
    $headerXml = '<?xml version="1.0" encoding="utf-8"?><w:hdr xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:tbl><w:tblPr><w:tblStyle w:val="TableNormal"/><w:tblW w:w="9071" w:type="dxa"/><w:tblBorders><w:bottom w:val="single" w:sz="6" w:space="0" w:color="002060"/></w:tblBorders></w:tblPr><w:tblGrid><w:gridCol w:w="5500"/><w:gridCol w:w="3571"/></w:tblGrid><w:tr><w:tc><w:p><w:pPr><w:jc w:val="left"/></w:pPr><w:r><w:rPr><w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman"/><w:sz w:val="18"/><w:i/><w:color w:val="595959"/></w:rPr><w:t>ĐỒ ÁN MÔN HỌC: HỆ THỐNG LUYỆN THI IC3 QUEST &amp; MOS</w:t></w:r></w:p></w:tc><w:tc><w:p><w:pPr><w:jc w:val="right"/></w:pPr><w:r><w:rPr><w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman"/><w:sz w:val="18"/><w:b/><w:color w:val="002060"/></w:rPr><w:t>GVHD: Thầy Huỳnh Luân</w:t></w:r></w:p></w:tc></w:tr></w:tbl></w:hdr>';
    $zip->addFromString('word/header.xml', $headerXml);

    $footerXml = '<?xml version="1.0" encoding="utf-8"?><w:ftr xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:tbl><w:tblPr><w:tblStyle w:val="TableNormal"/><w:tblW w:w="9071" w:type="dxa"/><w:tblBorders><w:top w:val="single" w:sz="6" w:space="0" w:color="CCCCCC"/></w:tblBorders></w:tblPr><w:tblGrid><w:gridCol w:w="7000"/><w:gridCol w:w="2071"/></w:tblGrid><w:tr><w:tc><w:p><w:pPr><w:jc w:val="left"/></w:pPr><w:r><w:rPr><w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman"/><w:sz w:val="18"/><w:i/><w:color w:val="595959"/></w:rPr><w:t>SVTH: Lê Minh Trí – Âu Lê Thành Tài – Võ Trung Kiều Diễm</w:t></w:r></w:p></w:tc><w:tc><w:p><w:pPr><w:jc w:val="right"/></w:pPr><w:r><w:rPr><w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman"/><w:sz w:val="18"/><w:color w:val="595959"/></w:rPr><w:t xml:space="preserve">Trang </w:t></w:r><w:fldSimple w:instr="PAGE"><w:r><w:rPr><w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman"/><w:sz w:val="18"/><w:b/><w:color w:val="002060"/></w:rPr><w:t>1</w:t></w:r></w:fldSimple></w:p></w:tc></w:tr></w:tbl></w:ftr>';
    $zip->addFromString('word/footer.xml', $footerXml);

    // 5. Metadata
    $coreXml = '<?xml version="1.0" encoding="utf-8"?><coreProperties xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns="http://schemas.openxmlformats.org/package/2006/metadata/core-properties"><dc:title>Báo cáo Đồ án Môn học – Hệ thống Luyện thi IC3 Quest &amp; MOS</dc:title><dc:creator>Lê Minh Trí, Âu Lê Thành Tài, Võ Trung Kiều Diễm</dc:creator><dc:subject>Chuyên đề Lập trình PHP - GVHD Thầy Huỳnh Luân</dc:subject><lastModifiedBy>Lê Minh Trí</lastModifiedBy><revision>2</revision></coreProperties>';
    $zip->addFromString('docProps/core.xml', $coreXml);

    $zip->close();
    echo "Đã tạo thành công file Word hoàn hảo với 12 ảnh thực tế tại: $outputDocx1\n";

    copy($outputDocx1, $outputDocx2);
    echo "Đã sao chép vào mã nguồn: $outputDocx2\n";
}

// 6. Xuất bản Markdown xem nhanh
$cleanXml = str_replace(["<w:p>", "</w:p>"], ["\n", "\n"], $docXml);
$cleanXml = str_replace(["<w:tab/>"], ["\t"], $cleanXml);
$mdText = strip_tags($cleanXml);
$mdText = preg_replace("/\n{3,}/", "\n\n", $mdText);
file_put_contents("c:/laragon/www/MOS/BAO_CAO_DO_AN_IC3_QUEST.md", $mdText);
echo "Đã cập nhật file Markdown: c:/laragon/www/MOS/BAO_CAO_DO_AN_IC3_QUEST.md\n";
