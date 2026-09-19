import fs from 'fs';

const code = fs.readFileSync('public/legacy/ic3/grade-3/gs6sparkl1-cd1-t1/data/quiz1.js', 'utf8');
const match = code.match(/var quizInfo = "([^"]+)"/);
if (match) {
    const rawJson = Buffer.from(match[1], 'base64').toString('utf8');
    const quiz = JSON.parse(rawJson);
    const s13 = quiz.d?.sl?.g?.[0]?.S?.[12]; // Slide 13

    console.log('Slide 13 JSON string:');
    console.log(JSON.stringify(s13));

    console.log('\n--- All resources in quiz.rs ---');
    console.log(JSON.stringify(quiz.rs, null, 2));
}
