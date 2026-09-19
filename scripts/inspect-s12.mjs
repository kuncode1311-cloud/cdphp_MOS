import fs from 'fs';

const code = fs.readFileSync('public/legacy/ic3/grade-3/gs6sparkl1-cd1-t1/data/quiz1.js', 'utf8');
const match = code.match(/var quizInfo = "([^"]+)"/);
const rawJson = Buffer.from(match[1], 'base64').toString('utf8');
const quiz = JSON.parse(rawJson);
const s12 = quiz.d?.sl?.g?.[0]?.S?.[11]; // Slide 12 (Matching)

console.log('Slide 12 Matching JSON:');
console.log(JSON.stringify(s12, null, 2));
