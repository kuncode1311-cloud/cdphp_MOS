import fs from 'node:fs';
import path from 'node:path';
import crypto from 'node:crypto';
import zlib from 'node:zlib';

const root = process.cwd();
const manifest = JSON.parse(fs.readFileSync(path.join(root, 'public/legacy/ic3/manifest.json'), 'utf8').replace(/^\uFEFF/, ''));

const slugToUserMapping = {
  // Grade 3
  'gs6sparkl1-cd1-t1': { topic: 'Chủ đề 1: Căn Bản Về Công Nghệ', test: 'Test 1', userPw: '25103011' },
  'gs6sparkl1-cd1-t2': { topic: 'Chủ đề 1: Căn Bản Về Công Nghệ', test: 'Test 2', userPw: '25103010' },
  'gs6sparkl1-cd2-t1': { topic: 'Chủ đề 2: Công Dân Số', test: 'Test 1', userPw: '25103009' },
  'gs6sparkl1-cd3-t1': { topic: 'Chủ đề 3: Quản Lí Thông Tin', test: 'Test 1', userPw: '25103008' },
  'gs6sparkl1-cd3-t2': { topic: 'Chủ đề 3: Quản Lí Thông Tin', test: 'Test 2', userPw: '25103007' },
  'gs6sparkl1-cd4-t1': { topic: 'Chủ đề 4: Sáng Tạo Nội Dung', test: 'Test 1', userPw: '25103006' },
  'gs6sparkl1-cd5-t1': { topic: 'Chủ đề 5: Truyền Thông', test: 'Test 1', userPw: '25103005' },
  'gs6sparkl1-cd5-t2': { topic: 'Chủ đề 5: Truyền Thông', test: 'Test 2', userPw: '25103004' },
  'gs6sparkl1-cd6-t1': { topic: 'Chủ đề 6: An Toàn Và Bảo Mật', test: 'Test 1', userPw: '25103003' },
  'gs6sparkl1-cd6-t2': { topic: 'Chủ đề 6: An Toàn Và Bảo Mật', test: 'Test 2', userPw: '25103002' },
  'gs6sparkl1-cdmr': { topic: 'Chủ đề Mở Rộng', test: 'Test 1', userPw: '25103001' },

  // Grade 4
  'gs6sparkl2-cd1-t1': { topic: 'Chủ đề 1: Căn Bản Về Công Nghệ', test: 'Test 1', userPw: '41025013' },
  'gs6sparkl2-cd1-t2': { topic: 'Chủ đề 1: Căn Bản Về Công Nghệ', test: 'Test 2', userPw: '41025012' },
  'gs6sparkl2-cd2-t1': { topic: 'Chủ đề 2: Công Dân Số', test: 'Test 1', userPw: '41025011' },
  'gs6sparkl2-cd3-t1': { topic: 'Chủ đề 3: Quản Lí Thông Tin', test: 'Test 1', userPw: '41025010' },
  'gs6sparkl2-cd3-t2': { topic: 'Chủ đề 3: Quản Lí Thông Tin', test: 'Test 2', userPw: '41025009' },
  'gs6sparkl2-cd4-t1': { topic: 'Chủ đề 4: Sáng Tạo Nội Dung', test: 'Test 1', userPw: '41025008' },
  'gs6sparkl2-cd4-t2': { topic: 'Chủ đề 4: Sáng Tạo Nội Dung', test: 'Test 2', userPw: '41025007' },
  'gs6sparkl2-cd4-t3': { topic: 'Chủ đề 4: Sáng Tạo Nội Dung', test: 'Test 3', userPw: '41025006' },
  'gs6sparkl2-cd5-t1': { topic: 'Chủ đề 5: Giao Tiếp Kĩ Thuật Số', test: 'Test 1', userPw: '41025005' },
  'gs6sparkl2-cd5-t2': { topic: 'Chủ đề 5: Giao Tiếp Kĩ Thuật Số', test: 'Test 2', userPw: '41025004' },
  'gs6sparkl2-cd6-t1': { topic: 'Chủ đề 6: Cộng Tác', test: 'Test 1', userPw: '41025003' },
  'gs6sparkl2-cd7-t1': { topic: 'Chủ đề 7: An Toàn Và Bảo Mật', test: 'Test 1', userPw: '41025002' },
  'gs6sparkl2-cd7-t2': { topic: 'Chủ đề 7: An Toàn Và Bảo Mật', test: 'Test 2', userPw: '41025001' },

  // Grade 5
  'gs6sparkl3-cd1-t1': { topic: 'Chủ đề 1: Căn Bản Về Công Nghệ', test: 'Test 1', userPw: '68759341' },
  'gs6sparkl3-cd1-t2': { topic: 'Chủ đề 1: Căn Bản Về Công Nghệ', test: 'Test 2', userPw: '68759342' },
  'gs6sparkl3-cd2-t1': { topic: 'Chủ đề 2: Công Dân Số', test: 'Test 1', userPw: '68759343' },
  'gs6sparkl3-cd2-t2': { topic: 'Chủ đề 2: Căn Bản Về Công Nghệ / Công Dân Số', test: 'Test 2', userPw: '68759344' },
  'gs6sparkl3-cd3-t1': { topic: 'Chủ đề 3: Quản Lí Thông Tin', test: 'Test 1', userPw: '68759345' },
  'gs6sparkl3-cd4-t1': { topic: 'Chủ đề 4: Sáng Tạo Nội Dung', test: 'Test 1', userPw: '68759346' },
  'gs6sparkl3-cd5-t1': { topic: 'Chủ đề 5: Giao Tiếp Kĩ Thuật Số', test: 'Test 1', userPw: '68759347' },
  'gs6sparkl3-cd5-t2': { topic: 'Chủ đề 5: Giao Tiếp Kĩ Thuật Số', test: 'Test 2', userPw: '68759348' },
  'gs6sparkl3-cd6-t1': { topic: 'Chủ đề 6: Cộng Tác', test: 'Test 1', userPw: '68759349' },
  'gs6sparkl3-cd7-t1': { topic: 'Chủ đề 7: An Toàn Và Bảo Mật', test: 'Test 1', userPw: '68759350' },
  'gs6sparkl3-cd7-t2': { topic: 'Chủ đề 7: An Toàn Và Bảo Mật', test: 'Test 2', userPw: '68759351' },
};

async function runDetailedAudit() {
  console.log('=== STARTING FULL DEEP AUDIT OF ALL IC3 TESTS ===\n');

  const detailedReports = [];
  let totalTests = 0;
  let totalQuestionsCount = 0;
  let allPerfect = true;

  for (const item of manifest) {
    totalTests++;
    const meta = slugToUserMapping[item.slug];
    const localBase = path.join(root, 'public', item.local_path.replace(/^\//, '').replace(/\/index\.html$/, ''));
    const sourceBase = new URL('./', item.source_url);

    const report = {
      grade: item.grade,
      slug: item.slug,
      topic: meta?.topic || 'Unknown',
      testName: meta?.test || item.name,
      userExpectedPassword: meta?.userPw,
      embeddedPassword: null,
      passwordMatches: false,
      onlineHtmlHashMatch: false,
      onlineQuiz1HashMatch: false,
      questionCount: 0,
      questions: [],
      assetAudit: {
        totalReferenced: 0,
        localExisting: 0,
        localMissing: [],
        onlineAccessible: 0,
        onlineMissing: []
      },
      discrepancies: []
    };

    // 1. Read local index.html and parse presInfo
    const localIndexPath = path.join(localBase, 'index.html');
    const localQuiz1Path = path.join(localBase, 'data/quiz1.js');

    if (!fs.existsSync(localIndexPath)) {
      report.discrepancies.push(`Missing local index.html`);
    }
    if (!fs.existsSync(localQuiz1Path)) {
      report.discrepancies.push(`Missing local data/quiz1.js`);
    }

    const localIndex = fs.readFileSync(localIndexPath, 'utf8');
    const localQuiz1 = fs.readFileSync(localQuiz1Path, 'utf8');

    // Extract password from presInfo
    const presMatch = localIndex.match(/var presInfo = "([^"]+)"/);
    let decodedPres = null;
    if (presMatch) {
      decodedPres = zlib.inflateSync(Buffer.from(presMatch[1], 'base64')).toString('utf8');
      const presJson = JSON.parse(decodedPres);
      report.embeddedPassword = presJson.e?.P?.p || null;
      report.passwordMatches = (String(report.embeddedPassword) === String(report.userExpectedPassword));
      if (!report.passwordMatches) {
        report.discrepancies.push(`Password mismatch: expected ${report.userExpectedPassword}, found in package ${report.embeddedPassword}`);
      }
    } else {
      report.discrepancies.push(`Could not find presInfo in local index.html`);
    }

    // 2. Compare with online index.html & quiz1.js
    try {
      const respIndex = await fetch(item.source_url);
      if (respIndex.ok) {
        const onlineIndexText = await respIndex.text();
        const localIndexSha = crypto.createHash('sha256').update(localIndex).digest('hex');
        const onlineIndexSha = crypto.createHash('sha256').update(onlineIndexText).digest('hex');
        report.onlineHtmlHashMatch = (localIndexSha === onlineIndexSha);
        if (!report.onlineHtmlHashMatch) {
          report.discrepancies.push(`index.html hash differs from live server`);
        }
      } else {
        report.discrepancies.push(`Could not fetch online index.html (status ${respIndex.status})`);
      }

      const onlineQuiz1Url = new URL('data/quiz1.js', sourceBase);
      const respQuiz1 = await fetch(onlineQuiz1Url);
      if (respQuiz1.ok) {
        const onlineQuiz1Text = await respQuiz1.text();
        const localQuiz1Sha = crypto.createHash('sha256').update(localQuiz1).digest('hex');
        const onlineQuiz1Sha = crypto.createHash('sha256').update(onlineQuiz1Text).digest('hex');
        report.onlineQuiz1HashMatch = (localQuiz1Sha === onlineQuiz1Sha);
        if (!report.onlineQuiz1HashMatch) {
          report.discrepancies.push(`data/quiz1.js hash differs from live server`);
        }
      } else {
        report.discrepancies.push(`Could not fetch online data/quiz1.js (status ${respQuiz1.status})`);
      }
    } catch (e) {
      report.discrepancies.push(`Network error fetching live assets: ${e.message}`);
    }

    // 3. Parse quizInfo JSON
    const quizMatch = localQuiz1.match(/var quizInfo = "([^"]+)"/);
    if (!quizMatch) {
      report.discrepancies.push(`Could not find quizInfo in data/quiz1.js`);
      detailedReports.push(report);
      continue;
    }

    const quizRawJson = Buffer.from(quizMatch[1], 'base64').toString('utf8');
    const quizData = JSON.parse(quizRawJson);

    // 4. Extract all questions / slides and audit each question
    const slides = quizData.d?.sl?.g?.[0]?.S || [];
    report.questionCount = slides.length;
    totalQuestionsCount += slides.length;

    for (let qIdx = 0; qIdx < slides.length; qIdx++) {
      const slide = slides[qIdx];
      const qReport = {
        questionNumber: qIdx + 1,
        slideId: slide.id,
        type: slide.tp,
        title: slide.D?.t || '',
        prompt: slide.D?.p || '',
        hasDescription: !!slide.D,
        choices: slide.C,
        referencedImages: [],
        missingImages: []
      };

      // Check question media / shapes
      const slideJsonStr = JSON.stringify(slide);
      const imgMatches = [...slideJsonStr.matchAll(/img-[a-f0-9]+\.(?:png|jpe?g|gif|svg|webp)/gi)].map(m => m[0]);
      for (const imgName of new Set(imgMatches)) {
        qReport.referencedImages.push(imgName);
        const p1 = path.join(localBase, 'data/quiz1/images', imgName);
        const p2 = path.join(localBase, 'data/images', imgName);
        if (!fs.existsSync(p1) && !fs.existsSync(p2)) {
          qReport.missingImages.push(imgName);
          report.discrepancies.push(`Câu ${qIdx + 1}: Missing image ${imgName}`);
        }
      }

      report.questions.push(qReport);
    }

    // 5. Audit all assets in the test folder
    const assetRe = /quiz\d+[\\/][A-Za-z0-9_./%()+ \\-]+\.(?:js|css|png|jpe?g|gif|svg|webp|woff2?|ttf|mp3|mp4|m4a|ogg|wav|xml|json)/gi;
    const quizAssets = [...new Set([...quizRawJson.matchAll(assetRe)].map(m => `data/${m[0].replaceAll('\\\\', '/').replaceAll('\\', '/')}`))];

    let presAssets = [];
    if (decodedPres) {
      const generalAssetRe = /data\/[A-Za-z0-9_./%()+ -]+\.(?:js|css|png|jpe?g|gif|svg|webp|woff2?|ttf|mp3|mp4|m4a|ogg|wav|xml|json)/gi;
      presAssets = [...new Set([...decodedPres.matchAll(generalAssetRe)].map(m => m[0].replace(/[?#].*$/, '')))];
    }

    const allAssets = [...new Set([...quizAssets, ...presAssets])];
    report.assetAudit.totalReferenced = allAssets.length;

    for (const rel of allAssets) {
      const target = path.join(localBase, ...rel.split('/'));
      if (fs.existsSync(target) && fs.statSync(target).size > 0) {
        report.assetAudit.localExisting++;
      } else {
        report.assetAudit.localMissing.push(rel);
        report.discrepancies.push(`Missing asset: ${rel}`);
      }
    }

    if (report.discrepancies.length > 0) {
      allPerfect = false;
    }

    detailedReports.push(report);
    console.log(`[Khối ${item.grade}] ${meta.topic} - ${meta.test} (${item.slug}):`);
    console.log(`   - Số câu: ${report.questionCount}`);
    console.log(`   - Password: ${report.embeddedPassword} (Khớp User PW: ${report.passwordMatches})`);
    console.log(`   - Online HTML match: ${report.onlineHtmlHashMatch}, Online Quiz1 match: ${report.onlineQuiz1HashMatch}`);
    console.log(`   - Assets: ${report.assetAudit.localExisting}/${report.assetAudit.totalReferenced} OK (Missing: ${report.assetAudit.localMissing.length})`);
    if (report.discrepancies.length > 0) {
      console.log(`   - CẢNH BÁO / LỖI:`, report.discrepancies);
    }
  }

  console.log('\n========================================');
  console.log(`TỔNG KẾT KIỂM TRA:`);
  console.log(`- Tổng số Khối kiểm tra: 3 (Khối 3, Khối 4, Khối 5)`);
  console.log(`- Tổng số bài Test: ${totalTests} / 35`);
  console.log(`- Tổng số câu hỏi: ${totalQuestionsCount}`);
  console.log(`- Trạng thái 100% toàn vẹn: ${allPerfect ? 'HOÀN TOÀN ĐẦY ĐỦ 100%' : 'CÓ PHẦN THIẾU/SAI'}`);
  console.log('========================================\n');

  fs.writeFileSync('storage/full-audit-report.json', JSON.stringify(detailedReports, null, 2));
}

runDetailedAudit().catch(console.error);
