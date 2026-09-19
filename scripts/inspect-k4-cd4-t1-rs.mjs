import fs from 'node:fs';
import path from 'node:path';

const raw = fs.readFileSync('public/legacy/ic3/grade-4/gs6sparkl2-cd4-t1/data/quiz1.js', 'utf8');
const match = raw.match(/var quizInfo = "([^"]+)"/);
const q = JSON.parse(Buffer.from(match[1], 'base64').toString('utf8'));
console.log('gs6sparkl2-cd4-t1 rs:');
console.log(JSON.stringify(q.rs, null, 2));
