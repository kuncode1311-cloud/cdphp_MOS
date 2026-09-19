import fs from 'fs';

const code = fs.readFileSync('public/legacy/ic3/grade-3/gs6sparkl1-cd1-t1/data/quiz1.js', 'utf8');
const match = code.match(/var quizInfo = "([^"]+)"/);
const rawJson = Buffer.from(match[1], 'base64').toString('utf8');
const quiz = JSON.parse(rawJson);
const s6 = quiz.d?.sl?.g?.[0]?.S?.[5]; // Q6 Hotspot

console.log('Q6 Hotspot keys:', Object.keys(s6));
console.log('s6.C:', JSON.stringify(s6.C, null, 2));
