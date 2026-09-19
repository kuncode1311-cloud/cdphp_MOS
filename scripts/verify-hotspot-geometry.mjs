import fs from 'node:fs';

const quizFile = process.argv[2];
if (!quizFile || !fs.existsSync(quizFile)) {
    console.error('Cách dùng: node scripts/verify-hotspot-geometry.mjs <quiz1.js>');
    process.exit(2);
}

const source = fs.readFileSync(quizFile, 'utf8');
const encoded = source.match(/var quizInfo = "([^"]+)"/)?.[1];
const quiz = encoded ? JSON.parse(Buffer.from(encoded, 'base64').toString('utf8')) : null;
const slides = quiz?.d?.sl?.g?.[0]?.S ?? [];
const failures = [];
const results = [];

function normalizeRect(rect) {
    if (!rect) return null;
    let { x = 0, y = 0, w = 0, h = 0 } = rect;
    if (x > 100 || y > 100 || w > 100 || h > 100) {
        x /= 100;
        y /= 100;
        w /= 100;
        h /= 100;
    }

    return { x, y, w, h };
}

function findArea(areas, point) {
    let match = -1;
    areas.forEach((area, index) => {
        const rect = normalizeRect(area.r);
        if (rect && point.x >= rect.x && point.x <= rect.x + rect.w
            && point.y >= rect.y && point.y <= rect.y + rect.h) {
            match = index;
        }
    });

    return match;
}

slides.forEach((slide, slideIndex) => {
    if (slide.tp !== 'Hotspot') return;

    const areas = slide.C?.a ?? [];
    const correct = areas.flatMap((area, index) => area.c ? [index] : []);
    if (correct.length !== 1) {
        failures.push(`Câu ${slideIndex + 1}: cần đúng 1 vùng đáp án, thực tế ${correct.length}`);
    }

    areas.forEach((area, areaIndex) => {
        const rect = normalizeRect(area.r);
        if (!rect || rect.w <= 0 || rect.h <= 0 || rect.x < 0 || rect.y < 0
            || rect.x + rect.w > 100.000001 || rect.y + rect.h > 100.000001) {
            failures.push(`Câu ${slideIndex + 1}, vùng ${areaIndex}: tọa độ không hợp lệ`);
            return;
        }
        const center = { x: rect.x + rect.w / 2, y: rect.y + rect.h / 2 };
        if (findArea(areas, center) !== areaIndex) {
            failures.push(`Câu ${slideIndex + 1}, vùng ${areaIndex}: tâm vùng không map về chính nó`);
        }
    });

    results.push({
        question: slideIndex + 1,
        areas: areas.length,
        correct_position: correct[0] ?? null,
    });
});

console.log(JSON.stringify({
    hotspot_questions: results.length,
    results,
    failures,
}, null, 2));

process.exit(failures.length === 0 ? 0 : 1);
