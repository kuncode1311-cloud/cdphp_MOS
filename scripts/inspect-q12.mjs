import fs from 'fs';

const code = fs.readFileSync('public/legacy/ic3/grade-3/gs6sparkl1-cd1-t1/data/quiz1.js', 'utf8');
const match = code.match(/var quizInfo = "([^"]+)"/);
const rawJson = Buffer.from(match[1], 'base64').toString('utf8');
const quiz = JSON.parse(rawJson);
const s12 = quiz.d?.sl?.g?.[0]?.S?.[11]; // Q12 Matching

console.log('Q12 Matching keys:', Object.keys(s12));
console.log('s12.C:', JSON.stringify(s12.C, null, 2));
