import fs from 'node:fs';
import path from 'node:path';
import crypto from 'node:crypto';
import zlib from 'node:zlib';

const root = process.cwd();
const manifest = JSON.parse(fs.readFileSync(path.join(root, 'public/legacy/ic3/manifest.json'), 'utf8').replace(/^\uFEFF/, ''));

// Mapping of passwords given by user
const expectedPasswords = {
  3: {
    'gs6sparkl1-cd1-t1': '25103011',
    'gs6sparkl1-cd1-t2': '25103010',
    'gs6sparkl1-cd2-t1': '25103009',
    'gs6sparkl1-cd3-t1': '25103008',
    'gs6sparkl1-cd3-t2': '25103007',
    'gs6sparkl1-cd4-t1': '25103006',
    'gs6sparkl1-cd5-t1': '25103005',
    'gs6sparkl1-cd5-t2': '25103004',
    'gs6sparkl1-cd6-t1': '25103003',
    'gs6sparkl1-cd6-t2': '25103002',
    'gs6sparkl1-cdmr': '25103001'
  },
  4: {
    'gs6sparkl2-cd1-t1': '41025013',
    'gs6sparkl2-cd1-t2': '41025012',
    'gs6sparkl2-cd2-t1': '41025011',
    'gs6sparkl2-cd3-t1': '41025010',
    'gs6sparkl2-cd3-t2': '41025009',
    'gs6sparkl2-cd4-t1': '41025008',
    'gs6sparkl2-cd4-t2': '41025007',
    'gs6sparkl2-cd4-t3': '41025006',
    'gs6sparkl2-cd5-t1': '41025005',
    'gs6sparkl2-cd5-t2': '41025004',
    'gs6sparkl2-cd6-t1': '41025003',
    'gs6sparkl2-cd7-t1': '41025002',
    'gs6sparkl2-cd7-t2': '41025001'
  },
  5: {
    'gs6sparkl3-cd1-t1': '68759341',
    'gs6sparkl3-cd1-t2': '68759342',
    'gs6sparkl3-cd2-t1': '68759343',
    'gs6sparkl3-cd2-t2': '68759344',
    'gs6sparkl3-cd3-t1': '68759345',
    'gs6sparkl3-cd4-t1': '68759346',
    'gs6sparkl3-cd5-t1': '68759347',
    'gs6sparkl3-cd5-t2': '68759348',
    'gs6sparkl3-cd6-t1': '68759349',
    'gs6sparkl3-cd7-t1': '68759350',
    'gs6sparkl3-cd7-t2': '68759351'
  }
};

const results = [];

async function audit() {
  console.log(`Starting thorough audit of ${manifest.length} tests...`);

  for (const item of manifest) {
    const grade = item.grade;
    const slug = item.slug;
    const expectedPassword = expectedPasswords[grade]?.[slug];
    const localBase = path.join(root, 'public', item.local_path.replace(/^\//, '').replace(/\/index\.html$/, ''));
    const sourceBase = new URL('./', item.source_url);

    const testReport = {
      grade,
      slug,
      name: item.name,
      sourceUrl: item.source_url,
      expectedPassword,
      actualPassword: null,
      passwordMatches: false,
      onlineIndexAccessible: false,
      onlineQuiz1Accessible: false,
      quizJsonMatchesOnline: false,
      totalQuestions: 0,
      questionTypes: {},
      missingLocalAssets: [],
      missingOnlineAssets: [],
      assetAudit: { total: 0, localOk: 0, localMissing: 0 },
      questionAuditErrors: []
    };

    // 1. Check local files existence
    const localIndexPath = path.join(localBase, 'index.html');
    const localQuiz1Path = path.join(localBase, 'data/quiz1.js');

    if (!fs.existsSync(localIndexPath) || !fs.existsSync(localQuiz1Path)) {
      testReport.questionAuditErrors.push(`Local files missing: index.html: ${fs.existsSync(localIndexPath)}, quiz1.js: ${fs.existsSync(localQuiz1Path)}`);
      results.push(testReport);
      continue;
    }

    const localIndexContent = fs.readFileSync(localIndexPath, 'utf8');
    const localQuiz1Content = fs.readFileSync(localQuiz1Path, 'utf8');

    // 2. Fetch online index.html & online quiz1.js to compare SHA256
    let onlineQuiz1Content = null;
    try {
      const respIndex = await fetch(item.source_url);
      testReport.onlineIndexAccessible = respIndex.ok;

      const onlineQuiz1Url = new URL('data/quiz1.js', sourceBase);
      const respQuiz1 = await fetch(onlineQuiz1Url);
      if (respQuiz1.ok) {
        testReport.onlineQuiz1Accessible = true;
        onlineQuiz1Content = await respQuiz1.text();
      }
    } catch (e) {
      testReport.questionAuditErrors.push(`Network fetch error: ${e.message}`);
    }

    // 3. Compare quiz1.js checksums
    const localQuiz1Sha = crypto.createHash('sha256').update(localQuiz1Content).digest('hex');
    const onlineQuiz1Sha = onlineQuiz1Content ? crypto.createHash('sha256').update(onlineQuiz1Content).digest('hex') : null;
    testReport.quizJsonMatchesOnline = (localQuiz1Sha === onlineQuiz1Sha);

    // 4. Parse quizInfo JSON
    const match = localQuiz1Content.match(/var quizInfo = "([^"]+)"/);
    if (!match) {
      testReport.questionAuditErrors.push('Could not parse var quizInfo from quiz1.js');
      results.push(testReport);
      continue;
    }

    let quizData;
    try {
      quizData = JSON.parse(Buffer.from(match[1], 'base64').toString('utf8'));
    } catch (e) {
      testReport.questionAuditErrors.push(`Failed to decode base64 quizInfo: ${e.message}`);
      results.push(testReport);
      continue;
    }

    // 5. Inspect quiz properties (password, question count, questions, answers, shapes)
    // Find password in quiz data
    if (quizData.p) {
      testReport.actualPassword = quizData.p;
    } else if (quizData.d?.p) {
      testReport.actualPassword = quizData.d.p;
    } else if (quizData.settings?.password) {
      testReport.actualPassword = quizData.settings.password;
    }

    // Deep search for password in quizData if not found at top level
    if (!testReport.actualPassword) {
      const jsonStr = JSON.stringify(quizData);
      const pwMatch = jsonStr.match(/"(?:pwd|password|psw|pass)":"([^"]+)"/i);
      if (pwMatch) {
        testReport.actualPassword = pwMatch[1];
      }
    }

    testReport.passwordMatches = (String(testReport.actualPassword) === String(expectedPassword));

    // 6. Inspect slides/questions
    // Usually quiz slides are in quizData.d.sl.g (slide groups) -> S (slides)
    const slideGroups = quizData.d?.sl?.g || [];
    const slides = [];
    for (const group of slideGroups) {
      if (Array.isArray(group.S)) {
        for (const slide of group.S) {
          slides.push(slide);
        }
      }
    }

    testReport.totalQuestions = slides.length;

    // Collect all asset references from quizData
    const assetRe = /quiz\d+[\\/][A-Za-z0-9_./%()+ \\-]+\.(?:js|css|png|jpe?g|gif|svg|webp|woff2?|ttf|mp3|mp4|m4a|ogg|wav|xml|json)/gi;
    const rawJsonStr = Buffer.from(match[1], 'base64').toString('utf8');
    const quizAssets = [...new Set([...rawJsonStr.matchAll(assetRe)].map(m => `data/${m[0].replaceAll('\\\\', '/').replaceAll('\\', '/')}`))];

    // Also get presInfo assets
    let presAssets = [];
    const presMatch = localIndexContent.match(/var presInfo = "([^"]+)"/);
    if (presMatch) {
      try {
        const decodedPres = zlib.inflateSync(Buffer.from(presMatch[1], 'base64')).toString('utf8');
        const generalAssetRe = /data\/[A-Za-z0-9_./%()+ -]+\.(?:js|css|png|jpe?g|gif|svg|webp|woff2?|ttf|mp3|mp4|m4a|ogg|wav|xml|json)/gi;
        presAssets = [...new Set([...decodedPres.matchAll(generalAssetRe)].map(m => m[0].replace(/[?#].*$/, '')))];
      } catch (e) {}
    }

    const allReferencedAssets = [...new Set([...quizAssets, ...presAssets])];
    testReport.assetAudit.total = allReferencedAssets.length;

    // Check each asset locally
    for (const relPath of allReferencedAssets) {
      const targetPath = path.join(localBase, ...relPath.split('/'));
      if (fs.existsSync(targetPath)) {
        const stats = fs.statSync(targetPath);
        if (stats.size === 0) {
          testReport.missingLocalAssets.push({ path: relPath, reason: 'zero_bytes' });
        } else {
          testReport.assetAudit.localOk++;
        }
      } else {
        testReport.missingLocalAssets.push({ path: relPath, reason: 'file_not_found' });
      }
    }
    testReport.assetAudit.localMissing = testReport.missingLocalAssets.length;

    // 7. Audit each slide / question in detail
    slides.forEach((slide, index) => {
      const slideType = slide.tp || 'unknown';
      testReport.questionTypes[slideType] = (testReport.questionTypes[slideType] || 0) + 1;

      // Validate question structure
      // D: description / prompt, C: choices / controls / answers, F: feedback
      if (!slide.D && !slide.C && !slide.sh) {
        testReport.questionAuditErrors.push(`Slide ${index + 1} (id: ${slide.id || index}) has no description, choices, or shapes`);
      }
    });

    results.push(testReport);
    console.log(`[Grade ${grade}] ${slug}: ${testReport.totalQuestions} questions, PW: ${testReport.actualPassword} (${testReport.passwordMatches ? 'MATCH' : 'MISMATCH'}), OnlineMatch: ${testReport.quizJsonMatchesOnline}, MissingAssets: ${testReport.assetAudit.localMissing}`);
  }

  // Summary
  fs.writeFileSync('storage/audit-report.json', JSON.stringify(results, null, 2));
  console.log('\n=== AUDIT REPORT SUMMARY WRITTEN TO storage/audit-report.json ===');
}

audit().catch(console.error);
