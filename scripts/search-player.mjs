import fs from 'fs';

const player = fs.readFileSync('public/legacy/ic3/grade-3/gs6sparkl1-cd1-t1/data/quizplayer.js', 'utf8');

// Find references to puzzle, tab, socket, svg path, matching
const matches = player.match(/.{0,100}(?:puzzle|socket|tab|Matching|matching|drag-drop|drag|drop).{0,100}/gi) || [];
console.log('Total matches found:', matches.length);
console.log('Sample matches:', matches.slice(0, 10));
