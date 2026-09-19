import fs from 'node:fs';
import path from 'node:path';

const root = process.cwd();
const localBase = path.join(root, 'public/legacy/ic3/grade-3/gs6sparkl1-cd1-t1');
const quiz1 = fs.readFileSync(path.join(localBase, 'data/quiz1.js'), 'utf8');
const match = quiz1.match(/var quizInfo = "([^"]+)"/);
const jsonStr = Buffer.from(match[1], 'base64').toString('utf8');
const quizData = JSON.parse(jsonStr);

console.log('Contains 25103011:', jsonStr.includes('25103011'));
console.log('quizData top keys:', Object.keys(quizData));
console.log('quizData.d keys:', Object.keys(quizData.d || {}));
console.log('quizData.d.s (settings):', JSON.stringify(quizData.d?.s));
console.log('quizData.d.pr (properties):', JSON.stringify(quizData.d?.pr));
console.log('quizData.d.g (groups/general):', JSON.stringify(quizData.d?.g));
console.log('quizData.d.v (variables):', JSON.stringify(quizData.d?.v));

// Let's search any 8-digit numbers in jsonStr
console.log('8-digit numbers found in quiz1:', jsonStr.match(/\b\d{8}\b/g));

// Let's check other files in folder
const allFiles = fs.readdirSync(path.join(localBase, 'data'), { recursive: true });
console.log('Files in data folder:', allFiles);
for (const f of allFiles) {
  const full = path.join(localBase, 'data', f);
  if (fs.statSync(full).isFile()) {
    const content = fs.readFileSync(full);
    if (content.includes('25103011')) {
      console.log('Found 25103011 in file:', f);
    }
  }
}
