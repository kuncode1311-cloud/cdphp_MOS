import fs from 'node:fs';

const content = fs.readFileSync('public/legacy/ic3/grade-3/gs6sparkl1-cd1-t1/data/quiz1.js', 'utf8');
const match = content.match(/var quizInfo = "([^"]+)"/);
const json = JSON.parse(Buffer.from(match[1], 'base64').toString('utf8'));
const q = json.d.sl.g[0].S[5]; // Question 6

console.log('--- QUESTION 6 (Hotspot) ---');
console.log('tp:', q.tp);
console.log('Title:', q.D?.d || q.D?.h);
console.log('C data:', JSON.stringify(q.C, null, 2));
