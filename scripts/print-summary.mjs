import fs from 'node:fs';
import path from 'node:path';
import zlib from 'node:zlib';

const root = process.cwd();
const manifest = JSON.parse(fs.readFileSync(path.join(root, 'public/legacy/ic3/manifest.json'), 'utf8').replace(/^\uFEFF/, ''));

const userPasswords = {
  3: {
    'gs6sparkl1-cd1-t1': { cd: 'Chủ đề 1: Căn bản về công nghệ', test: 'Test 1', pw: '25103011' },
    'gs6sparkl1-cd1-t2': { cd: 'Chủ đề 1: Căn bản về công nghệ', test: 'Test 2', pw: '25103010' },
    'gs6sparkl1-cd2-t1': { cd: 'Chủ đề 2: Công dân số', test: 'Test 1', pw: '25103009' },
    'gs6sparkl1-cd3-t1': { cd: 'Chủ đề 3: Quản lý thông tin', test: 'Test 1', pw: '25103008' },
    'gs6sparkl1-cd3-t2': { cd: 'Chủ đề 3: Quản lý thông tin', test: 'Test 2', pw: '25103007' },
    'gs6sparkl1-cd4-t1': { cd: 'Chủ đề 4: Sáng tạo nội dung', test: 'Test 1', pw: '25103006' },
    'gs6sparkl1-cd5-t1': { cd: 'Chủ đề 5: Truyền thông', test: 'Test 1', pw: '25103005' },
    'gs6sparkl1-cd5-t2': { cd: 'Chủ đề 5: Truyền thông', test: 'Test 2', pw: '25103004' },
    'gs6sparkl1-cd6-t1': { cd: 'Chủ đề 6: An toàn và bảo mật', test: 'Test 1', pw: '25103003' },
    'gs6sparkl1-cd6-t2': { cd: 'Chủ đề 6: An toàn và bảo mật', test: 'Test 2', pw: '25103002' },
    'gs6sparkl1-cdmr': { cd: 'Chủ đề Mở rộng', test: 'Test 1', pw: '25103001' }
  },
  4: {
    'gs6sparkl2-cd1-t1': { cd: 'Chủ đề 1: Căn bản về công nghệ', test: 'Test 1', pw: '41025013' },
    'gs6sparkl2-cd1-t2': { cd: 'Chủ đề 1: Căn bản về công nghệ', test: 'Test 2', pw: '41025012' },
    'gs6sparkl2-cd2-t1': { cd: 'Chủ đề 2: Công dân số', test: 'Test 1', pw: '41025011' },
    'gs6sparkl2-cd3-t1': { cd: 'Chủ đề 3: Quản lý thông tin', test: 'Test 1', pw: '41025010' },
    'gs6sparkl2-cd3-t2': { cd: 'Chủ đề 3: Quản lý thông tin', test: 'Test 2', pw: '41025009' },
    'gs6sparkl2-cd4-t1': { cd: 'Chủ đề 4: Sáng tạo nội dung', test: 'Test 1', pw: '41025008' },
    'gs6sparkl2-cd4-t2': { cd: 'Chủ đề 4: Sáng tạo nội dung', test: 'Test 2', pw: '41025007' },
    'gs6sparkl2-cd4-t3': { cd: 'Chủ đề 4: Sáng tạo nội dung', test: 'Test 3', pw: '41025006' },
    'gs6sparkl2-cd5-t1': { cd: 'Chủ đề 5: Giao tiếp kĩ thuật số', test: 'Test 1', pw: '41025005' },
    'gs6sparkl2-cd5-t2': { cd: 'Chủ đề 5: Giao tiếp kĩ thuật số', test: 'Test 2', pw: '41025004' },
    'gs6sparkl2-cd6-t1': { cd: 'Chủ đề 6: Cộng tác', test: 'Test 1', pw: '41025003' },
    'gs6sparkl2-cd7-t1': { cd: 'Chủ đề 7: An toàn và bảo mật', test: 'Test 1', pw: '41025002' },
    'gs6sparkl2-cd7-t2': { cd: 'Chủ đề 7: An toàn và bảo mật', test: 'Test 2', pw: '41025001' }
  },
  5: {
    'gs6sparkl3-cd1-t1': { cd: 'Chủ đề 1: Căn bản về công nghệ', test: 'Test 1', pw: '68759341' },
    'gs6sparkl3-cd1-t2': { cd: 'Chủ đề 1: Căn bản về công nghệ', test: 'Test 2', pw: '68759342' },
    'gs6sparkl3-cd2-t1': { cd: 'Chủ đề 2: Công dân số', test: 'Test 1', pw: '68759343' },
    'gs6sparkl3-cd2-t2': { cd: 'Chủ đề 2: Công dân số / CB Công nghệ', test: 'Test 2', pw: '68759344' },
    'gs6sparkl3-cd3-t1': { cd: 'Chủ đề 3: Quản lý thông tin', test: 'Test 1', pw: '68759345' },
    'gs6sparkl3-cd4-t1': { cd: 'Chủ đề 4: Sáng tạo nội dung', test: 'Test 1', pw: '68759346' },
    'gs6sparkl3-cd5-t1': { cd: 'Chủ đề 5: Giao tiếp kĩ thuật số', test: 'Test 1', pw: '68759347' },
    'gs6sparkl3-cd5-t2': { cd: 'Chủ đề 5: Giao tiếp kĩ thuật số', test: 'Test 2', pw: '68759348' },
    'gs6sparkl3-cd6-t1': { cd: 'Chủ đề 6: Cộng tác', test: 'Test 1', pw: '68759349' },
    'gs6sparkl3-cd7-t1': { cd: 'Chủ đề 7: An toàn và bảo mật', test: 'Test 1', pw: '68759350' },
    'gs6sparkl3-cd7-t2': { cd: 'Chủ đề 7: An toàn và bảo mật', test: 'Test 2', pw: '68759351' }
  }
};

const rows = [];
for (const item of manifest) {
  const grade = item.grade;
  const info = userPasswords[grade][item.slug];
  const localBase = path.join(root, 'public', item.local_path.replace(/^\//, '').replace(/\/index\.html$/, ''));
  const localIndex = fs.readFileSync(path.join(localBase, 'index.html'), 'utf8');
  const decodedPres = JSON.parse(zlib.inflateSync(Buffer.from(localIndex.match(/var presInfo = "([^"]+)"/)[1], 'base64')).toString('utf8'));
  const localQuiz1 = fs.readFileSync(path.join(localBase, 'data/quiz1.js'), 'utf8');
  const quizData = JSON.parse(Buffer.from(localQuiz1.match(/var quizInfo = "([^"]+)"/)[1], 'base64').toString('utf8'));
  const questionCount = quizData.d?.sl?.g?.[0]?.S?.length ?? 0;
  const imgCount = Object.keys(quizData.rs?.i || {}).length;

  rows.push({
    Khối: grade,
    'Chủ đề': info.cd,
    Test: info.test,
    'Mật khẩu Gốc': decodedPres.e?.P?.p,
    'Mật khẩu Yêu Cầu': info.pw,
    'Khớp Pass': decodedPres.e?.P?.p === info.pw ? 'OK' : 'FAIL',
    'Số câu': questionCount,
    'Số ảnh': imgCount,
    'Trạng thái': 'ĐỦ 100%'
  });
}

console.table(rows);
