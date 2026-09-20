import puppeteer from 'puppeteer-core';
import fs from 'fs';
import path from 'path';

const outDir = 'c:/laragon/www/MOS/storage/app/report_screenshots';
if (!fs.existsSync(outDir)) {
    fs.mkdirSync(outDir, { recursive: true });
}

async function captureAll() {
    const browser = await puppeteer.launch({
        executablePath: 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
        headless: 'new',
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });

    console.log('--- 1. GUEST CONTEXT ---');
    const guestCtx = await browser.createBrowserContext();
    const guestPage = await guestCtx.newPage();
    await guestPage.setViewport({ width: 1280, height: 800 });

    // Login page
    await guestPage.goto('http://localhost/MOS/public/dang-nhap', { waitUntil: 'networkidle2' });
    await new Promise(r => setTimeout(r, 600));
    await guestPage.screenshot({ path: path.join(outDir, 'hinh3_01_dang_nhap.png') });
    console.log('[OK] hinh3_01_dang_nhap.png');

    // Pricing page
    await guestPage.goto('http://localhost/MOS/public/bang-gia', { waitUntil: 'networkidle2' });
    await new Promise(r => setTimeout(r, 600));
    await guestPage.screenshot({ path: path.join(outDir, 'hinh3_14_bang_gia_dich_vu.png') });
    console.log('[OK] hinh3_14_bang_gia_dich_vu.png');

    // VietQR Checkout
    await guestPage.goto('http://localhost/MOS/public/bang-gia/thanh-toan/MOS-202609-OQBPB', { waitUntil: 'networkidle2' });
    await new Promise(r => setTimeout(r, 600));
    await guestPage.screenshot({ path: path.join(outDir, 'hinh3_15_thanh_toan_vietqr.png') });
    console.log('[OK] hinh3_15_thanh_toan_vietqr.png');
    await guestCtx.close();

    console.log('--- 2. STUDENT CONTEXT (HS001) ---');
    const studentCtx = await browser.createBrowserContext();
    const studentPage = await studentCtx.newPage();
    await studentPage.setViewport({ width: 1280, height: 800 });

    await studentPage.goto('http://localhost/MOS/public/dang-nhap', { waitUntil: 'networkidle2' });
    await studentPage.type('input[name="login"]', 'HS001');
    await studentPage.type('input[name="password"]', '123456');
    await Promise.all([
        studentPage.waitForNavigation({ waitUntil: 'networkidle2' }),
        studentPage.click('button[type="submit"]')
    ]);
    console.log('Logged in as Student');

    // Student Home (Action Hub)
    await new Promise(r => setTimeout(r, 800));
    await studentPage.screenshot({ path: path.join(outDir, 'hinh3_07_cong_hoc_tap.png') });
    console.log('[OK] hinh3_07_cong_hoc_tap.png');

    // Programs / Levels
    await studentPage.goto('http://localhost/MOS/public/hoc-tap', { waitUntil: 'networkidle2' });
    await new Promise(r => setTimeout(r, 800));
    await studentPage.screenshot({ path: path.join(outDir, 'hinh3_08_danh_muc_khoa_hoc.png') });
    console.log('[OK] hinh3_08_danh_muc_khoa_hoc.png');

    // Level map (Khoi 3)
    await studentPage.goto('http://localhost/MOS/public/chuong-trinh/khoi-3-spark-level-1', { waitUntil: 'networkidle2' });
    await new Promise(r => setTimeout(r, 800));
    await studentPage.screenshot({ path: path.join(outDir, 'hinh3_09_ban_do_chu_de.png') });
    console.log('[OK] hinh3_09_ban_do_chu_de.png');

    // Test launch
    await studentPage.goto('http://localhost/MOS/public/bai-luyen/k3-cd1-bai-1/lam-bai', { waitUntil: 'networkidle2' });
    await new Promise(r => setTimeout(r, 800));
    await studentPage.screenshot({ path: path.join(outDir, 'hinh3_10_chuan_bi_lam_bai.png') });
    console.log('[OK] hinh3_10_chuan_bi_lam_bai.png');

    // Test questions interface
    await studentPage.goto('http://localhost/MOS/public/bai-luyen/k3-cd1-bai-1', { waitUntil: 'networkidle2' });
    await new Promise(r => setTimeout(r, 800));
    await studentPage.screenshot({ path: path.join(outDir, 'hinh3_16_chi_tiet_bai_luyen.png') });
    console.log('[OK] hinh3_16_chi_tiet_bai_luyen.png');

    // Achievements & Leaderboard
    await studentPage.goto('http://localhost/MOS/public/thanh-tich', { waitUntil: 'networkidle2' });
    await new Promise(r => setTimeout(r, 800));
    await studentPage.screenshot({ path: path.join(outDir, 'hinh3_11_bang_thanh_tich.png') });
    console.log('[OK] hinh3_11_bang_thanh_tich.png');

    // Games store
    await studentPage.goto('http://localhost/MOS/public/tro-choi', { waitUntil: 'networkidle2' });
    await new Promise(r => setTimeout(r, 800));
    await studentPage.screenshot({ path: path.join(outDir, 'hinh3_12_khu_tro_choi_doi_sao.png') });
    console.log('[OK] hinh3_12_khu_tro_choi_doi_sao.png');

    // Parent dashboard (needs time for Chart.js animation)
    await studentPage.goto('http://localhost/MOS/public/phu-huynh', { waitUntil: 'networkidle2' });
    await new Promise(r => setTimeout(r, 1500));
    await studentPage.screenshot({ path: path.join(outDir, 'hinh3_13_goc_phu_huynh.png') });
    console.log('[OK] hinh3_13_goc_phu_huynh.png');

    // License history
    await studentPage.goto('http://localhost/MOS/public/lich-su-thue-goi', { waitUntil: 'networkidle2' });
    await new Promise(r => setTimeout(r, 800));
    await studentPage.screenshot({ path: path.join(outDir, 'hinh3_17_lich_su_thue_goi.png') });
    console.log('[OK] hinh3_17_lich_su_thue_goi.png');

    await studentCtx.close();

    console.log('--- 3. ADMIN CONTEXT (admin@ic3.test) ---');
    const adminCtx = await browser.createBrowserContext();
    const adminPage = await adminCtx.newPage();
    await adminPage.setViewport({ width: 1280, height: 800 });

    await adminPage.goto('http://localhost/MOS/public/dang-nhap', { waitUntil: 'networkidle2' });
    await adminPage.type('input[name="login"]', 'admin@ic3.test');
    await adminPage.type('input[name="password"]', '123456');
    await Promise.all([
        adminPage.waitForNavigation({ waitUntil: 'networkidle2' }),
        adminPage.click('button[type="submit"]')
    ]);
    console.log('Logged in as Admin, URL:', adminPage.url());

    // Admin Dashboard
    await adminPage.goto('http://localhost/MOS/public/quan-tri', { waitUntil: 'networkidle2' });
    await new Promise(r => setTimeout(r, 1000));
    await adminPage.screenshot({ path: path.join(outDir, 'hinh3_02_dashboard_admin.png') });
    console.log('[OK] hinh3_02_dashboard_admin.png');

    // Admin User & Class management
    await adminPage.goto('http://localhost/MOS/public/quan-tri/quan-ly', { waitUntil: 'networkidle2' });
    await new Promise(r => setTimeout(r, 1000));
    await adminPage.screenshot({ path: path.join(outDir, 'hinh3_03_quan_ly_nguoi_dung.png') });
    console.log('[OK] hinh3_03_quan_ly_nguoi_dung.png');

    // Question studio
    await adminPage.goto('http://localhost/MOS/public/quan-tri/bo-de-cau-hoi', { waitUntil: 'networkidle2' });
    await new Promise(r => setTimeout(r, 1000));
    await adminPage.screenshot({ path: path.join(outDir, 'hinh3_04_ic3_question_studio.png') });
    console.log('[OK] hinh3_04_ic3_question_studio.png');

    // Game settings
    await adminPage.goto('http://localhost/MOS/public/quan-tri/tro-choi/cai-dat', { waitUntil: 'networkidle2' });
    await new Promise(r => setTimeout(r, 1000));
    await adminPage.screenshot({ path: path.join(outDir, 'hinh3_05_cai_dat_tro_choi.png') });
    console.log('[OK] hinh3_05_cai_dat_tro_choi.png');

    // Admin Packages & Orders
    await adminPage.goto('http://localhost/MOS/public/quan-tri/goi-dich-vu', { waitUntil: 'networkidle2' });
    await new Promise(r => setTimeout(r, 1000));
    await adminPage.screenshot({ path: path.join(outDir, 'hinh3_06_goi_dich_vu_admin.png') });
    console.log('[OK] hinh3_06_goi_dich_vu_admin.png');

    await adminCtx.close();
    await browser.close();
    console.log('ALL SCREENSHOTS CAPTURED PERFECTLY!');
}

captureAll().catch(e => {
    console.error('Error capturing screenshots:', e);
    process.exit(1);
});
