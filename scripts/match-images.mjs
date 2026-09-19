import fs from 'fs';

const code = fs.readFileSync('public/legacy/ic3/grade-3/gs6sparkl1-cd1-t1/data/quiz1.js', 'utf8');
const match = code.match(/var quizInfo = "([^"]+)"/);
const rawJson = Buffer.from(match[1], 'base64').toString('utf8');
const quiz = JSON.parse(rawJson);
const slides = quiz.d?.sl?.g?.[0]?.S || [];
const resources = quiz.rs?.i || {};

slides.forEach((s, idx) => {
    const sStr = JSON.stringify(s);
    const matches = sStr.match(/img-[a-f0-9]+\.(?:png|jpe?g|gif|webp)/gi) || [];
    console.log(`\nQ${idx + 1} (${s.tp}): "${s.D?.d?.[0]}"`);
    console.log(`  Found images in slide:`, [...new Set(matches)]);
});
