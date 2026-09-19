import fs from 'node:fs';
import path from 'node:path';

function walk(dir) {
  let results = [];
  const list = fs.readdirSync(dir);
  list.forEach(file => {
    const full = path.join(dir, file);
    const stat = fs.statSync(full);
    if (stat && stat.isDirectory()) results = results.concat(walk(full));
    else if (file === 'quiz1.js') results.push(full);
  });
  return results;
}

const files = walk('./public/legacy/ic3');
files.forEach(f => {
  const content = fs.readFileSync(f, 'utf8');
  const match = content.match(/var quizInfo = "([^"]+)"/);
  if (match) {
    const json = JSON.parse(Buffer.from(match[1], 'base64').toString('utf8'));
    const questions = json.d?.sl?.g?.[0]?.S || [];
    questions.forEach((q, idx) => {
      if (q.tp === 'Hotspot') {
        console.log(`File: ${f} -> Q${idx+1} [Hotspot]:`, q.C?.a?.map(x => ({ l: x.l, c: x.c, r: x.r })));
      }
    });
  }
});
