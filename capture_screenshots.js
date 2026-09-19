import puppeteer from 'puppeteer-core';
import fs from 'fs';
import path from 'path';

const edgePath = 'C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe';
const outputDir = 'c:\\laragon\\www\\MOS\\report_images';

if (!fs.existsSync(outputDir)) {
    fs.mkdirSync(outputDir, { recursive: true });
}

async function capture() {
    console.log("Launching Edge...");
    const browser = await puppeteer.launch({
        executablePath: edgePath,
        headless: true,
        defaultViewport: { width: 1366, height: 768 }
    });

    const page = await browser.newPage();

    // 1. Giao diện Đăng nhập
    console.log("Capturing 01_dang_nhap.png...");
    await page.goto('http://localhost/MOS/public/dang-nhap', { waitUntil: 'networkidle0' });
    await page.screenshot({ path: path.join(outputDir, '01_dang_nhap.png') });

    // 2. Đăng nhập Admin
    console.log("Logging in as Admin...");
    await page.type('input[name="login"]', 'admin@ic3.test');
    await page.type('input[name="password"]', '123456');
    await Promise.all([
        page.waitForNavigation({ waitUntil: 'networkidle0' }),
        page.click('button[type="submit"]')
    ]);

    // 2. Admin Dashboard
    console.log("Capturing 02_admin_dashboard.png...");
    await page.screenshot({ path: path.join(outputDir, '02_admin_dashboard.png') });

    // 3. Admin Quản lý
    console.log("Capturing 03_admin_quan_ly.png...");
    await page.goto('http://localhost/MOS/public/quan-tri/quan-ly', { waitUntil: 'networkidle0' });
    await page.screenshot({ path: path.join(outputDir, '03_admin_quan_ly.png') });

    // 4. Admin Question Studio
    console.log("Capturing 04_admin_question_studio.png...");
    await page.goto('http://localhost/MOS/public/quan-tri/bo-de-cau-hoi', { waitUntil: 'networkidle0' });
    await page.screenshot({ path: path.join(outputDir, '04_admin_question_studio.png') });

    // 5. Admin Game Settings
    console.log("Capturing 05_admin_game_settings.png...");
    await page.goto('http://localhost/MOS/public/quan-tri/tro-choi/cai-dat', { waitUntil: 'networkidle0' });
    await page.screenshot({ path: path.join(outputDir, '05_admin_game_settings.png') });

    // Xóa cookies để đăng xuất hoàn toàn
    console.log("Clearing cookies to logout Admin...");
    const client = await page.target().createCDPSession();
    await client.send('Network.clearBrowserCookies');

    // 6. Đăng nhập Học sinh An Nhiên (Khối 3)
    console.log("Logging in as Student...");
    await page.goto('http://localhost/MOS/public/dang-nhap', { waitUntil: 'networkidle0' });
    await page.type('input[name="login"]', 'hs001@student.ic3.local');
    await page.type('input[name="password"]', '123456');
    await Promise.all([
        page.waitForNavigation({ waitUntil: 'networkidle0' }),
        page.click('button[type="submit"]')
    ]);

    // 6. Cổng học sinh Home
    console.log("Capturing 06_hoc_sinh_home.png...");
    await page.screenshot({ path: path.join(outputDir, '06_hoc_sinh_home.png') });

    // 7. Khối lớp chi tiết
    console.log("Capturing 07_hoc_sinh_khoi_lop.png...");
    await page.goto('http://localhost/MOS/public/chuong-trinh/khoi-3-spark-level-1', { waitUntil: 'networkidle0' });
    await page.screenshot({ path: path.join(outputDir, '07_hoc_sinh_khoi_lop.png') });

    // 8. Phòng thi ảo làm bài
    console.log("Capturing 08_hoc_sinh_phong_thi.png...");
    await page.goto('http://localhost/MOS/public/bai-luyen/k3-cd1-bai-1/lam-bai', { waitUntil: 'networkidle0' });
    await page.screenshot({ path: path.join(outputDir, '08_hoc_sinh_phong_thi.png') });

    // 9. Thành tích & Bảng xếp hạng
    console.log("Capturing 09_hoc_sinh_thanh_tich.png...");
    await page.goto('http://localhost/MOS/public/thanh-tich', { waitUntil: 'networkidle0' });
    await page.screenshot({ path: path.join(outputDir, '09_hoc_sinh_thanh_tich.png') });

    // 10. Khu trò chơi giải trí
    console.log("Capturing 10_hoc_sinh_tro_choi.png...");
    await page.goto('http://localhost/MOS/public/tro-choi', { waitUntil: 'networkidle0' });
    await page.screenshot({ path: path.join(outputDir, '10_hoc_sinh_tro_choi.png') });

    // 11. Bảng điều khiển phụ huynh
    console.log("Capturing 11_phu_huynh_dashboard.png...");
    await page.goto('http://localhost/MOS/public/phu-huynh', { waitUntil: 'networkidle0' });
    await page.screenshot({ path: path.join(outputDir, '11_phu_huynh_dashboard.png') });

    await browser.close();
    console.log("All screenshots captured successfully in " + outputDir);
}

capture().catch(err => {
    console.error("Error capturing screenshots:", err);
    process.exit(1);
});
