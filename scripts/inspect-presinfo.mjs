import fs from 'node:fs';
import path from 'node:path';
import zlib from 'node:zlib';

const root = process.cwd();
const localBase = path.join(root, 'public/legacy/ic3/grade-3/gs6sparkl1-cd1-t1');
const indexHtml = fs.readFileSync(path.join(localBase, 'index.html'), 'utf8');

const match = indexHtml.match(/var presInfo = "([^"]+)"/);
const decoded = zlib.inflateSync(Buffer.from(match[1], 'base64')).toString('utf8');
console.log('Decoded presInfo length:', decoded.length);
console.log('Contains 25103011:', decoded.includes('25103011'));
console.log('Sample decoded presInfo:', decoded.slice(0, 2000));
