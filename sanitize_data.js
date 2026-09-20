import fs from 'fs';

const files = ['report_chapter1_data.js', 'report_chapter2_data.js', 'report_chapter3_data.js'];

for (const file of files) {
    let content = fs.readFileSync(file, 'utf8');
    // Replace all backticks inside template strings with single quotes
    // Match each template string enclosed by ` ... `
    // But since these files only use backticks for multi-line string properties:
    // Let's replace internal backticks in content properties
    const updated = content.replace(/content:\s*`([\s\S]*?)`/g, (match, p1) => {
        return 'content: `' + p1.replace(/`/g, "'") + '`';
    }).replace(/code:\s*`([\s\S]*?)`/g, (match, p1) => {
        return 'code: `' + p1.replace(/`/g, "'") + '`';
    }).replace(/appendixA:\s*`([\s\S]*?)`/g, (match, p1) => {
        return 'appendixA: `' + p1.replace(/`/g, "'") + '`';
    }).replace(/appendixB:\s*`([\s\S]*?)`/g, (match, p1) => {
        return 'appendixB: `' + p1.replace(/`/g, "'") + '`';
    });
    fs.writeFileSync(file, updated);
    console.log(`Sanitized ${file}`);
}
