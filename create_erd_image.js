import puppeteer from 'puppeteer-core';
import fs from 'fs';
import path from 'path';

const edgePath = 'C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe';
const outputDir = 'c:\\laragon\\www\\MOS\\report_images';

const htmlContent = `<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Sơ đồ Thực thể Mối quan hệ (ERD) - IC3 Quest</title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; }
  body { background: #f8fafc; padding: 40px; }
  .title { text-align: center; color: #0f172a; margin-bottom: 24px; }
  .title h1 { font-size: 26px; font-weight: 700; color: #1e3a8a; text-transform: uppercase; }
  .title p { font-size: 14px; color: #64748b; margin-top: 6px; }

  .canvas {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    max-width: 1400px;
    margin: 0 auto;
  }

  .table-card {
    background: #ffffff;
    border-radius: 8px;
    border: 1px solid #cbd5e1;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
    overflow: hidden;
  }

  .table-header {
    background: #1e40af;
    color: #ffffff;
    padding: 10px 14px;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
  .table-header.core { background: #1e3a8a; }
  .table-header.content { background: #047857; }
  .table-header.exam { background: #b91c1c; }
  .table-header.gamify { background: #6b21a8; }

  .table-header h3 { font-size: 14px; font-weight: 700; letter-spacing: 0.5px; }
  .table-header span { font-size: 11px; background: rgba(255,255,255,0.2); padding: 2px 6px; border-radius: 4px; }

  .fields-list { list-style: none; font-size: 12px; }
  .field-row {
    display: flex;
    justify-content: space-between;
    padding: 6px 12px;
    border-bottom: 1px solid #f1f5f9;
  }
  .field-row:nth-child(even) { background: #f8fafc; }
  .field-name { font-weight: 500; color: #334155; }
  .field-name.pk { color: #b91c1c; font-weight: 700; }
  .field-name.fk { color: #2563eb; font-weight: 600; }
  .field-type { color: #64748b; font-size: 11px; }

  .legend {
    max-width: 1400px;
    margin: 30px auto 0;
    padding: 14px 20px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    display: flex;
    justify-content: space-around;
    font-size: 13px;
  }
  .legend-item { display: flex; align-items: center; gap: 8px; }
  .badge { width: 14px; height: 14px; border-radius: 3px; }
  .rel-box {
    margin-top: 20px;
    padding: 16px;
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    border-radius: 8px;
    font-size: 13px;
    color: #1e40af;
    line-height: 1.6;
    max-width: 1400px;
    margin-left: auto;
    margin-right: auto;
  }
</style>
</head>
<body>

<div class="title">
  <h1>Sơ đồ Thực thể Mối quan hệ Cơ sở Dữ liệu (ERD)</h1>
  <p>Hệ thống Học tập & Luyện thi Tin học Quốc tế IC3 Quest & MOS (MySQL 8.0 - 12 Bảng Chuẩn hóa 3NF)</p>
</div>

<div class="canvas">
  <!-- 1. users -->
  <div class="table-card">
    <div class="table-header core">
      <h3>users</h3>
      <span>Người dùng</span>
    </div>
    <ul class="fields-list">
      <li class="field-row"><span class="field-name pk">🔑 id</span><span class="field-type">BIGINT (PK)</span></li>
      <li class="field-row"><span class="field-name">name</span><span class="field-type">VARCHAR(255)</span></li>
      <li class="field-row"><span class="field-name">email</span><span class="field-type">VARCHAR(255) (UQ)</span></li>
      <li class="field-row"><span class="field-name">password</span><span class="field-type">VARCHAR(255)</span></li>
      <li class="field-row"><span class="field-name">role</span><span class="field-type">VARCHAR(30)</span></li>
      <li class="field-row"><span class="field-name">student_code</span><span class="field-type">VARCHAR(255) (UQ)</span></li>
      <li class="field-row"><span class="field-name fk">🔗 classroom_id</span><span class="field-type">BIGINT (FK)</span></li>
      <li class="field-row"><span class="field-name">reward_stars</span><span class="field-type">INT</span></li>
      <li class="field-row"><span class="field-name">game_time_seconds</span><span class="field-type">INT</span></li>
      <li class="field-row"><span class="field-name">status</span><span class="field-type">VARCHAR(30)</span></li>
    </ul>
  </div>

  <!-- 2. classrooms -->
  <div class="table-card">
    <div class="table-header core">
      <h3>classrooms</h3>
      <span>Lớp học</span>
    </div>
    <ul class="fields-list">
      <li class="field-row"><span class="field-name pk">🔑 id</span><span class="field-type">BIGINT (PK)</span></li>
      <li class="field-row"><span class="field-name">name</span><span class="field-type">VARCHAR(255)</span></li>
      <li class="field-row"><span class="field-name">grade</span><span class="field-type">TINYINT (3,4,5)</span></li>
      <li class="field-row"><span class="field-name">school_year</span><span class="field-type">VARCHAR(255)</span></li>
      <li class="field-row"><span class="field-name fk">🔗 teacher_id</span><span class="field-type">BIGINT (FK)</span></li>
      <li class="field-row"><span class="field-name">created_at</span><span class="field-type">TIMESTAMP</span></li>
    </ul>
  </div>

  <!-- 3. programs -->
  <div class="table-card">
    <div class="table-header content">
      <h3>programs</h3>
      <span>Chương trình</span>
    </div>
    <ul class="fields-list">
      <li class="field-row"><span class="field-name pk">🔑 id</span><span class="field-type">BIGINT (PK)</span></li>
      <li class="field-row"><span class="field-name">name</span><span class="field-type">VARCHAR(255)</span></li>
      <li class="field-row"><span class="field-name">slug</span><span class="field-type">VARCHAR(255) (UQ)</span></li>
      <li class="field-row"><span class="field-name">description</span><span class="field-type">TEXT</span></li>
      <li class="field-row"><span class="field-name">accent</span><span class="field-type">VARCHAR(255)</span></li>
    </ul>
  </div>

  <!-- 4. levels -->
  <div class="table-card">
    <div class="table-header content">
      <h3>levels</h3>
      <span>Khối lớp (Grade)</span>
    </div>
    <ul class="fields-list">
      <li class="field-row"><span class="field-name pk">🔑 id</span><span class="field-type">BIGINT (PK)</span></li>
      <li class="field-row"><span class="field-name fk">🔗 program_id</span><span class="field-type">BIGINT (FK)</span></li>
      <li class="field-row"><span class="field-name">name</span><span class="field-type">VARCHAR(255)</span></li>
      <li class="field-row"><span class="field-name">slug</span><span class="field-type">VARCHAR(255)</span></li>
      <li class="field-row"><span class="field-name">grade</span><span class="field-type">TINYINT</span></li>
      <li class="field-row"><span class="field-name">position</span><span class="field-type">SMALLINT</span></li>
    </ul>
  </div>

  <!-- 5. topics -->
  <div class="table-card">
    <div class="table-header content">
      <h3>topics</h3>
      <span>Chủ đề (21 Topics)</span>
    </div>
    <ul class="fields-list">
      <li class="field-row"><span class="field-name pk">🔑 id</span><span class="field-type">BIGINT (PK)</span></li>
      <li class="field-row"><span class="field-name fk">🔗 level_id</span><span class="field-type">BIGINT (FK)</span></li>
      <li class="field-row"><span class="field-name">name</span><span class="field-type">VARCHAR(255)</span></li>
      <li class="field-row"><span class="field-name">slug</span><span class="field-type">VARCHAR(255)</span></li>
      <li class="field-row"><span class="field-name">icon</span><span class="field-type">VARCHAR(255)</span></li>
      <li class="field-row"><span class="field-name">position</span><span class="field-type">SMALLINT</span></li>
    </ul>
  </div>

  <!-- 6. practice_tests -->
  <div class="table-card">
    <div class="table-header exam">
      <h3>practice_tests</h3>
      <span>Bộ đề (35 Bài)</span>
    </div>
    <ul class="fields-list">
      <li class="field-row"><span class="field-name pk">🔑 id</span><span class="field-type">BIGINT (PK)</span></li>
      <li class="field-row"><span class="field-name fk">🔗 topic_id</span><span class="field-type">BIGINT (FK)</span></li>
      <li class="field-row"><span class="field-name">name</span><span class="field-type">VARCHAR(255)</span></li>
      <li class="field-row"><span class="field-name">slug</span><span class="field-type">VARCHAR(255)</span></li>
      <li class="field-row"><span class="field-name">duration_minutes</span><span class="field-type">SMALLINT</span></li>
      <li class="field-row"><span class="field-name">pass_score</span><span class="field-type">SMALLINT (1000)</span></li>
      <li class="field-row"><span class="field-name">shuffle_questions</span><span class="field-type">BOOLEAN</span></li>
      <li class="field-row"><span class="field-name">shuffle_options</span><span class="field-type">BOOLEAN</span></li>
    </ul>
  </div>

  <!-- 7. questions -->
  <div class="table-card">
    <div class="table-header exam">
      <h3>questions</h3>
      <span>Câu hỏi (509 Câu)</span>
    </div>
    <ul class="fields-list">
      <li class="field-row"><span class="field-name pk">🔑 id</span><span class="field-type">BIGINT (PK)</span></li>
      <li class="field-row"><span class="field-name fk">🔗 practice_test_id</span><span class="field-type">BIGINT (FK)</span></li>
      <li class="field-row"><span class="field-name">external_id</span><span class="field-type">VARCHAR(255)</span></li>
      <li class="field-row"><span class="field-name">type</span><span class="field-type">VARCHAR(60)</span></li>
      <li class="field-row"><span class="field-name">title</span><span class="field-type">TEXT</span></li>
      <li class="field-row"><span class="field-name">points</span><span class="field-type">SMALLINT</span></li>
      <li class="field-row"><span class="field-name">is_published</span><span class="field-type">BOOLEAN</span></li>
    </ul>
  </div>

  <!-- 8. question_options -->
  <div class="table-card">
    <div class="table-header exam">
      <h3>question_options</h3>
      <span>Đáp án A-B-C-D</span>
    </div>
    <ul class="fields-list">
      <li class="field-row"><span class="field-name pk">🔑 id</span><span class="field-type">BIGINT (PK)</span></li>
      <li class="field-row"><span class="field-name fk">🔗 question_id</span><span class="field-type">BIGINT (FK)</span></li>
      <li class="field-row"><span class="field-name">content</span><span class="field-type">TEXT</span></li>
      <li class="field-row"><span class="field-name">image_path</span><span class="field-type">VARCHAR(255)</span></li>
      <li class="field-row"><span class="field-name">is_correct</span><span class="field-type">BOOLEAN</span></li>
      <li class="field-row"><span class="field-name">position</span><span class="field-type">SMALLINT</span></li>
    </ul>
  </div>

  <!-- 9. question_assets -->
  <div class="table-card">
    <div class="table-header exam">
      <h3>question_assets</h3>
      <span>Ảnh đề bài</span>
    </div>
    <ul class="fields-list">
      <li class="field-row"><span class="field-name pk">🔑 id</span><span class="field-type">BIGINT (PK)</span></li>
      <li class="field-row"><span class="field-name fk">🔗 question_id</span><span class="field-type">BIGINT (FK)</span></li>
      <li class="field-row"><span class="field-name">kind</span><span class="field-type">VARCHAR(30)</span></li>
      <li class="field-row"><span class="field-name">path</span><span class="field-type">VARCHAR(255)</span></li>
      <li class="field-row"><span class="field-name">original_name</span><span class="field-type">VARCHAR(255)</span></li>
    </ul>
  </div>

  <!-- 10. test_attempts -->
  <div class="table-card">
    <div class="table-header core">
      <h3>test_attempts</h3>
      <span>Lịch sử thi</span>
    </div>
    <ul class="fields-list">
      <li class="field-row"><span class="field-name pk">🔑 id</span><span class="field-type">BIGINT (PK)</span></li>
      <li class="field-row"><span class="field-name fk">🔗 user_id</span><span class="field-type">BIGINT (FK)</span></li>
      <li class="field-row"><span class="field-name fk">🔗 practice_test_id</span><span class="field-type">BIGINT (FK)</span></li>
      <li class="field-row"><span class="field-name">score</span><span class="field-type">SMALLINT (1000)</span></li>
      <li class="field-row"><span class="field-name">correct_answers</span><span class="field-type">SMALLINT</span></li>
      <li class="field-row"><span class="field-name">duration_seconds</span><span class="field-type">INT</span></li>
      <li class="field-row"><span class="field-name">completed_at</span><span class="field-type">TIMESTAMP</span></li>
    </ul>
  </div>

  <!-- 11. game_settings -->
  <div class="table-card">
    <div class="table-header gamify">
      <h3>game_settings</h3>
      <span>Cấu hình Game</span>
    </div>
    <ul class="fields-list">
      <li class="field-row"><span class="field-name pk">🔑 id</span><span class="field-type">BIGINT (PK)</span></li>
      <li class="field-row"><span class="field-name">key</span><span class="field-type">VARCHAR(255) (UQ)</span></li>
      <li class="field-row"><span class="field-name">value</span><span class="field-type">TEXT</span></li>
      <li class="field-row"><span class="field-name">description</span><span class="field-type">VARCHAR(255)</span></li>
    </ul>
  </div>

  <!-- 12. game_transactions -->
  <div class="table-card">
    <div class="table-header gamify">
      <h3>game_transactions</h3>
      <span>Nhật ký đổi Sao</span>
    </div>
    <ul class="fields-list">
      <li class="field-row"><span class="field-name pk">🔑 id</span><span class="field-type">BIGINT (PK)</span></li>
      <li class="field-row"><span class="field-name fk">🔗 user_id</span><span class="field-type">BIGINT (FK)</span></li>
      <li class="field-row"><span class="field-name">type</span><span class="field-type">VARCHAR(255)</span></li>
      <li class="field-row"><span class="field-name">stars_change</span><span class="field-type">INT</span></li>
      <li class="field-row"><span class="field-name">time_seconds_change</span><span class="field-type">INT</span></li>
    </ul>
  </div>
</div>

<div class="rel-box">
  <strong>🔗 Các mối quan hệ thực thể cốt lõi trong hệ thống:</strong><br>
  • <strong>programs (1) ── (N) levels:</strong> Một chương trình (IC3 GS6) chia thành nhiều Khối lớp (Khối 3, 4, 5).<br>
  • <strong>levels (1) ── (N) topics:</strong> Mỗi khối lớp phân bổ thành 7 chủ đề kiến thức chuẩn quốc tế.<br>
  • <strong>topics (1) ── (N) practice_tests:</strong> Mỗi chủ đề chứa từ 1 đến 3 bài luyện thi chuẩn hóa.<br>
  • <strong>practice_tests (1) ── (N) questions:</strong> Mỗi đề thi sở hữu ngân hàng câu hỏi độc lập (10-30 câu).<br>
  • <strong>questions (1) ── (N) question_options & question_assets:</strong> Mỗi câu hỏi liên kết 4 phương án đáp án và hình ảnh chụp màn hình minh họa.<br>
  • <strong>users (1) ── (N) test_attempts & game_transactions:</strong> Lưu vết lịch sử điểm thi và biến động Sao thưởng của học sinh.
</div>

<div class="legend">
  <div class="legend-item"><div class="badge" style="background: #1e3a8a;"></div> Nhóm Người dùng & Khóa học</div>
  <div class="legend-item"><div class="badge" style="background: #047857;"></div> Nhóm Chương trình & Chủ đề</div>
  <div class="legend-item"><div class="badge" style="background: #b91c1c;"></div> Nhóm Bộ đề & Câu hỏi IC3</div>
  <div class="legend-item"><div class="badge" style="background: #6b21a8;"></div> Nhóm Gamification & Đổi quà</div>
</div>

</body>
</html>
`;

async function renderErd() {
    const htmlPath = path.join(outputDir, 'erd_temp.html');
    fs.writeFileSync(htmlPath, htmlContent, 'utf-8');

    const browser = await puppeteer.launch({
        executablePath: edgePath,
        headless: true,
        defaultViewport: { width: 1480, height: 1100 }
    });

    const page = await browser.newPage();
    await page.goto('file:///' + htmlPath.replace(/\\/g, '/'), { waitUntil: 'networkidle0' });
    await page.screenshot({ path: path.join(outputDir, '12_sodo_erd_csdl.png'), fullPage: true });

    await browser.close();
    fs.unlinkSync(htmlPath);
    console.log("ERD diagram rendered successfully to 12_sodo_erd_csdl.png");
}

renderErd().catch(err => {
    console.error("Error rendering ERD:", err);
    process.exit(1);
});
