import fs from 'fs';

const code = fs.readFileSync('public/legacy/ic3/grade-3/gs6sparkl1-cd1-t1/data/quiz1.js', 'utf8');
const match = code.match(/var quizInfo = "([^"]+)"/);
const rawJson = Buffer.from(match[1], 'base64').toString('utf8');
const quiz = JSON.parse(rawJson);
const s13 = quiz.d?.sl?.g?.[0]?.S?.[12];

function findKey(obj, path = '') {
    if (!obj) return;
    if (typeof obj === 'string') {
        if (obj.includes('img-3d0919de')) {
            console.log(`Found image at path: ${path} => "${obj}"`);
        }
    } else if (typeof obj === 'object') {
        for (const k in obj) {
            findKey(obj[k], path ? `${path}.${k}` : k);
        }
    }
}

findKey(s13);
findKey(quiz.rs);
