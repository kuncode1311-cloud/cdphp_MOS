import fs from 'node:fs';
import path from 'node:path';

const quizRaw = fs.readFileSync('public/legacy/ic3/grade-4/gs6sparkl2-cd4-t1/data/quiz1.js', 'utf8');
const match = quizRaw.match(/var quizInfo = "([^"]+)"/);
const quizData = JSON.parse(Buffer.from(match[1], 'base64').toString('utf8'));
const slides = quizData.d.sl.g[0].S;

console.log('--- Slide 2 (Câu 2) ---');
console.log(JSON.stringify(slides[1], null, 2));

console.log('--- Slide 10 (Câu 10) ---');
console.log(JSON.stringify(slides[9], null, 2));

console.log('--- Slide 13 (Câu 13) ---');
console.log(JSON.stringify(slides[12], null, 2));
