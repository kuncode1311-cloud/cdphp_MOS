import fs from 'fs';

const code = fs.readFileSync('public/legacy/ic3/grade-3/gs6sparkl1-cd1-t1/data/quiz1.js', 'utf8');
const match = code.match(/var quizInfo = "([^"]+)"/);
if (match) {
    const rawJson = Buffer.from(match[1], 'base64').toString('utf8');
    const quiz = JSON.parse(rawJson);
    const slides = quiz.d?.sl?.g?.[0]?.S || [];
    const resources = quiz.rs?.i || {};
    
    console.log('Resources count:', Object.keys(resources).length);
    console.log('Sample resources:', Object.entries(resources).slice(0, 10));

    const s13 = slides[12]; // Q13
    console.log('\n--- Q13 details ---');
    console.log('s13 keys:', Object.keys(s13));
    console.log('s13.a:', JSON.stringify(s13.a, null, 2));

    const s6 = slides[5]; // Hotspot Q6
    console.log('\n--- Q6 Hotspot details ---');
    console.log('s6.a:', JSON.stringify(s6.a, null, 2));

    const s4 = slides[3]; // Q4 (laptop image?)
    console.log('\n--- Q4 details ---');
    console.log('s4.a:', JSON.stringify(s4.a, null, 2));
}
