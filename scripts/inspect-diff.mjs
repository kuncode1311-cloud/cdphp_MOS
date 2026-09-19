import fs from 'node:fs';
import path from 'node:path';

// 1. Check index.html difference for Khối 3 Test 1
const localIndex = fs.readFileSync('public/legacy/ic3/grade-3/gs6sparkl1-cd1-t1/index.html', 'utf8');
const resp = await fetch('https://ic3review.iigvietnam.edu.vn/newdata/GS6SparkL1/GS6SparkL1-CD1-T1%20%28Published%29/index.html');
const onlineIndex = await resp.text();

console.log('Local length:', localIndex.length, 'Online length:', onlineIndex.length);
console.log('Local first 200 chars:\n', localIndex.slice(0, 200));
console.log('Online first 200 chars:\n', onlineIndex.slice(0, 200));

// Compare line by line or diff
const localLines = localIndex.split('\n');
const onlineLines = onlineIndex.split('\n');
console.log('Local line count:', localLines.length, 'Online line count:', onlineLines.length);

for (let i = 0; i < Math.min(localLines.length, onlineLines.length); i++) {
  if (localLines[i].trim() !== onlineLines[i].trim()) {
    console.log(`Diff at line ${i + 1}:\nLOCAL : ${localLines[i].slice(0, 100)}\nONLINE: ${onlineLines[i].slice(0, 100)}`);
    break;
  }
}

// 2. Check the 3 missing images from Khối 4 CD4 T1 online
const testBase = 'https://ic3review.iigvietnam.edu.vn/newdata/GS6SparkL2/GS6SparkL2-CD4-T1%20(Published)/';
const missingImgs = [
  'img-f893476c01ce68c7517d0d29fd92e59922797fd6.png',
  'img-de3a3b025aaf0146c529042850ac6e2f01a7a6d0.png',
  'img-2b8887a01e6bc0efc238aabeebdafb256f506fee.png'
];

for (const img of missingImgs) {
  const urlsToTry = [
    `${testBase}data/quiz1/images/${img}`,
    `${testBase}data/images/${img}`,
    `${testBase}data/${img}`
  ];
  for (const u of urlsToTry) {
    const r = await fetch(u);
    console.log(`Checking ${u} -> Status: ${r.status}`);
  }
}
