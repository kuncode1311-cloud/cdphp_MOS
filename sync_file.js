import fs from 'fs';

const src = 'c:/laragon/www/MOS/tri_kun.docx';
const dest1 = 'E:/Desktop/trikun/CD_php/trí kun.docx';
const dest2 = 'E:/Desktop/trikun/CD_php/trí kun_ban_moi.docx';

function tryCopy() {
    try {
        if (fs.existsSync(src)) {
            try { fs.copyFileSync(src, dest1); console.log('[SYNC] Copied to trí kun.docx'); } catch(e){}
            try { fs.copyFileSync(src, dest2); console.log('[SYNC] Copied to trí kun_ban_moi.docx'); } catch(e){}
        }
    } catch (e) {}
    setTimeout(tryCopy, 3000);
}

tryCopy();
