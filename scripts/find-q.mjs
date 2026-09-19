import fs from 'node:fs';

const content = fs.readFileSync('public/legacy/ic3/grade-3/gs6sparkl1-cd1-t1/data/quiz1.js', 'utf8');
const match = content.match(/var quizInfo = "([^"]+)"/);
const json = JSON.parse(Buffer.from(match[1], 'base64').toString('utf8'));
const questions = json.d.sl.g[0].S;

console.log('=== GS6 Spark L1 CD1-T1 QUESTIONS (Total:', questions.length, ') ===');
questions.forEach((q, i) => {
  const title = q.D?.d?.[0] || q.D?.h?.replace(/<[^>]+>/g, '') || 'No title';
  console.log(`${i + 1}. [${q.tp}] ${title.trim()}`);
});
