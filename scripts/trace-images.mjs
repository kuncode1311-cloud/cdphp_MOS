import fs from 'fs';

const code = fs.readFileSync('public/legacy/ic3/grade-3/gs6sparkl1-cd1-t1/data/quiz1.js', 'utf8');
const match = code.match(/var quizInfo = "([^"]+)"/);
const rawJson = Buffer.from(match[1], 'base64').toString('utf8');
const quiz = JSON.parse(rawJson);
const slides = quiz.d?.sl?.g?.[0]?.S || [];
const images = fs.readdirSync('public/legacy/ic3/grade-3/gs6sparkl1-cd1-t1/data/quiz1/images');

console.log('Total decoded length:', rawJson.length);
images.forEach(img => {
    const baseName = img.replace(/\.(png|jpg|jpeg)/, '');
    const count = (rawJson.match(new RegExp(baseName, 'g')) || []).length;
    console.log(`Image ${img}: occurrences in JSON = ${count}`);
});
