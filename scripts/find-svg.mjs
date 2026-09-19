import fs from 'fs';

const player = fs.readFileSync('public/legacy/ic3/grade-3/gs6sparkl1-cd1-t1/data/quizplayer.js', 'utf8');

// Find all SVG path definitions in quizplayer.js
const regex = /<path[^>]+d="([^"]+)"/g;
let m;
const paths = [];
while ((m = regex.exec(player)) !== null) {
    paths.push(m[1]);
}
console.log('Total SVG paths:', paths.length);
console.log('Sample SVG paths:', paths.slice(0, 20));
