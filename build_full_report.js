import {
    Document,
    Packer,
    Paragraph,
    TextRun,
    HeadingLevel,
    Table,
    TableRow,
    TableCell,
    WidthType,
    BorderStyle,
    AlignmentType,
    ImageRun,
    Header,
    Footer,
    PageNumber,
    PageBreak,
    ShadingType,
    NumberFormat,
    TabStopType,
    TabStopPosition,
    LeaderType
} from 'docx';
import fs from 'fs';
import path from 'path';

import { preliminaryData } from './report_preliminary_data.js';
import { introAndChapter1 } from './report_chapter1_data.js';
import { chapter2Data } from './report_chapter2_data.js';
import { databaseTables } from './report_tables_data.js';
import { chapter3And4Data } from './report_chapter3_data.js';

console.log("Compiling complete Word report: 'trí kun.docx'...");

// --- HÀM TIỆN ÍCH TẠO PARAGRAPH, TEXT VÀ ĐỊNH DẠNG ---

const FONT_FAMILY = "Times New Roman";

function p(text, options = {}) {
    const runs = Array.isArray(text) ? text : [new TextRun({
        text: text,
        font: FONT_FAMILY,
        size: options.size || 26, // 13pt = 26 half-points
        bold: options.bold || false,
        italics: options.italics || false,
        color: options.color || "111827"
    })];

    // Thụt đầu dòng 1.27cm (720 dxa) theo đúng thể thức văn bản học thuật Việt Nam
    let indentOpt = undefined;
    if (options.indent !== undefined) {
        indentOpt = options.indent;
    } else if (options.firstLine !== undefined) {
        indentOpt = { firstLine: options.firstLine };
    } else if (!options.noIndent && !options.alignment && !options.bold) {
        indentOpt = { firstLine: 720 };
    }

    return new Paragraph({
        children: runs,
        alignment: options.alignment || AlignmentType.JUSTIFIED,
        indent: indentOpt,
        spacing: {
            line: options.lineSpacing || 360, // 1.5 lines
            lineRule: "auto",
            before: options.before !== undefined ? options.before : 40,
            after: options.after !== undefined ? options.after : 80
        }
    });
}

function h1(text) {
    return new Paragraph({
        heading: HeadingLevel.HEADING_1,
        alignment: AlignmentType.LEFT,
        spacing: { before: 280, after: 140, line: 360 },
        children: [
            new TextRun({
                text: text.toUpperCase(),
                font: FONT_FAMILY,
                size: 32, // 16pt
                bold: true,
                color: "1E3A8A" // Deep Navy Blue
            })
        ]
    });
}

function h2(text) {
    return new Paragraph({
        heading: HeadingLevel.HEADING_2,
        alignment: AlignmentType.LEFT,
        spacing: { before: 220, after: 100, line: 360 },
        children: [
            new TextRun({
                text: text,
                font: FONT_FAMILY,
                size: 28, // 14pt
                bold: true,
                color: "1D4ED8" // Royal Blue
            })
        ]
    });
}

function h3(text) {
    return new Paragraph({
        heading: HeadingLevel.HEADING_3,
        alignment: AlignmentType.LEFT,
        spacing: { before: 160, after: 80, line: 360 },
        children: [
            new TextRun({
                text: text,
                font: FONT_FAMILY,
                size: 26, // 13pt
                bold: true,
                color: "0F172A" // Dark Slate
            })
        ]
    });
}

function tocItem(title, page, level = 0, isBold = false) {
    let indentDxa = 0;
    if (level === 1) indentDxa = 360; // 0.63 cm
    else if (level === 2) indentDxa = 720; // 1.27 cm
    else if (level === 3) indentDxa = 1080;

    return new Paragraph({
        tabStops: [
            {
                type: TabStopType.RIGHT,
                position: 9026,
                leader: LeaderType.DOT
            }
        ],
        indent: { left: indentDxa },
        spacing: {
            line: 300,
            before: level === 0 ? 60 : 20,
            after: level === 0 ? 30 : 20
        },
        children: [
            new TextRun({
                text: title,
                font: FONT_FAMILY,
                size: level === 0 ? 25 : 23,
                bold: isBold || level === 0,
                color: level === 0 ? "1E3A8A" : "1F2937"
            }),
            new TextRun("\t"),
            new TextRun({
                text: String(page),
                font: FONT_FAMILY,
                size: 23,
                bold: isBold || level === 0,
                color: level === 0 ? "1E3A8A" : "374151"
            })
        ]
    });
}

function imageParagraph(imagePath, caption, width = 460, height = 288) {
    const paras = [];
    if (fs.existsSync(imagePath)) {
        try {
            const imgBuffer = fs.readFileSync(imagePath);
            paras.push(new Paragraph({
                alignment: AlignmentType.CENTER,
                keepWithNext: true,
                spacing: { before: 140, after: 60 },
                children: [
                    new ImageRun({
                        data: imgBuffer,
                        transformation: { width, height },
                        type: "png"
                    })
                ]
            }));
            paras.push(new Paragraph({
                alignment: AlignmentType.CENTER,
                spacing: { before: 40, after: 180 },
                children: [
                    new TextRun({
                        text: caption,
                        font: FONT_FAMILY,
                        size: 22, // 11pt
                        italics: true,
                        bold: true,
                        color: "475569"
                    })
                ]
            }));
        } catch (e) {
            console.error("Lỗi khi chèn ảnh:", imagePath, e.message);
        }
    }
    return paras;
}

function tableGrid(headers, rows, colWidths = []) {
    const tableRows = [];
    const totalWidthDxa = 9070;

    let colDxaWidths = [];
    if (colWidths && colWidths.length > 0) {
        const sumPct = colWidths.reduce((a, b) => a + b, 0);
        let accumulated = 0;
        colDxaWidths = colWidths.map((pct, idx) => {
            if (idx === colWidths.length - 1) {
                return totalWidthDxa - accumulated;
            }
            const w = Math.round((pct / sumPct) * totalWidthDxa);
            accumulated += w;
            return w;
        });
    } else {
        const equalWidth = Math.floor(totalWidthDxa / headers.length);
        colDxaWidths = headers.map((_, idx) => idx === headers.length - 1 ? totalWidthDxa - equalWidth * (headers.length - 1) : equalWidth);
    }

    // Header row
    const headerCells = headers.map((h, i) => new TableCell({
        children: [
            new Paragraph({
                alignment: AlignmentType.CENTER,
                spacing: { before: 50, after: 50 },
                children: [
                    new TextRun({
                        text: h,
                        font: FONT_FAMILY,
                        size: 23, // 11.5pt
                        bold: true,
                        color: "FFFFFF"
                    })
                ]
            })
        ],
        shading: { fill: "1E3A8A", type: ShadingType.CLEAR },
        margins: { top: 90, bottom: 90, left: 110, right: 110 },
        width: { size: colDxaWidths[i], type: WidthType.DXA }
    }));
    tableRows.push(new TableRow({ children: headerCells }));

    // Data rows
    rows.forEach((r, rIdx) => {
        const isEven = rIdx % 2 === 0;
        const dataCells = r.map((cellText, cIdx) => new TableCell({
            children: [
                new Paragraph({
                    alignment: cIdx === 0 ? AlignmentType.CENTER : AlignmentType.LEFT,
                    spacing: { before: 30, after: 30 },
                    children: [
                        new TextRun({
                            text: String(cellText),
                            font: FONT_FAMILY,
                            size: 22, // 11pt
                            color: "1F2937"
                        })
                    ]
                })
            ],
            shading: isEven ? { fill: "F8FAFC", type: ShadingType.CLEAR } : { fill: "FFFFFF", type: ShadingType.CLEAR },
            margins: { top: 70, bottom: 70, left: 90, right: 90 },
            width: { size: colDxaWidths[cIdx], type: WidthType.DXA }
        }));
        tableRows.push(new TableRow({ children: dataCells }));
    });

    return new Table({
        width: { size: totalWidthDxa, type: WidthType.DXA },
        columnWidths: colDxaWidths,
        rows: tableRows,
        borders: {
            top: { style: BorderStyle.SINGLE, size: 6, color: "1E3A8A" },
            bottom: { style: BorderStyle.SINGLE, size: 6, color: "1E3A8A" },
            left: { style: BorderStyle.SINGLE, size: 6, color: "CBD5E1" },
            right: { style: BorderStyle.SINGLE, size: 6, color: "CBD5E1" },
            insideHorizontal: { style: BorderStyle.SINGLE, size: 4, color: "E2E8F0" },
            insideVertical: { style: BorderStyle.SINGLE, size: 4, color: "E2E8F0" }
        }
    });
}

function codeBox(title, code) {
    const lines = code.trim().split("\n");
    const paras = [
        new Paragraph({
            spacing: { before: 100, after: 40 },
            children: [
                new TextRun({
                    text: `Mã nguồn: ${title}`,
                    font: FONT_FAMILY,
                    size: 24,
                    bold: true,
                    color: "1E40AF"
                })
            ]
        })
    ];

    const codeLines = lines.map(line => new Paragraph({
        spacing: { before: 10, after: 10, line: 240 },
        children: [
            new TextRun({
                text: line,
                font: "Consolas",
                size: 19, // 9.5pt
                color: "1E293B"
            })
        ]
    }));

    paras.push(new Table({
        width: { size: 9070, type: WidthType.DXA },
        columnWidths: [9070],
        rows: [
            new TableRow({
                children: [
                    new TableCell({
                        children: codeLines,
                        shading: { fill: "F8FAFC", type: ShadingType.CLEAR },
                        margins: { top: 140, bottom: 140, left: 160, right: 160 },
                        width: { size: 9070, type: WidthType.DXA }
                    })
                ]
            })
        ],
        borders: {
            top: { style: BorderStyle.SINGLE, size: 6, color: "94A3B8" },
            bottom: { style: BorderStyle.SINGLE, size: 6, color: "94A3B8" },
            left: { style: BorderStyle.SINGLE, size: 18, color: "2563EB" }, // Thick blue left border
            right: { style: BorderStyle.SINGLE, size: 6, color: "94A3B8" }
        }
    }));

    paras.push(new Paragraph({ spacing: { after: 120 } }));
    return paras;
}

// --- 1. SECTION TRANG BÌA (COVER SECTION) ---

function buildCoverSection() {
    const children = [];

    children.push(new Paragraph({
        alignment: AlignmentType.CENTER,
        spacing: { before: 40, after: 20 },
        children: [new TextRun({ text: "BỘ GIÁO DỤC VÀ ĐÀO TẠO", font: FONT_FAMILY, size: 26, bold: true })]
    }));
    children.push(new Paragraph({
        alignment: AlignmentType.CENTER,
        spacing: { before: 10, after: 20 },
        children: [new TextRun({ text: "TRƯỜNG CAO ĐẲNG CÔNG NGHỆ THÔNG TIN TP.HCM", font: FONT_FAMILY, size: 28, bold: true, color: "1E3A8A" })]
    }));
    children.push(new Paragraph({
        alignment: AlignmentType.CENTER,
        spacing: { before: 10, after: 100 },
        children: [new TextRun({ text: "KHOA CÔNG NGHỆ THÔNG TIN", font: FONT_FAMILY, size: 24, bold: true })]
    }));

    // Logo trường
    const logoPath = "c:\\laragon\\www\\MOS\\storage\\app\\extracted_media\\image1.png";
    if (fs.existsSync(logoPath)) {
        const logoBuf = fs.readFileSync(logoPath);
        children.push(new Paragraph({
            alignment: AlignmentType.CENTER,
            spacing: { before: 60, after: 100 },
            children: [
                new ImageRun({
                    data: logoBuf,
                    transformation: { width: 110, height: 100 },
                    type: "png"
                })
            ]
        }));
    }

    children.push(new Paragraph({
        alignment: AlignmentType.CENTER,
        spacing: { before: 80, after: 20 },
        children: [new TextRun({ text: "BÁO CÁO ĐỒ ÁN MÔN HỌC", font: FONT_FAMILY, size: 30, bold: true, color: "1E40AF" })]
    }));
    children.push(new Paragraph({
        alignment: AlignmentType.CENTER,
        spacing: { before: 10, after: 80 },
        children: [new TextRun({ text: "CHUYÊN ĐỀ LẬP TRÌNH PHP", font: FONT_FAMILY, size: 26, bold: true })]
    }));

    children.push(new Paragraph({
        alignment: AlignmentType.CENTER,
        spacing: { before: 20, after: 10 },
        children: [new TextRun({ text: "ĐỀ TÀI:", font: FONT_FAMILY, size: 24, bold: true })]
    }));
    children.push(new Paragraph({
        alignment: AlignmentType.CENTER,
        spacing: { before: 10, after: 140 },
        children: [
            new TextRun({
                text: "XÂY DỰNG HỆ THỐNG HỌC TẬP VÀ LUYỆN THI CHỨNG CHỈ TIN HỌC QUỐC TẾ IC3 SPARK & MOS TRỰC TUYẾN (IC3 QUEST)",
                font: FONT_FAMILY,
                size: 28,
                bold: true,
                color: "B91C1C" // Crimson Red
            })
        ]
    }));

    // Bảng thông tin sinh viên & giảng viên
    const metaTable = new Table({
        width: { size: 9070, type: WidthType.DXA },
        columnWidths: [3800, 5270],
        alignment: AlignmentType.CENTER,
        borders: {
            top: { style: BorderStyle.SINGLE, size: 6, color: "1E3A8A" },
            bottom: { style: BorderStyle.SINGLE, size: 6, color: "1E3A8A" },
            left: { style: BorderStyle.SINGLE, size: 6, color: "1E3A8A" },
            right: { style: BorderStyle.SINGLE, size: 6, color: "1E3A8A" },
            insideHorizontal: { style: BorderStyle.SINGLE, size: 4, color: "CBD5E1" },
            insideVertical: { style: BorderStyle.SINGLE, size: 4, color: "CBD5E1" }
        },
        rows: [
            new TableRow({
                children: [
                    new TableCell({
                        children: [new Paragraph({ alignment: AlignmentType.CENTER, children: [new TextRun({ text: "THÔNG TIN HƯỚNG DẪN VÀ THỰC HIỆN", font: FONT_FAMILY, size: 22, bold: true, color: "1E3A8A" })] })],
                        shading: { fill: "DBEAFE", type: ShadingType.CLEAR },
                        width: { size: 3800, type: WidthType.DXA },
                        margins: { top: 40, bottom: 40, left: 100, right: 100 }
                    }),
                    new TableCell({
                        children: [new Paragraph({ alignment: AlignmentType.CENTER, children: [new TextRun({ text: "CHI TIẾT", font: FONT_FAMILY, size: 22, bold: true, color: "1E3A8A" })] })],
                        shading: { fill: "DBEAFE", type: ShadingType.CLEAR },
                        width: { size: 5270, type: WidthType.DXA },
                        margins: { top: 40, bottom: 40, left: 100, right: 100 }
                    })
                ]
            }),
            new TableRow({
                children: [
                    new TableCell({ children: [new Paragraph({ children: [new TextRun({ text: "Giảng viên hướng dẫn:", font: FONT_FAMILY, size: 22, bold: true })] })], width: { size: 3800, type: WidthType.DXA }, margins: { top: 35, bottom: 35, left: 90, right: 90 } }),
                    new TableCell({ children: [new Paragraph({ children: [new TextRun({ text: preliminaryData.instructor, font: FONT_FAMILY, size: 22, bold: true, color: "1E3A8A" })] })], width: { size: 5270, type: WidthType.DXA }, margins: { top: 35, bottom: 35, left: 90, right: 90 } })
                ]
            }),
            new TableRow({
                children: [
                    new TableCell({ children: [new Paragraph({ children: [new TextRun({ text: "Sinh viên thực hiện 1 (Nhóm trưởng):", font: FONT_FAMILY, size: 22, bold: true })] })], width: { size: 3800, type: WidthType.DXA }, margins: { top: 35, bottom: 35, left: 90, right: 90 } }),
                    new TableCell({ children: [new Paragraph({ children: [new TextRun({ text: "Lê Minh Trí           MSSV: (Tạm để trống)", font: FONT_FAMILY, size: 22 })] })], width: { size: 5270, type: WidthType.DXA }, margins: { top: 35, bottom: 35, left: 90, right: 90 } })
                ]
            }),
            new TableRow({
                children: [
                    new TableCell({ children: [new Paragraph({ children: [new TextRun({ text: "Sinh viên thực hiện 2:", font: FONT_FAMILY, size: 22, bold: true })] })], width: { size: 3800, type: WidthType.DXA }, margins: { top: 35, bottom: 35, left: 90, right: 90 } }),
                    new TableCell({ children: [new Paragraph({ children: [new TextRun({ text: "Âu Lê Thành Tài        MSSV: (Tạm để trống)", font: FONT_FAMILY, size: 22 })] })], width: { size: 5270, type: WidthType.DXA }, margins: { top: 35, bottom: 35, left: 90, right: 90 } })
                ]
            }),
            new TableRow({
                children: [
                    new TableCell({ children: [new Paragraph({ children: [new TextRun({ text: "Sinh viên thực hiện 3:", font: FONT_FAMILY, size: 22, bold: true })] })], width: { size: 3800, type: WidthType.DXA }, margins: { top: 35, bottom: 35, left: 90, right: 90 } }),
                    new TableCell({ children: [new Paragraph({ children: [new TextRun({ text: "Võ Trung Kiều Diễm     MSSV: (Tạm để trống)", font: FONT_FAMILY, size: 22 })] })], width: { size: 5270, type: WidthType.DXA }, margins: { top: 35, bottom: 35, left: 90, right: 90 } })
                ]
            }),
            new TableRow({
                children: [
                    new TableCell({ children: [new Paragraph({ children: [new TextRun({ text: "Ngành học:", font: FONT_FAMILY, size: 22, bold: true })] })], width: { size: 3800, type: WidthType.DXA }, margins: { top: 35, bottom: 35, left: 90, right: 90 } }),
                    new TableCell({ children: [new Paragraph({ children: [new TextRun({ text: preliminaryData.major, font: FONT_FAMILY, size: 22 })] })], width: { size: 5270, type: WidthType.DXA }, margins: { top: 35, bottom: 35, left: 90, right: 90 } })
                ]
            }),
            new TableRow({
                children: [
                    new TableCell({ children: [new Paragraph({ children: [new TextRun({ text: "Lớp / Khóa:", font: FONT_FAMILY, size: 22, bold: true })] })], width: { size: 3800, type: WidthType.DXA }, margins: { top: 35, bottom: 35, left: 90, right: 90 } }),
                    new TableCell({ children: [new Paragraph({ children: [new TextRun({ text: preliminaryData.classroom, font: FONT_FAMILY, size: 22, bold: true })] })], width: { size: 5270, type: WidthType.DXA }, margins: { top: 35, bottom: 35, left: 90, right: 90 } })
                ]
            })
        ]
    });
    children.push(metaTable);

    children.push(new Paragraph({
        alignment: AlignmentType.CENTER,
        spacing: { before: 80, after: 20 },
        children: [new TextRun({ text: preliminaryData.locationDate, font: FONT_FAMILY, size: 24, bold: true })]
    }));

    return {
        properties: {
            page: {
                margin: { top: 1134, bottom: 1134, left: 1701, right: 1134 }
            }
        },
        children: children
    };
}

// --- 2. SECTION TRANG SƠ BỘ (PRELIMINARY SECTION - ROMAN PAGE NUMBERING) ---

function buildPreliminarySection() {
    const children = [];

    // --- LỜI CẢM ƠN ---
    children.push(h1("LỜI CẢM ƠN"));
    preliminaryData.loiCamOn.split("\n\n").forEach(para => {
        children.push(p(para, { size: 25, lineSpacing: 300, before: 30, after: 40 }));
    });
    children.push(p("TP. Hồ Chí Minh, ngày 14 tháng 09 năm 2026", { alignment: AlignmentType.RIGHT, italics: true, before: 60, after: 20, size: 24 }));
    children.push(p("Tập thể nhóm sinh viên thực hiện:", { alignment: AlignmentType.RIGHT, bold: true, before: 10, after: 10, size: 24 }));
    children.push(p("Lê Minh Trí – Âu Lê Thành Tài – Võ Trung Kiều Diễm", { alignment: AlignmentType.RIGHT, bold: true, color: "1E3A8A", before: 10, after: 20, size: 24 }));
    children.push(new Paragraph({ children: [new PageBreak()] }));

    // --- LỜI CAM ĐOAN ---
    children.push(h1("LỜI CAM ĐOAN"));
    preliminaryData.loiCamDoan.split("\n\n").forEach(para => {
        children.push(p(para, { size: 25, lineSpacing: 300, before: 30, after: 40 }));
    });
    children.push(p("TP. Hồ Chí Minh, ngày 14 tháng 09 năm 2026", { alignment: AlignmentType.RIGHT, italics: true, before: 80, after: 20, size: 24 }));
    children.push(p("Đại diện nhóm sinh viên (Nhóm trưởng)", { alignment: AlignmentType.RIGHT, bold: true, before: 10, after: 10, size: 24 }));
    children.push(p("Lê Minh Trí", { alignment: AlignmentType.RIGHT, bold: true, color: "1E3A8A", before: 10, after: 20, size: 24 }));
    children.push(new Paragraph({ children: [new PageBreak()] }));

    // --- NHẬN XÉT CỦA GIẢNG VIÊN HƯỚNG DẪN ---
    children.push(h1("NHẬN XÉT CỦA GIẢNG VIÊN HƯỚNG DẪN"));
    children.push(p("Họ và tên giảng viên hướng dẫn: Thầy Huỳnh Luân", { bold: true, before: 20, after: 15, size: 25 }));
    children.push(p("Đơn vị công tác: Khoa Công nghệ Thông tin – Trường Cao đẳng Công nghệ Thông tin TP.HCM (ITC)", { italics: true, before: 10, after: 15, size: 25 }));
    children.push(p("Tên đề tài đồ án: XÂY DỰNG HỆ THỐNG HỌC TẬP VÀ LUYỆN THI CHỨNG CHỈ TIN HỌC QUỐC TẾ IC3 SPARK & MOS TRỰC TUYẾN (IC3 QUEST)", { bold: true, color: "1E3A8A", before: 10, after: 15, size: 25 }));
    children.push(p("Sinh viên thực hiện: Lê Minh Trí (Nhóm trưởng) – Âu Lê Thành Tài – Võ Trung Kiều Diễm", { bold: true, before: 10, after: 25, size: 25 }));
    children.push(p("Ý kiến nhận xét của giảng viên hướng dẫn về quá trình thực hiện đồ án:", { bold: true, before: 15, after: 10, size: 25 }));
    children.push(p("1. Về tinh thần, thái độ làm việc, ý thức kỷ luật và tính tự giác của nhóm sinh viên:\n........................................................................................................................................................................\n........................................................................................................................................................................", { before: 10, after: 20, size: 24, lineSpacing: 280 }));
    children.push(p("2. Về năng lực ứng dụng công nghệ (PHP 8.3, Laravel 11, MySQL, TailwindCSS, Gamification):\n........................................................................................................................................................................\n........................................................................................................................................................................", { before: 10, after: 20, size: 24, lineSpacing: 280 }));
    children.push(p("3. Về quy mô, độ hoàn thiện chức năng và tính ứng dụng thực tiễn của sản phẩm phần mềm:\n........................................................................................................................................................................\n........................................................................................................................................................................", { before: 10, after: 20, size: 24, lineSpacing: 280 }));
    children.push(p("4. Bố cục, hình thức và tính chuẩn mực trong phương pháp trình bày báo cáo:\n........................................................................................................................................................................\n........................................................................................................................................................................", { before: 10, after: 20, size: 24, lineSpacing: 280 }));
    children.push(p("Điểm đánh giá: ............ / 10.0 (Bằng chữ: .................................................................................)", { bold: true, before: 25, after: 15, size: 25 }));
    children.push(p("Kết luận:   [  ] ĐỒNG Ý CHO BẢO VỆ          [  ] KHÔNG ĐỒNG Ý CHO BẢO VỆ", { bold: true, before: 10, after: 25, size: 25 }));
    children.push(p("TP. Hồ Chí Minh, ngày ...... tháng ...... năm 2026", { alignment: AlignmentType.RIGHT, italics: true, before: 15, after: 10, size: 24 }));
    children.push(p("GIẢNG VIÊN HƯỚNG DẪN", { alignment: AlignmentType.RIGHT, bold: true, before: 10, after: 10, size: 25 }));
    children.push(p("(Ký và ghi rõ họ tên)", { alignment: AlignmentType.RIGHT, italics: true, before: 10, after: 80, size: 23 }));
    children.push(p("Thầy Huỳnh Luân", { alignment: AlignmentType.RIGHT, bold: true, color: "1E3A8A", before: 60, after: 20, size: 25 }));
    children.push(new Paragraph({ children: [new PageBreak()] }));

    // --- NHẬN XÉT CỦA GIẢNG VIÊN PHẢN BIỆN ---
    children.push(h1("NHẬN XÉT CỦA GIẢNG VIÊN PHẢN BIỆN"));
    children.push(p("Họ và tên giảng viên phản biện: .....................................................................................................................", { bold: true, before: 20, after: 15, size: 25 }));
    children.push(p("Tên đề tài: XÂY DỰNG HỆ THỐNG HỌC TẬP VÀ LUYỆN THI CHỨNG CHỈ TIN HỌC QUỐC TẾ IC3 SPARK & MOS TRỰC TUYẾN (IC3 QUEST)", { bold: true, before: 10, after: 15, size: 25 }));
    children.push(p("Sinh viên thực hiện: Lê Minh Trí (Nhóm trưởng) – Âu Lê Thành Tài – Võ Trung Kiều Diễm", { bold: true, before: 10, after: 25, size: 25 }));
    children.push(p("Ý kiến nhận xét của giảng viên phản biện:", { bold: true, before: 15, after: 10, size: 25 }));
    children.push(p("1. Tính khoa học và sự phù hợp của các phương pháp nghiên cứu áp dụng trong đề tài:\n........................................................................................................................................................................\n........................................................................................................................................................................", { before: 10, after: 20, size: 24, lineSpacing: 280 }));
    children.push(p("2. Chất lượng nội dung thuyết minh và mức độ hoàn thiện sản phẩm phần mềm:\n........................................................................................................................................................................\n........................................................................................................................................................................", { before: 10, after: 20, size: 24, lineSpacing: 280 }));
    children.push(p("3. Những ưu điểm nổi bật và những thiếu sót cần khắc phục, hoàn thiện thêm:\n........................................................................................................................................................................\n........................................................................................................................................................................", { before: 10, after: 20, size: 24, lineSpacing: 280 }));
    children.push(p("4. Các câu hỏi chất vấn sinh viên khi bảo vệ trước Hội đồng chấm đồ án:\n- Câu 1: ....................................................................................................................................................................................................................\n- Câu 2: ....................................................................................................................................................................................................................", { before: 10, after: 20, size: 24, lineSpacing: 280 }));
    children.push(p("Điểm đánh giá: ............ / 10.0 (Bằng chữ: .................................................................................)", { bold: true, before: 25, after: 15, size: 25 }));
    children.push(p("TP. Hồ Chí Minh, ngày ...... tháng ...... năm 2026", { alignment: AlignmentType.RIGHT, italics: true, before: 15, after: 10, size: 24 }));
    children.push(p("GIẢNG VIÊN PHẢN BIỆN", { alignment: AlignmentType.RIGHT, bold: true, before: 10, after: 10, size: 25 }));
    children.push(p("(Ký và ghi rõ họ tên)", { alignment: AlignmentType.RIGHT, italics: true, before: 10, after: 80, size: 23 }));
    children.push(new Paragraph({ children: [new PageBreak()] }));

    // --- LỊCH LÀM VIỆC CỦA NHÓM SINH VIÊN ---
    children.push(h1("LỊCH LÀM VIỆC CỦA NHÓM SINH VIÊN"));
    children.push(p("Bảng phân công nhiệm vụ và tiến độ triển khai thực hiện đồ án (Từ tháng 07/2026 đến tháng 09/2026):"));
    const scheduleRows = preliminaryData.workSchedule.map(s => [s.week, s.content, s.inCharge, s.result]);
    children.push(tableGrid(["THỜI GIAN", "NỘI DUNG CÔNG VIỆC CHI TIẾT", "NGƯỜI PHỤ TRÁCH", "KẾT QUẢ ĐẠT ĐƯỢC"], scheduleRows, [16, 44, 18, 22]));
    children.push(new Paragraph({ children: [new PageBreak()] }));

    // --- TÓM TẮT ĐỒ ÁN ---
    children.push(h1("TÓM TẮT ĐỒ ÁN"));
    children.push(h2("1. Vấn đề được đưa ra giải quyết trong đồ án"));
    children.push(p("- Khảo sát thực trạng ôn tập và thi chứng chỉ tin học quốc tế IC3 Spark tại các trường tiểu học hiện nay: chỉ ra các bất cập lớn như thiếu môi trường thi thử chuẩn quốc tế, giáo viên chịu gánh nặng chấm bài thủ công, phụ huynh thiếu công cụ giám sát và học sinh thiếu động lực tự học."));
    children.push(p("- Thiết kế và xây dựng một nền tảng học tập kết hợp trò chơi (Gamification LMS) hoàn chỉnh gồm 4 phân hệ: Quản trị viên, Giáo viên, Học sinh và Phụ huynh; tích hợp 35 bộ đề thi với 509 câu hỏi chất lượng cao chuẩn IC3 GS6."));
    children.push(p("- Xây dựng cơ chế tích lũy Sao thưởng và cửa hàng đổi phút chơi mini-game giáo dục, đồng thời tích hợp cổng thanh toán VietQR / PayOS tự động và kết nối Webhook Telegram Bot tư vấn Live Chat."));

    children.push(h2("2. Mục tiêu khoa học và thực tiễn đã đạt được"));
    children.push(p("- Vận hành thành công ứng dụng web IC3 Quest bám sát 100% chuẩn kiến thức IC3 GS6 Spark của Certiport (Hoa Kỳ) với thang điểm 1000 chuẩn IIG."));
    children.push(p("- Triển khai thuật toán xáo trộn câu hỏi và đáp án (Shuffle) chống gian lận, đồng hồ đếm ngược JS thời gian thực và tự động khóa/nộp bài khi hết giờ."));
    children.push(p("- Xây dựng Parent Dashboard chuyên sâu với biểu đồ Radar 7 chủ đề, biểu đồ đường và hệ thống cảnh báo sớm giúp phụ huynh nắm rõ điểm yếu của con."));
    children.push(p("- Toàn bộ 10 kịch bản kiểm thử (Test Cases) cốt lõi của hệ thống đều vượt qua thành công với tỷ lệ 100% (PASS)."));

    children.push(h2("3. Các kỹ thuật công nghệ chính được sử dụng"));
    children.push(p("- Ngôn ngữ lập trình: PHP 8.3 (server-side), HTML5 ngữ nghĩa, CSS3 nâng cao, JavaScript ES6+ (client-side)."));
    children.push(p("- Framework phát triển: Laravel 11.x (sử dụng Eloquent ORM, Blade Component, Middleware, Sanctum, Queue, Event)."));
    children.push(p("- Hệ quản trị cơ sở dữ liệu: MySQL 8.x (chuẩn hóa 3NF gồm 17 bảng quan hệ logic chặt chẽ)."));
    children.push(p("- Giao diện Frontend: TailwindCSS, Game Theme 3D Cards tactile xúc giác, Alpine.js, Chart.js."));
    children.push(p("- Tích hợp số: Cổng thanh toán VietQR & PayOS API, Telegram Bot Webhook (@sp_trikun_bot)."));
    children.push(new Paragraph({ children: [new PageBreak()] }));

    // --- MỤC LỤC CHI TIẾT CHUẨN WORD (TAB STOPS VỚI DOT LEADER) ---
    children.push(h1("MỤC LỤC"));
    children.push(new Paragraph({
        tabStops: [{ type: TabStopType.RIGHT, position: 9026, leader: LeaderType.NONE }],
        spacing: { before: 80, after: 120 },
        children: [
            new TextRun({ text: "NỘI DUNG MỤC LỤC", font: FONT_FAMILY, size: 22, bold: true, color: "1E3A8A" }),
            new TextRun("\t"),
            new TextRun({ text: "TRANG", font: FONT_FAMILY, size: 22, bold: true, color: "1E3A8A" })
        ]
    }));

    const tocEntries = [
        { title: "LỜI CẢM ƠN", page: "I", level: 0, bold: true },
        { title: "LỜI CAM ĐOAN", page: "II", level: 0, bold: true },
        { title: "NHẬN XÉT CỦA GIẢNG VIÊN HƯỚNG DẪN", page: "III", level: 0, bold: true },
        { title: "NHẬN XÉT CỦA GIẢNG VIÊN PHẢN BIỆN", page: "IV", level: 0, bold: true },
        { title: "LỊCH LÀM VIỆC CỦA NHÓM SINH VIÊN", page: "V", level: 0, bold: true },
        { title: "TÓM TẮT ĐỒ ÁN", page: "VI", level: 0, bold: true },
        { title: "DANH MỤC SƠ ĐỒ, LƯỢC ĐỒ, BIỂU ĐỒ, HÌNH VẼ", page: "VII", level: 0, bold: true },
        { title: "DANH MỤC BẢNG BIỂU SỬ DỤNG", page: "VIII", level: 0, bold: true },
        { title: "KÍ HIỆU CÁC CỤM TỪ VIẾT TẮT", page: "IX", level: 0, bold: true },
        { title: "LỜI MỞ ĐẦU", page: "1", level: 0, bold: true },
        { title: "Chương 1. TỔNG QUAN TÀI LIỆU", page: "4", level: 0, bold: true },
        { title: "1.1. Khảo sát bài toán thực tế", page: "4", level: 1, bold: true },
        { title: "1.1.1. Thực trạng tổ chức đào tạo và thi chứng chỉ tin học quốc tế", page: "4", level: 2, bold: false },
        { title: "1.1.2. Đề xuất giải pháp xây dựng hệ thống IC3 Quest", page: "5", level: 2, bold: false },
        { title: "1.1.3. Mục tiêu cụ thể của đề tài tốt nghiệp", page: "6", level: 2, bold: false },
        { title: "1.1.4. Khảo sát các hệ thống thi trực tuyến hiện nay", page: "7", level: 2, bold: false },
        { title: "1.2. Cơ sở lý thuyết công nghệ", page: "8", level: 1, bold: true },
        { title: "1.2.1. Ngôn ngữ lập trình PHP 8.3", page: "8", level: 2, bold: false },
        { title: "1.2.2. Framework phát triển Laravel 11.x", page: "9", level: 2, bold: false },
        { title: "1.2.3. Hệ quản trị cơ sở dữ liệu MySQL 8.x", page: "10", level: 2, bold: false },
        { title: "1.2.4. Công nghệ Frontend hiện đại và Phong cách 3D Gamification", page: "11", level: 2, bold: false },
        { title: "1.2.5. Cổng thanh toán trực tuyến PayOS và Chuẩn VietQR", page: "12", level: 2, bold: false },
        { title: "1.2.6. Tích hợp Telegram Bot Webhook & Live Chat Realtime", page: "13", level: 2, bold: false },
        { title: "1.2.7. Trình soạn thảo mã nguồn Visual Studio Code", page: "14", level: 2, bold: false },
        { title: "1.2.8. Môi trường máy chủ cục bộ Laragon", page: "14", level: 2, bold: false },
        { title: "Chương 2. PHƯƠNG PHÁP THỰC HIỆN", page: "15", level: 0, bold: true },
        { title: "2.1. Phân tích hệ thống nghiệp vụ", page: "15", level: 1, bold: true },
        { title: "2.1.1. Quy trình nghiệp vụ 5 bước cốt lõi", page: "15", level: 2, bold: false },
        { title: "2.1.2. Yêu cầu chức năng của hệ thống", page: "17", level: 2, bold: false },
        { title: "2.1.3. Sơ đồ khối chức năng chi tiết theo từng vai trò", page: "19", level: 2, bold: false },
        { title: "2.2. Các biểu đồ thiết kế hệ thống (UML Diagrams)", page: "21", level: 1, bold: true },
        { title: "2.2.1. Lược đồ Use Case tổng thể toàn hệ thống", page: "21", level: 2, bold: false },
        { title: "2.2.2. Đặc tả chi tiết 12 Use Cases nghiệp vụ", page: "22", level: 2, bold: false },
        { title: "2.2.3. Lược đồ hoạt động (Activity Diagrams) cho các Use Cases", page: "32", level: 2, bold: false },
        { title: "2.3. Thiết kế Cơ sở dữ liệu chi tiết", page: "35", level: 1, bold: true },
        { title: "2.3.1. Sơ đồ thực thể mối quan hệ (ERD)", page: "35", level: 2, bold: false },
        { title: "2.3.2. Báo cáo đặc tả cấu trúc chi tiết của 17 bảng CSDL trong MySQL", page: "37", level: 2, bold: false },
        { title: "Chương 3. CÀI ĐẶT THỰC NGHIỆM VÀ KẾT QUẢ", page: "48", level: 0, bold: true },
        { title: "3.1. Môi trường cài đặt hệ thống", page: "48", level: 1, bold: true },
        { title: "3.2. Một số giao diện chính và phân tích chức năng", page: "49", level: 1, bold: true },
        { title: "3.2.1. Phân hệ Xác thực & Cổng thông tin công khai (Hình 3.1, 3.14, 3.15)", page: "49", level: 2, bold: false },
        { title: "3.2.2. Phân hệ Quản trị viên - Admin Portal (Hình 3.2, 3.3, 3.4, 3.5, 3.6)", page: "52", level: 2, bold: false },
        { title: "3.2.3. Phân hệ Học sinh - Cổng học tập & Gamification (Hình 3.7 - 3.12, 3.16, 3.17)", page: "56", level: 2, bold: false },
        { title: "3.2.4. Phân hệ Phụ huynh - Giám sát tiến độ học tập (Hình 3.13)", page: "62", level: 2, bold: false },
        { title: "3.3. Các đoạn mã nguồn lập trình tiêu biểu", page: "63", level: 1, bold: true },
        { title: "3.3.1. Thuật toán chấm điểm và quy đổi điểm chuẩn IC3 (thang 1000 điểm)", page: "63", level: 2, bold: false },
        { title: "3.3.2. Thuật toán trộn ngẫu nhiên (Shuffle) câu hỏi và đáp án", page: "65", level: 2, bold: false },
        { title: "3.3.3. Xử lý thanh toán VietQR / PayOS và Webhook đồng bộ tự động", page: "66", level: 2, bold: false },
        { title: "3.3.4. Hệ thống Live Chat Realtime Polling & Bắn thông báo Telegram Bot", page: "68", level: 2, bold: false },
        { title: "3.3.5. Cơ chế Gamification: Khấu trừ thời gian chơi mini-game từng giây", page: "69", level: 2, bold: false },
        { title: "3.4. Kiểm thử chất lượng hệ thống (Software Testing)", page: "71", level: 1, bold: true },
        { title: "3.4.1. Phương pháp kiểm thử hộp đen (Black-box Testing)", page: "71", level: 2, bold: false },
        { title: "3.4.2. Bảng kịch bản kiểm thử chi tiết cho 10 Test Cases chính", page: "71", level: 2, bold: false },
        { title: "Chương 4. KẾT LUẬN VÀ HƯỚNG PHÁT TRIỂN", page: "74", level: 0, bold: true },
        { title: "4.1. Đánh giá kết quả đồ án tốt nghiệp", page: "74", level: 1, bold: true },
        { title: "4.2. Định hướng và lộ trình phát triển trong tương lai", page: "76", level: 1, bold: true },
        { title: "PHỤ LỤC", page: "78", level: 0, bold: true },
        { title: "Phụ lục A: Nội dung tệp cấu hình môi trường (.env)", page: "78", level: 1, bold: false },
        { title: "Phụ lục B: Hướng dẫn chi tiết cài đặt và vận hành hệ thống trên Laragon", page: "79", level: 1, bold: false },
        { title: "DANH MỤC TÀI LIỆU THAM KHẢO", page: "81", level: 0, bold: true }
    ];

    tocEntries.forEach(item => {
        children.push(tocItem(item.title, item.page, item.level, item.bold));
    });
    children.push(new Paragraph({ children: [new PageBreak()] }));

    // --- DANH MỤC HÌNH ẢNH VÀ SƠ ĐỒ CHUẨN WORD (TAB STOPS VỚI DOT LEADER) ---
    children.push(h1("DANH MỤC SƠ ĐỒ, LƯỢC ĐỒ, BIỂU ĐỒ, HÌNH VẼ"));
    children.push(new Paragraph({
        tabStops: [{ type: TabStopType.RIGHT, position: 9026, leader: LeaderType.NONE }],
        spacing: { before: 80, after: 120 },
        children: [
            new TextRun({ text: "KÝ HIỆU VÀ TÊN HÌNH VẼ / SƠ ĐỒ MINH HỌA", font: FONT_FAMILY, size: 22, bold: true, color: "1E3A8A" }),
            new TextRun("\t"),
            new TextRun({ text: "TRANG", font: FONT_FAMILY, size: 22, bold: true, color: "1E3A8A" })
        ]
    }));

    const figRows = [
        ["Lược đồ 2.1", "Lược đồ Use Case tổng thể toàn hệ thống IC3 Quest", "21"],
        ["Lược đồ 2.2", "Lược đồ Activity chức năng Đăng nhập phân quyền hệ thống", "32"],
        ["Lược đồ 2.3", "Lược đồ Activity chức năng Thanh toán VietQR / PayOS tự động", "33"],
        ["Lược đồ 2.4", "Lược đồ Activity chức năng Học sinh làm bài phòng thi ảo & Chấm điểm", "34"],
        ["Lược đồ 2.5", "Lược đồ Activity chức năng Đổi Sao lấy phút chơi mini-game giải trí", "34"],
        ["Hình 2.1", "Sơ đồ Thực thể Mối quan hệ Cơ sở Dữ liệu (ERD) 17 bảng chuẩn MySQL", "36"],
        ["Hình 3.1", "Giao diện trang Đăng nhập phân quyền hệ thống (Auth Login)", "49"],
        ["Hình 3.2", "Giao diện Bảng điều khiển Quản trị viên (Admin Dashboard)", "52"],
        ["Hình 3.3", "Giao diện Quản lý Người dùng, Phân quyền Giáo viên và Lớp học", "53"],
        ["Hình 3.4", "Giao diện IC3 Question Studio - Quản trị 509 câu hỏi và bộ đề thi", "54"],
        ["Hình 3.5", "Giao diện Cài đặt Khu trò chơi & Tỷ lệ quy đổi Sao thưởng", "55"],
        ["Hình 3.6", "Giao diện Quản lý Gói dịch vụ & Đơn thuê bản quyền giáo viên", "56"],
        ["Hình 3.7", "Giao diện Cổng học tập học sinh - Hero Banner & 4 Thẻ lối tắt 3D", "57"],
        ["Hình 3.8", "Giao diện Danh mục khóa học & Lưới khối lớp đào tạo", "58"],
        ["Hình 3.9", "Giao diện Bản đồ 7 chủ đề kiến thức chuẩn quốc tế Khối 3", "59"],
        ["Hình 3.10", "Giao diện Thẻ giới thiệu arcade 3D & Chuẩn bị vào phòng thi", "60"],
        ["Hình 3.11", "Giao diện Bảng thành tích & Bảng xếp hạng Top 10 Hiệp sĩ nhí", "61"],
        ["Hình 3.12", "Giao diện Khu trò chơi giải trí & Cửa hàng đổi Sao lấy phút chơi", "62"],
        ["Hình 3.13", "Giao diện Bảng điều khiển Phụ huynh giám sát tiến độ học tập", "63"],
        ["Hình 3.14", "Giao diện Cổng Bảng giá dịch vụ và các gói bản quyền giáo viên", "50"],
        ["Hình 3.15", "Giao diện Thanh toán VietQR tự động và Cổng PayOS", "51"],
        ["Hình 3.16", "Giao diện Xem chi tiết nội dung bài luyện thi và danh sách câu hỏi", "60"],
        ["Hình 3.17", "Giao diện Lịch sử thuê gói và Quản lý thời hạn bản quyền", "61"]
    ];

    figRows.forEach(f => {
        children.push(tocItem(`${f[0]}: ${f[1]}`, f[2], 0, false));
    });
    children.push(new Paragraph({ children: [new PageBreak()] }));

    // --- DANH MỤC BẢNG BIỂU SỬ DỤNG CHUẨN WORD (TAB STOPS VỚI DOT LEADER) ---
    children.push(h1("DANH MỤC BẢNG BIỂU SỬ DỤNG"));
    children.push(new Paragraph({
        tabStops: [{ type: TabStopType.RIGHT, position: 9026, leader: LeaderType.NONE }],
        spacing: { before: 80, after: 120 },
        children: [
            new TextRun({ text: "KÝ HIỆU VÀ TÊN BẢNG BIỂU ĐẶC TẢ TRONG BÁO CÁO", font: FONT_FAMILY, size: 22, bold: true, color: "1E3A8A" }),
            new TextRun("\t"),
            new TextRun({ text: "TRANG", font: FONT_FAMILY, size: 22, bold: true, color: "1E3A8A" })
        ]
    }));

    const tableListRows = [
        ["Bảng 1.1", "Bảng so sánh tính năng giữa IC3 Quest và các hệ thống thi trực tuyến hiện nay", "7"],
        ["Bảng 2.1", "Bảng yêu cầu chức năng cốt lõi của hệ thống IC3 Quest (16 chức năng)", "17"],
        ["Bảng 2.2 đến 2.13", "Bảng đặc tả chi tiết 12 Use Cases nghiệp vụ của hệ thống", "22"],
        ["Bảng 2.14", "Đặc tả chi tiết bảng users (Tài khoản người dùng)", "37"],
        ["Bảng 2.15", "Đặc tả chi tiết bảng packages (Gói dịch vụ bản quyền)", "38"],
        ["Bảng 2.16", "Đặc tả chi tiết bảng package_level (Khối lớp cấp quyền của gói)", "39"],
        ["Bảng 2.17", "Đặc tả chi tiết bảng package_orders (Đơn hàng thuê gói & Thanh toán)", "39"],
        ["Bảng 2.18", "Đặc tả chi tiết bảng support_messages (Tin nhắn Live Chat & Telegram)", "40"],
        ["Bảng 2.19", "Đặc tả chi tiết bảng programs (Chương trình đào tạo)", "41"],
        ["Bảng 2.20", "Đặc tả chi tiết bảng levels (Khối lớp / Cấp độ đào tạo)", "41"],
        ["Bảng 2.21", "Đặc tả chi tiết bảng topics (7 Chủ đề kiến thức chuẩn IC3 GS6)", "42"],
        ["Bảng 2.22", "Đặc tả chi tiết bảng practice_tests (Ngân hàng 35 đề thi & bài luyện)", "43"],
        ["Bảng 2.23", "Đặc tả chi tiết bảng questions (Ngân hàng 509 câu hỏi)", "44"],
        ["Bảng 2.24", "Đặc tả chi tiết bảng question_options (Phương án lựa chọn trả lời)", "45"],
        ["Bảng 2.25", "Đặc tả chi tiết bảng question_assets (Tệp hình ảnh media đề thi)", "45"],
        ["Bảng 2.26", "Đặc tả chi tiết bảng test_attempts (Lượt thi & Lịch sử làm bài)", "46"],
        ["Bảng 2.27", "Đặc tả chi tiết bảng classrooms (Danh sách lớp học phụ trách)", "47"],
        ["Bảng 2.28", "Đặc tả chi tiết bảng teacher_level (Phân quyền khối lớp cho giáo viên)", "47"],
        ["Bảng 2.29", "Đặc tả chi tiết bảng game_transactions (Nhật ký Sao thưởng & Giờ chơi)", "48"],
        ["Bảng 2.30", "Đặc tả chi tiết bảng game_settings (Cấu hình hệ thống & Trò chơi)", "48"],
        ["Bảng 3.1", "Thông số chi tiết môi trường cài đặt phần mềm", "48"],
        ["Bảng 3.2", "Bảng kịch bản kiểm thử phần mềm hệ thống (10 Test Cases chính)", "71"]
    ];

    tableListRows.forEach(t => {
        children.push(tocItem(`${t[0]}: ${t[1]}`, t[2], 0, false));
    });
    children.push(new Paragraph({ children: [new PageBreak()] }));

    // --- KÍ HIỆU CÁC CỤM TỪ VIẾT TẮT ---
    children.push(h1("KÍ HIỆU CÁC CỤM TỪ VIẾT TẮT"));
    const abbrRows = preliminaryData.abbreviations.map(a => [a.term, a.full, a.meaning]);
    children.push(tableGrid(["TỪ VIẾT TẮT", "CỤM TỪ TIẾNG ANH ĐẦY ĐỦ", "Ý NGHĨA CHUYÊN MÔN"], abbrRows, [16, 36, 48]));
    children.push(new Paragraph({ children: [new PageBreak()] }));

    return {
        properties: {
            page: {
                margin: { top: 1134, bottom: 1134, left: 1701, right: 1134 },
                pageNumbers: {
                    formatType: NumberFormat.UPPER_ROMAN,
                    start: 1
                }
            }
        },
        children: children
    };
}

// --- 3. SECTION NỘI DUNG CHÍNH (MAIN BODY SECTION - ARABIC PAGE NUMBERING) ---

function buildMainBodySection() {
    const children = [];

    // --- LỜI MỞ ĐẦU ---
    children.push(h1(introAndChapter1.loiMoDau.title));
    introAndChapter1.loiMoDau.content.forEach(pText => children.push(p(pText)));

    children.push(h2("Tính cấp thiết của đề tài"));
    introAndChapter1.loiMoDau.urgency.split("\n").forEach(line => children.push(p(line)));

    children.push(h2("Mục tiêu nghiên cứu của đề tài tốt nghiệp"));
    introAndChapter1.loiMoDau.objectives.split("\n").forEach(line => children.push(p(line)));

    children.push(h2("Đối tượng và phạm vi nghiên cứu"));
    introAndChapter1.loiMoDau.scope.split("\n").forEach(line => children.push(p(line)));

    children.push(h2("Kết cấu thuyết minh đồ án tốt nghiệp"));
    introAndChapter1.loiMoDau.structure.split("\n").forEach(line => children.push(p(line)));
    children.push(new Paragraph({ children: [new PageBreak()] }));

    // --- CHƯƠNG 1: TỔNG QUAN TÀI LIỆU ---
    children.push(h1(introAndChapter1.chapter1.title));
    introAndChapter1.chapter1.sections.forEach(sec => {
        children.push(h2(sec.title));
        sec.subsections.forEach(sub => {
            children.push(h3(sub.title));
            sub.content.split("\n\n").forEach(pText => {
                if (pText.includes("Bảng 1.1:")) {
                    children.push(p(pText));
                    // Chèn bảng so sánh
                    const compareTable = tableGrid(
                        ["Tiêu chí so sánh", "Google Forms", "Azota", "GMetrix / TestPrep", "IC3 Quest (MOS)"],
                        [
                            ["Giao diện & Trải nghiệm", "Đơn điệu, khảo sát", "Phẳng thông thường", "Giao diện cũ, phức tạp", "Giao diện 3D Gamified rực rỡ, hấp dẫn học sinh"],
                            ["Độ tương thích chuẩn IC3", "Thấp", "Trung bình", "Rất cao, chuẩn Certiport", "Chuẩn IC3 GS6 Spark 7 chủ đề, thang 1000 điểm"],
                            ["Yếu tố Gamification", "Không có", "Rất hạn chế", "Không có", "Sao thưởng, Huy hiệu, Đua top hiệp sĩ, Đổi giờ chơi mini-game"],
                            ["Góc phụ huynh giám sát", "Không có", "Xem điểm cơ bản", "Không có", "Dashboard chuyên sâu, biểu đồ radar/doughnut, cảnh báo sớm"],
                            ["Thanh toán nội địa", "Không có", "Thẻ cào / Chuyển khoản", "Thẻ tín dụng USD", "VietQR quét mã tức thì & Cổng PayOS hoàn toàn tự động"],
                            ["Hỗ trợ khách hàng", "Không có", "Email / Zalo chậm", "Ticket tiếng Anh", "Live Chat trên web đồng bộ Telegram Bot realtime"],
                            ["Chi phí sử dụng", "Miễn phí", "Trả phí theo gói", "Rất đắt (50$ - 100$/code)", "Chi phí tối ưu, chính sách linh hoạt cho trường học"]
                        ],
                        [22, 18, 18, 20, 22]
                    );
                    children.push(compareTable);
                } else {
                    children.push(p(pText));
                }
            });
        });
    });
    children.push(new Paragraph({ children: [new PageBreak()] }));

    // --- CHƯƠNG 2: PHƯƠNG PHÁP THỰC HIỆN ---
    children.push(h1(chapter2Data.title));
    chapter2Data.sections.forEach(sec => {
        children.push(h2(sec.title));
        sec.subsections.forEach(sub => {
            children.push(h3(sub.title));
            if (sub.content) {
                sub.content.split("\n\n").forEach(pText => children.push(p(pText)));
            }

            // Bảng yêu cầu chức năng
            if (sub.tableFunctionalRequirements) {
                const reqRows = sub.tableFunctionalRequirements.map(r => [r.stt, r.role, r.name, r.desc, r.priority]);
                children.push(tableGrid(["STT", "VAI TRÒ", "TÊN CHỨC NĂNG NGHIỆP VỤ", "MÔ TẢ CHI TIẾT HOẠT ĐỘNG HỆ THỐNG", "ĐỘ ƯU TIÊN"], reqRows, [8, 16, 26, 38, 12]));
            }

            // Các bảng Use Cases
            if (sub.useCases) {
                sub.useCases.forEach((uc, ucIdx) => {
                    children.push(p(`Bảng 2.${ucIdx + 2}: Đặc tả Use Case ${uc.id} - ${uc.name}`, { bold: true, color: "1E3A8A", before: 80 }));
                    const ucTable = tableGrid(
                        ["THÀNH PHẦN ĐẶC TẢ", "NỘI DUNG ĐẶC TẢ CHI TIẾT NGHIỆP VỤ"],
                        [
                            ["Mã Use Case", uc.id],
                            ["Tên Use Case", uc.name],
                            ["Tác nhân (Actors)", uc.actor],
                            ["Mục đích (Goal)", uc.goal],
                            ["Tiền điều kiện", uc.precondition],
                            ["Luồng sự kiện chính", uc.mainFlow],
                            ["Luồng phụ / Ngoại lệ", uc.altFlow],
                            ["Hậu điều kiện", uc.postcondition]
                        ],
                        [25, 75]
                    );
                    children.push(ucTable);
                    children.push(new Paragraph({ spacing: { after: 60 } }));
                });
            }
        });
    });

    // Chèn hình ảnh Sơ đồ ERD
    const erdPath = "c:\\laragon\\www\\MOS\\storage\\app\\extracted_media\\image2.png";
    children.push(...imageParagraph(erdPath, "Hình 2.1: Sơ đồ Thực thể Mối quan hệ Cơ sở Dữ liệu (ERD) 17 bảng chuẩn MySQL", 440, 270));

    // Chèn 17 bảng đặc tả CSDL
    databaseTables.forEach(table => {
        children.push(p(`Bảng ${table.num}: Đặc tả cấu trúc chi tiết bảng \`${table.name}\` (${table.meaning})`, { bold: true, color: "1E3A8A", before: 80 }));
        const colRows = table.columns.map(c => [c.col, c.type, c.key, c.null, c.def, c.desc]);
        const tableObj = tableGrid(
            ["TÊN CỘT", "KIỂU DỮ LIỆU", "KHÓA", "NULL", "MẶC ĐỊNH", "MÔ TẢ Ý NGHĨA NGHIỆP VỤ"],
            colRows,
            [18, 20, 10, 8, 14, 30]
        );
        children.push(tableObj);
        children.push(new Paragraph({ spacing: { after: 60 } }));
    });
    children.push(new Paragraph({ children: [new PageBreak()] }));

    // --- CHƯƠNG 3: CÀI ĐẶT THỰC NGHIỆM VÀ KẾT QUẢ ---
    children.push(h1("Chương 3. CÀI ĐẶT THỰC NGHIỆM VÀ KẾT QUẢ"));
    children.push(h2("3.1. Môi trường cài đặt hệ thống"));
    children.push(p("Hệ thống thi kiểm tra trực tuyến IC3 Quest đã được triển khai cài đặt và vận hành ổn định trên môi trường máy chủ cục bộ Laragon phục vụ công tác nghiệm thu đồ án. Các thông số chi tiết của môi trường được tổng hợp trong bảng dưới đây:"));

    const envRows = chapter3And4Data.envTable.map(e => [e.item, e.version, e.role]);
    children.push(tableGrid(["THÀNH PHẦN MÔI TRƯỜNG", "PHIÊN BẢN CÀI ĐẶT", "VAI TRÒ HOẠT ĐỘNG CHUYÊN MÔN TRONG HỆ THỐNG"], envRows, [26, 26, 48]));
    children.push(new Paragraph({ spacing: { after: 120 } }));

    children.push(h2("3.2. Một số giao diện chính và phân tích chức năng"));
    children.push(p("Dưới đây là phần trình bày chi tiết và phân tích chức năng toàn bộ 17 giao diện đặc trưng của hệ thống IC3 Quest tương ứng với 4 phân hệ người dùng. Tất cả các hình ảnh đều được chụp trực tiếp từ ứng dụng web đang vận hành thực tế:"));

    // --- 3.2.1. PHÂN HỆ XÁC THỰC & CỔNG THÔNG TIN CÔNG KHAI ---
    children.push(h3("3.2.1. Phân hệ Xác thực & Cổng thông tin công khai"));
    children.push(p("Phân hệ quản lý việc bảo mật định danh người dùng và giới thiệu dịch vụ. Cung cấp trang đăng nhập bảo vệ CSRF đa vai trò và cổng thương mại dịch vụ cho phép người dùng tự do tra cứu bảng giá bản quyền và thực hiện giao dịch chuyển khoản VietQR tự động."));
    const group1 = ["hinh3_01_dang_nhap.png", "hinh3_14_bang_gia_dich_vu.png", "hinh3_15_thanh_toan_vietqr.png"];
    group1.forEach((fName, idx) => {
        const sc = chapter3And4Data.screenshots.find(s => s.file === fName);
        if (sc) {
            const letter = String.fromCharCode(97 + idx); // a, b, c
            children.push(new Paragraph({
                spacing: { before: 160, after: 80, line: 360 },
                children: [
                    new TextRun({
                        text: `${letter}. Giao diện Hình ${sc.num}: ${sc.title}`,
                        font: FONT_FAMILY,
                        size: 26,
                        bold: true,
                        color: "1E3A8A"
                    })
                ]
            }));
            children.push(p(sc.desc));
            const shotPath = `c:\\laragon\\www\\MOS\\storage\\app\\report_screenshots\\${sc.file}`;
            children.push(...imageParagraph(shotPath, `Hình ${sc.num}: ${sc.title}`, 460, 288));
        }
    });

    // --- 3.2.2. PHÂN HỆ QUẢN TRỊ VIÊN - ADMIN PORTAL ---
    children.push(h3("3.2.2. Phân hệ Quản trị viên - Admin Portal"));
    children.push(p("Phân hệ dành riêng cho ban quản trị và giáo viên phụ trách trường. Hệ thống cung cấp trung tâm điều hành Dashboard trực quan, công cụ phân quyền giáo viên theo khối lớp, module chuyên sâu IC3 Question Studio quản trị 509 câu hỏi và 35 bộ đề thi, thiết lập tỷ lệ quy đổi Sao thưởng Gamification và quản lý đơn hàng thuê gói bản quyền."));
    const group2 = [
        "hinh3_02_dashboard_admin.png",
        "hinh3_03_quan_ly_nguoi_dung.png",
        "hinh3_04_ic3_question_studio.png",
        "hinh3_05_cai_dat_tro_choi.png",
        "hinh3_06_goi_dich_vu_admin.png"
    ];
    group2.forEach((fName, idx) => {
        const sc = chapter3And4Data.screenshots.find(s => s.file === fName);
        if (sc) {
            const letter = String.fromCharCode(97 + idx); // a, b, c, d, e
            children.push(new Paragraph({
                spacing: { before: 160, after: 80, line: 360 },
                children: [
                    new TextRun({
                        text: `${letter}. Giao diện Hình ${sc.num}: ${sc.title}`,
                        font: FONT_FAMILY,
                        size: 26,
                        bold: true,
                        color: "1E3A8A"
                    })
                ]
            }));
            children.push(p(sc.desc));
            const shotPath = `c:\\laragon\\www\\MOS\\storage\\app\\report_screenshots\\${sc.file}`;
            children.push(...imageParagraph(shotPath, `Hình ${sc.num}: ${sc.title}`, 460, 288));
        }
    });

    // --- 3.2.3. PHÂN HỆ HỌC SINH - CỔNG HỌC TẬP & GAMIFICATION ---
    children.push(h3("3.2.3. Phân hệ Học sinh - Cổng học tập & Gamification"));
    children.push(p("Phân hệ trung tâm của ứng dụng, được thiết kế theo phong cách game phiêu lưu giáo dục sống động với các thẻ 3D card tactile xúc giác rực rỡ. Học sinh được tiếp cận hệ thống lộ trình học chuẩn IC3 GS6, tham gia thi thử tại phòng thi ảo bấm giờ, tích lũy Sao thưởng danh giá, vinh danh trên Bảng xếp hạng Top 10 Hiệp sĩ nhí và tham gia đổi phút chơi mini-game giải trí lành mạnh."));
    const group3 = [
        "hinh3_07_cong_hoc_tap.png",
        "hinh3_08_danh_muc_khoa_hoc.png",
        "hinh3_09_ban_do_chu_de.png",
        "hinh3_10_chuan_bi_lam_bai.png",
        "hinh3_16_chi_tiet_bai_luyen.png",
        "hinh3_11_bang_thanh_tich.png",
        "hinh3_12_khu_tro_choi_doi_sao.png",
        "hinh3_17_lich_su_thue_goi.png"
    ];
    group3.forEach((fName, idx) => {
        const sc = chapter3And4Data.screenshots.find(s => s.file === fName);
        if (sc) {
            const letter = String.fromCharCode(97 + idx); // a, b, c, d, e, f, g, h
            children.push(new Paragraph({
                spacing: { before: 160, after: 80, line: 360 },
                children: [
                    new TextRun({
                        text: `${letter}. Giao diện Hình ${sc.num}: ${sc.title}`,
                        font: FONT_FAMILY,
                        size: 26,
                        bold: true,
                        color: "1E3A8A"
                    })
                ]
            }));
            children.push(p(sc.desc));
            const shotPath = `c:\\laragon\\www\\MOS\\storage\\app\\report_screenshots\\${sc.file}`;
            children.push(...imageParagraph(shotPath, `Hình ${sc.num}: ${sc.title}`, 460, 288));
        }
    });

    // --- 3.2.4. PHÂN HỆ PHỤ HUYNH - GIÁM SÁT TIẾN ĐỘ HỌC TẬP ---
    children.push(h3("3.2.4. Phân hệ Phụ huynh - Giám sát tiến độ học tập"));
    children.push(p("Parent Dashboard là công cụ đắc lực hỗ trợ cha mẹ đồng hành cùng quá trình tự học của con em. Giao diện trực quan hóa toàn bộ lịch sử thi cử thành các chỉ số năng lực theo 7 chủ đề kiến thức chuẩn quốc tế, nhận diện các dạng bài con hay sai và phát cảnh báo sớm thông minh."));
    const sc13 = chapter3And4Data.screenshots.find(s => s.file === "hinh3_13_goc_phu_huynh.png");
    if (sc13) {
        children.push(new Paragraph({
            spacing: { before: 160, after: 80, line: 360 },
            children: [
                new TextRun({
                    text: `a. Giao diện Hình ${sc13.num}: ${sc13.title}`,
                    font: FONT_FAMILY,
                    size: 26,
                    bold: true,
                    color: "1E3A8A"
                })
            ]
        }));
        children.push(p(sc13.desc));
        const shotPath = `c:\\laragon\\www\\MOS\\storage\\app\\report_screenshots\\${sc13.file}`;
        children.push(...imageParagraph(shotPath, `Hình ${sc13.num}: ${sc13.title}`, 460, 288));
    }

    children.push(h2("3.3. Các đoạn mã nguồn lập trình tiêu biểu"));
    children.push(p("Dưới đây là phần thuyết minh chi tiết 5 đoạn mã nguồn xử lý thuật toán cốt lõi được xây dựng trong hệ thống IC3 Quest, thể hiện kiến trúc hướng đối tượng, bảo mật và logic thông minh của đồ án:"));

    chapter3And4Data.codeSnippets.forEach(cs => {
        children.push(h3(`${cs.num}. ${cs.title}`));
        children.push(p(cs.desc));
        children.push(...codeBox(cs.title, cs.code));
    });

    children.push(h2("3.4. Kiểm thử chất lượng hệ thống (Software Testing)"));
    children.push(p("Sau khi hoàn thiện toàn bộ các tính năng lập trình của hệ thống IC3 Quest, nhóm đã tiến hành kiểm thử chất lượng phần mềm bằng phương pháp kiểm thử hộp đen (Black-box Testing) tập trung vào chức năng. Quy trình nhằm xác thực hệ thống phản hồi chính xác với mọi điều kiện đầu vào hợp lệ và không hợp lệ của người dùng. Dưới đây là đặc tả chi tiết kịch bản và kết quả kiểm thử của 10 Test Cases cốt lõi:"));

    const tcRows = chapter3And4Data.testCases.map(tc => [tc.code, tc.name, tc.input, tc.expected, tc.actual, tc.result]);
    children.push(tableGrid(
        ["MÃ TC", "CHỨC NĂNG KIỂM THỬ", "DỮ LIỆU VÀ ĐIỀU KIỆN ĐẦU VÀO", "KẾT QUẢ MONG ĐỢI", "KẾT QUẢ THỰC TẾ", "ĐÁNH GIÁ"],
        tcRows,
        [8, 18, 22, 24, 20, 8]
    ));
    children.push(new Paragraph({ children: [new PageBreak()] }));

    // --- CHƯƠNG 4: KẾT LUẬN VÀ HƯỚNG PHÁT TRIỂN ---
    children.push(h1(chapter3And4Data.chapter4.title));
    chapter3And4Data.chapter4.content.split("\n\n").forEach(block => {
        if (block.startsWith("4.1.") || block.startsWith("4.2.")) {
            children.push(h2(block.split("\n")[0]));
            block.split("\n").slice(1).forEach(l => children.push(p(l)));
        } else {
            block.split("\n").forEach(l => children.push(p(l)));
        }
    });
    children.push(new Paragraph({ children: [new PageBreak()] }));

    // --- PHỤ LỤC ---
    children.push(h1("PHỤ LỤC"));
    children.push(h2("Phụ lục A: Nội dung tệp cấu hình môi trường (.env)"));
    children.push(...codeBox(".env Configuration", chapter3And4Data.appendices.appendixA));

    children.push(h2("Phụ lục B: Hướng dẫn chi tiết cài đặt và vận hành hệ thống trên môi trường máy chủ cục bộ (Laragon)"));
    chapter3And4Data.appendices.appendixB.split("\n\n").forEach(step => {
        children.push(p(step));
    });
    children.push(new Paragraph({ children: [new PageBreak()] }));

    // --- TÀI LIỆU THAM KHẢO ---
    children.push(h1("DANH MỤC TÀI LIỆU THAM KHẢO"));
    children.push(p("Danh mục các tài liệu học thuật, giáo trình và tài liệu kỹ thuật chính thức được tham khảo và trích dẫn trong đồ án:"));
    const refRows = chapter3And4Data.references.map(r => [r.stt, r.author, r.title, r.publisher, r.year, r.note]);
    children.push(tableGrid(
        ["STT", "TÁC GIẢ / TỔ CHỨC", "TÊN TÀI LIỆU / GIÁO TRÌNH", "NHÀ XUẤT BẢN", "NĂM", "GHI CHÚ TRÍCH DẪN"],
        refRows,
        [6, 20, 28, 16, 8, 22]
    ));

    return {
        properties: {
            page: {
                margin: { top: 1134, bottom: 1134, left: 1701, right: 1134 },
                pageNumbers: {
                    formatType: NumberFormat.DECIMAL,
                    start: 1
                }
            }
        },
        headers: {
            default: new Header({
                children: [
                    new Paragraph({
                        alignment: AlignmentType.RIGHT,
                        children: [
                            new TextRun({
                                text: "Đồ án: Luyện thi Tin học Quốc tế IC3 Quest       |       GVHD: Thầy Huỳnh Luân",
                                font: FONT_FAMILY,
                                size: 19, // 9.5pt
                                italics: true,
                                color: "6B7280"
                            })
                        ]
                    })
                ]
            })
        },
        footers: {
            default: new Footer({
                children: [
                    new Paragraph({
                        alignment: AlignmentType.RIGHT,
                        children: [
                            new TextRun({
                                text: "SVTH: Lê Minh Trí – Âu Lê Thành Tài – Võ Trung Kiều Diễm  (Lớp CD25CT1)                Trang ",
                                font: FONT_FAMILY,
                                size: 19,
                                italics: true,
                                color: "6B7280"
                            }),
                            new TextRun({
                                children: [PageNumber.CURRENT],
                                font: FONT_FAMILY,
                                size: 19,
                                bold: true,
                                color: "1E3A8A"
                            })
                        ]
                    })
                ]
            })
        },
        children: children
    };
}

// --- TẠO TÀI LIỆU WORD HOÀN CHỈNH ---

async function generateReport() {
    const doc = new Document({
        styles: {
            default: {
                document: {
                    run: {
                        font: FONT_FAMILY,
                        size: 26,
                        color: "111827"
                    }
                }
            }
        },
        sections: [
            buildCoverSection(),        // Section 1: Trang bìa chính
            buildCoverSection(),        // Section 2: Trang bìa lót (trình bày tương tự)
            buildPreliminarySection(),  // Section 3: Trang sơ bộ (Số trang La Mã I, II, III...)
            buildMainBodySection()      // Section 4: Toàn bộ nội dung Chương 1 - 4 & Phụ lục (Số trang 1, 2, 3...)
        ]
    });

    console.log("Packing document to buffer...");
    const buffer = await Packer.toBuffer(doc);

    // Lưu vào các vị trí:
    // 1. E:\Desktop\trikun\CD_php\trí kun.docx (yêu cầu của người dùng)
    // 2. E:\Desktop\trikun\CD_php\trí kun_ban_moi.docx
    // 3. E:\Desktop\trikun\CD_php\trí kun_hoan_thien.docx (đảm bảo mở ngay lập tức)
    // 4. c:\laragon\www\MOS\tri_kun.docx (bản sao lưu an toàn)
    const outPath1 = "E:\\Desktop\\trikun\\CD_php\\trí kun.docx";
    const outPathNew = "E:\\Desktop\\trikun\\CD_php\\trí kun_ban_moi.docx";
    const outPathPerfect = "E:\\Desktop\\trikun\\CD_php\\trí kun_hoan_thien.docx";
    const outPath2 = "c:\\laragon\\www\\MOS\\tri_kun.docx";

    try {
        fs.writeFileSync(outPathPerfect, buffer);
        console.log(`[OK] Successfully written to: ${outPathPerfect} (${buffer.length} bytes)`);
    } catch (err) {
        console.error(`[WARN] Could not write to ${outPathPerfect}:`, err.message);
    }

    try {
        fs.writeFileSync(outPathNew, buffer);
        console.log(`[OK] Successfully written to: ${outPathNew} (${buffer.length} bytes)`);
    } catch (err) {
        console.error(`[WARN] Could not write to ${outPathNew}:`, err.message);
    }

    try {
        fs.writeFileSync(outPath1, buffer);
        console.log(`[OK] Successfully written to: ${outPath1} (${buffer.length} bytes)`);
    } catch (err) {
        console.error(`[WARN] Could not write to ${outPath1} (file is open in Word):`, err.message);
    }

    try {
        fs.writeFileSync(outPath2, buffer);
        console.log(`[OK] Successfully written backup to: ${outPath2} (${buffer.length} bytes)`);
    } catch (err) {
        console.error(`[WARN] Could not write to ${outPath2}:`, err.message);
    }

    console.log("REPORT GENERATION COMPLETE!");
}

generateReport().catch(err => {
    console.error("FATAL ERROR generating report:", err);
});
