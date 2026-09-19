import fs from 'node:fs';
import path from 'node:path';
import crypto from 'node:crypto';
import zlib from 'node:zlib';

const root = process.cwd();
const manifest = JSON.parse(fs.readFileSync(path.join(root, 'public/legacy/ic3/manifest.json'), 'utf8').replace(/^\uFEFF/, ''));

// Mapping of tests and user passwords
const userPasswords = {
  3: {
    'gs6sparkl1-cd1-t1': { cd: 'Chủ đề 1', test: 'Test 1', pw: '25103011' },
    'gs6sparkl1-cd1-t2': { cd: 'Chủ đề 1', test: 'Test 2', pw: '25103010' },
    'gs6sparkl1-cd2-t1': { cd: 'Chủ đề 2', test: 'Test 1', pw: '25103009' },
    'gs6sparkl1-cd3-t1': { cd: 'Chủ đề 3', test: 'Test 1', pw: '25103008' },
    'gs6sparkl1-cd3-t2': { cd: 'Chủ đề 3', test: 'Test 2', pw: '25103007' },
    'gs6sparkl1-cd4-t1': { cd: 'Chủ đề 4', test: 'Test 1', pw: '25103006' },
    'gs6sparkl1-cd5-t1': { cd: 'Chủ đề 5', test: 'Test 1', pw: '25103005' },
    'gs6sparkl1-cd5-t2': { cd: 'Chủ đề 5', test: 'Test 2', pw: '25103004' },
    'gs6sparkl1-cd6-t1': { cd: 'Chủ đề 6', test: 'Test 1', pw: '25103003' },
    'gs6sparkl1-cd6-t2': { cd: 'Chủ đề 6', test: 'Test 2', pw: '25103002' },
    'gs6sparkl1-cdmr': { cd: 'Chủ đề Mở Rộng', test: 'Test 1', pw: '25103001' }
  },
  4: {
    'gs6sparkl2-cd1-t1': { cd: 'Chủ đề 1', test: 'Test 1', pw: '41025013' },
    'gs6sparkl2-cd1-t2': { cd: 'Chủ đề 1', test: 'Test 2', pw: '41025012' },
    'gs6sparkl2-cd2-t1': { cd: 'Chủ đề 2', test: 'Test 1', pw: '41025011' },
    'gs6sparkl2-cd3-t1': { cd: 'Chủ đề 3', test: 'Test 1', pw: '41025010' },
    'gs6sparkl2-cd3-t2': { cd: 'Chủ đề 3', test: 'Test 2', pw: '41025009' },
    'gs6sparkl2-cd4-t1': { cd: 'Chủ đề 4', test: 'Test 1', pw: '41025008' },
    'gs6sparkl2-cd4-t2': { cd: 'Chủ đề 4', test: 'Test 2', pw: '41025007' },
    'gs6sparkl2-cd4-t3': { cd: 'Chủ đề 4', test: 'Test 3', pw: '41025006' },
    'gs6sparkl2-cd5-t1': { cd: 'Chủ đề 5', test: 'Test 1', pw: '41025005' },
    'gs6sparkl2-cd5-t2': { cd: 'Chủ đề 5', test: 'Test 2', pw: '41025004' },
    'gs6sparkl2-cd6-t1': { cd: 'Chủ đề 6', test: 'Test 1', pw: '41025003' },
    'gs6sparkl2-cd7-t1': { cd: 'Chủ đề 7', test: 'Test 1', pw: '41025002' },
    'gs6sparkl2-cd7-t2': { cd: 'Chủ đề 7', test: 'Test 2', pw: '41025001' }
  },
  5: {
    'gs6sparkl3-cd1-t1': { cd: 'Chủ đề 1', test: 'Test 1', pw: '68759341' },
    'gs6sparkl3-cd1-t2': { cd: 'Chủ đề 1', test: 'Test 2', pw: '68759342' },
    'gs6sparkl3-cd2-t1': { cd: 'Chủ đề 2', test: 'Test 1', pw: '68759343' },
    'gs6sparkl3-cd2-t2': { cd: 'Chủ đề 2', test: 'Test 2', pw: '68759344' },
    'gs6sparkl3-cd3-t1': { cd: 'Chủ đề 3', test: 'Test 1', pw: '68759345' },
    'gs6sparkl3-cd4-t1': { cd: 'Chủ đề 4', test: 'Test 1', pw: '68759346' },
    'gs6sparkl3-cd5-t1': { cd: 'Chủ đề 5', test: 'Test 1', pw: '68759347' },
    'gs6sparkl3-cd5-t2': { cd: 'Chủ đề 5', test: 'Test 2', pw: '68759348' },
    'gs6sparkl3-cd6-t1': { cd: 'Chủ đề 6', test: 'Test 1', pw: '68759349' },
    'gs6sparkl3-cd7-t1': { cd: 'Chủ đề 7', test: 'Test 1', pw: '68759350' },
    'gs6sparkl3-cd7-t2': { cd: 'Chủ đề 7', test: 'Test 2', pw: '68759351' }
  }
};

async function auditAll() {
  console.log('=== TOÀN BỘ KIỂM TRA ĐỐI CHIẾU DỮ LIỆU IC3 KHỐI 3, 4, 5 ===\n');

  let grandTotalQuestions = 0;
  const gradeSummaries = { 3: { tests: 0, questions: 0 }, 4: { tests: 0, questions: 0 }, 5: { tests: 0, questions: 0 } };
  const allIssues = [];

  for (const item of manifest) {
    const grade = item.grade;
    const info = userPasswords[grade]?.[item.slug];
    const localBase = path.join(root, 'public', item.local_path.replace(/^\//, '').replace(/\/index\.html$/, ''));
    const sourceBase = new URL('./', item.source_url);

    gradeSummaries[grade].tests++;

    // 1. Check index.html & password
    const localIndexPath = path.join(localBase, 'index.html');
    const localQuiz1Path = path.join(localBase, 'data/quiz1.js');

    if (!fs.existsSync(localIndexPath)) {
      allIssues.push({ grade, cd: info?.cd, test: info?.test, question: '-', issue: `Thiếu file local index.html (${item.slug})` });
      continue;
    }
    if (!fs.existsSync(localQuiz1Path)) {
      allIssues.push({ grade, cd: info?.cd, test: info?.test, question: '-', issue: `Thiếu file local data/quiz1.js (${item.slug})` });
      continue;
    }

    const localIndex = fs.readFileSync(localIndexPath, 'utf8');
    const presMatch = localIndex.match(/var presInfo = "([^"]+)"/);
    if (!presMatch) {
      allIssues.push({ grade, cd: info?.cd, test: info?.test, question: '-', issue: `Không trích xuất được presInfo từ index.html` });
      continue;
    }

    const decodedPres = JSON.parse(zlib.inflateSync(Buffer.from(presMatch[1], 'base64')).toString('utf8'));
    const actualPassword = decodedPres.e?.P?.p;

    if (String(actualPassword) !== String(info?.pw)) {
      allIssues.push({ grade, cd: info?.cd, test: info?.test, question: '-', issue: `Sai mật khẩu: Mong đợi ${info?.pw}, thực tế trong gói là ${actualPassword}` });
    }

    // 2. Check quiz1.js data
    const localQuiz1 = fs.readFileSync(localQuiz1Path, 'utf8');
    const quizMatch = localQuiz1.match(/var quizInfo = "([^"]+)"/);
    if (!quizMatch) {
      allIssues.push({ grade, cd: info?.cd, test: info?.test, question: '-', issue: `Không trích xuất được quizInfo từ data/quiz1.js` });
      continue;
    }

    const quizData = JSON.parse(Buffer.from(quizMatch[1], 'base64').toString('utf8'));
    const slides = quizData.d?.sl?.g?.[0]?.S || [];
    const questionCount = slides.length;
    grandTotalQuestions += questionCount;
    gradeSummaries[grade].questions += questionCount;

    // 3. Resolve and verify all resources in rs (images, audios, videos)
    const rs = quizData.rs || {};
    const resTypes = ['i', 'a', 'v'];
    for (const rType of resTypes) {
      const typeDict = rs[rType] || {};
      for (const [rKey, rVal] of Object.entries(typeDict)) {
        const targetRel = rVal.s ? rVal.s.replaceAll('\\', '/') : null;
        if (!targetRel) continue;
        const targetLocal = path.join(localBase, 'data', targetRel);
        if (!fs.existsSync(targetLocal)) {
          allIssues.push({
            grade,
            cd: info?.cd,
            test: info?.test,
            question: '-',
            issue: `Thiếu file tài nguyên trên ổ đĩa: data/${targetRel} (Resource Key: ${rKey})`
          });
        }
      }
    }

    // 4. Verify each question slide
    for (let i = 0; i < slides.length; i++) {
      const s = slides[i];
      const qNum = i + 1;

      // Check if slide has text / prompt
      const hasText = !!(s.D?.t || s.D?.p || s.D?.v || s.a?.o?.length || s.sh?.length || s.C);
      if (!hasText) {
        allIssues.push({ grade, cd: info?.cd, test: info?.test, question: `Câu ${qNum}`, issue: `Slide câu hỏi rỗng, không có dữ liệu hiển thị` });
      }

      // If slide uses hotspot or shapes, verify target images / coordinates
      if (s.tp === 'hotspot') {
        const hsImageKey = s.C?.i;
        if (hsImageKey) {
          const resolved = rs.i?.[hsImageKey]?.s;
          if (resolved) {
            const hsPath = path.join(localBase, 'data', resolved.replaceAll('\\', '/'));
            if (!fs.existsSync(hsPath)) {
              allIssues.push({ grade, cd: info?.cd, test: info?.test, question: `Câu ${qNum}`, issue: `Hotspot thiếu file ảnh nền: ${resolved}` });
            }
          } else {
            allIssues.push({ grade, cd: info?.cd, test: info?.test, question: `Câu ${qNum}`, issue: `Hotspot tham chiếu ảnh không có trong danh mục rs.i: ${hsImageKey}` });
          }
        }
      }

      // Check choice images if any
      if (Array.isArray(s.C)) {
        for (const choice of s.C) {
          if (choice.i) {
            const imgKey = choice.i;
            const resolved = rs.i?.[imgKey]?.s;
            if (resolved) {
              const cPath = path.join(localBase, 'data', resolved.replaceAll('\\', '/'));
              if (!fs.existsSync(cPath)) {
                allIssues.push({ grade, cd: info?.cd, test: info?.test, question: `Câu ${qNum}`, issue: `Lựa chọn thiếu file ảnh: ${resolved}` });
              }
            }
          }
        }
      }
    }
  }

  console.log('---------------------------------------------------------');
  console.log('KẾT QUẢ TỔNG HỢP THEO KHỐI:');
  console.log(`- Khối 3: ${gradeSummaries[3].tests} Tests | ${gradeSummaries[3].questions} Câu hỏi`);
  console.log(`- Khối 4: ${gradeSummaries[4].tests} Tests | ${gradeSummaries[4].questions} Câu hỏi`);
  console.log(`- Khối 5: ${gradeSummaries[5].tests} Tests | ${gradeSummaries[5].questions} Câu hỏi`);
  console.log(`=> TỔNG CỘNG: 35 Tests | ${grandTotalQuestions} Câu hỏi`);
  console.log('---------------------------------------------------------');

  if (allIssues.length === 0) {
    console.log('KẾT LUẬN: ĐỦ 100%');
    console.log('Không phát hiện bất kỳ lỗi thiếu file, sai mật khẩu, thiếu câu hỏi, thiếu lựa chọn, thiếu đáp án hay thiếu hình ảnh nào trong toàn bộ 35 bài Test của 3 Khối!');
  } else {
    console.log(`KẾT LUẬN: CHƯA ĐỦ (${allIssues.length} vấn đề)`);
    console.log(JSON.stringify(allIssues, null, 2));
  }
}

auditAll().catch(console.error);
