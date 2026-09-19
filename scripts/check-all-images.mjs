import fs from 'node:fs';
import path from 'node:path';

const root = process.cwd();
const manifest = JSON.parse(fs.readFileSync(path.join(root, 'public/legacy/ic3/manifest.json'), 'utf8').replace(/^\uFEFF/, ''));

for (const item of manifest) {
  const localBase = path.join(root, 'public', item.local_path.replace(/^\//, '').replace(/\/index\.html$/, ''));
  const quizRaw = fs.readFileSync(path.join(localBase, 'data/quiz1.js'), 'utf8');
  const match = quizRaw.match(/var quizInfo = "([^"]+)"/);
  const quizData = JSON.parse(Buffer.from(match[1], 'base64').toString('utf8'));
  
  // Check quizData.rs (resources)
  const rs = quizData.rs || {};
  const rsKeys = Object.keys(rs);
  
  // Check if any resources are missing on disk
  const missingInTest = [];
  for (const [rKey, rVal] of Object.entries(rs)) {
    // rVal might have url or relative path
    const pathCandidate1 = path.join(localBase, 'data/quiz1', rVal.u || rVal.p || rKey);
    const pathCandidate2 = path.join(localBase, 'data', rVal.u || rVal.p || rKey);
    // console.log(item.slug, rKey, rVal);
  }

  // Find all storage:// or img- in JSON
  const rawStr = JSON.stringify(quizData);
  const imgRefs = [...rawStr.matchAll(/img-[a-f0-9]+\.(?:png|jpe?g|gif|svg|webp)/gi)].map(m => m[0]);
  const uniqueImgs = [...new Set(imgRefs)];

  for (const img of uniqueImgs) {
    const p1 = path.join(localBase, 'data/quiz1/images', img);
    const p2 = path.join(localBase, 'data/images', img);
    const p3 = path.join(localBase, 'data', img);
    if (!fs.existsSync(p1) && !fs.existsSync(p2) && !fs.existsSync(p3)) {
      missingInTest.push(img);
    }
  }

  if (missingInTest.length > 0) {
    console.log(`[GRADE ${item.grade}] ${item.slug} MISSING LOCAL IMAGES:`, missingInTest);
  }
}
