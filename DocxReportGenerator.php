<?php
/**
 * DOCX REPORT GENERATOR CHO HỆ THỐNG IC3 QUEST / MOS
 * Hỗ trợ tạo văn bản OpenXML Word chuẩn mực: Font, Căn lề, Header/Footer, Bảng biểu và Chèn Ảnh Chụp Màn Hình.
 */

class DocxReportGenerator {
    private array $p = [];

    private function escape(string $text): string {
        return htmlspecialchars($text, ENT_XML1 | ENT_COMPAT, 'UTF-8');
    }

    public function addRaw(string $xml): void {
        $this->p[] = $xml;
    }

    public function addPageBreak(): void {
        $this->p[] = '<w:p><w:r><w:br w:type="page"/></w:r></w:p>';
    }

    public function addParagraph(
        string $text,
        string $align = 'both',
        int $size = 26, // 13pt
        bool $bold = false,
        bool $italic = false,
        string $color = '000000',
        int $spaceBefore = 60,
        int $spaceAfter = 60,
        int $indentFirstLine = 720,
        int $lineSpacing = 360 // 1.5 lines
    ): void {
        $pPr = '<w:pPr>';
        $pPr .= '<w:spacing w:before="' . $spaceBefore . '" w:after="' . $spaceAfter . '" w:line="' . $lineSpacing . '" w:lineRule="auto"/>';
        if ($indentFirstLine > 0) {
            $pPr .= '<w:ind w:firstLine="' . $indentFirstLine . '"/>';
        }
        $pPr .= '<w:jc w:val="' . $align . '"/>';
        $pPr .= '</w:pPr>';

        $rPr = '<w:rPr>';
        $rPr .= '<w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs="Times New Roman"/>';
        $rPr .= '<w:sz w:val="' . $size . '"/>';
        $rPr .= '<w:szCs w:val="' . $size . '"/>';
        if ($bold) {
            $rPr .= '<w:b/><w:bCs/>';
        }
        if ($italic) {
            $rPr .= '<w:i/><w:iCs/>';
        }
        if ($color !== '000000') {
            $rPr .= '<w:color w:val="' . $color . '"/>';
        }
        $rPr .= '</w:rPr>';

        $this->p[] = '<w:p>' . $pPr . '<w:r>' . $rPr . '<w:t xml:space="preserve">' . $this->escape($text) . '</w:t></w:r></w:p>';
    }

    public function addHeading1(string $text): void {
        $this->p[] = '<w:p><w:pPr>'
            . '<w:pStyle w:val="Heading1"/>'
            . '<w:spacing w:before="240" w:after="120" w:line="360" w:lineRule="auto"/>'
            . '<w:jc w:val="left"/>'
            . '</w:pPr>'
            . '<w:r><w:rPr>'
            . '<w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs="Times New Roman"/>'
            . '<w:b/><w:bCs/><w:sz w:val="32"/><w:szCs w:val="32"/><w:color w:val="002060"/>'
            . '</w:rPr><w:t>' . $this->escape($text) . '</w:t></w:r></w:p>';
    }

    public function addHeading2(string $text): void {
        $this->p[] = '<w:p><w:pPr>'
            . '<w:pStyle w:val="Heading2"/>'
            . '<w:spacing w:before="180" w:after="90" w:line="360" w:lineRule="auto"/>'
            . '<w:jc w:val="left"/>'
            . '</w:pPr>'
            . '<w:r><w:rPr>'
            . '<w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs="Times New Roman"/>'
            . '<w:b/><w:bCs/><w:sz w:val="28"/><w:szCs w:val="28"/><w:color w:val="1F4E79"/>'
            . '</w:rPr><w:t>' . $this->escape($text) . '</w:t></w:r></w:p>';
    }

    public function addHeading3(string $text): void {
        $this->p[] = '<w:p><w:pPr>'
            . '<w:pStyle w:val="Heading3"/>'
            . '<w:spacing w:before="120" w:after="60" w:line="360" w:lineRule="auto"/>'
            . '<w:jc w:val="left"/>'
            . '</w:pPr>'
            . '<w:r><w:rPr>'
            . '<w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs="Times New Roman"/>'
            . '<w:b/><w:bCs/><w:sz w:val="26"/><w:szCs w:val="26"/><w:color w:val="2F5597"/>'
            . '</w:rPr><w:t>' . $this->escape($text) . '</w:t></w:r></w:p>';
    }

    public function addBullet(string $text, int $level = 1): void {
        $indent = $level * 360;
        $this->p[] = '<w:p><w:pPr>'
            . '<w:spacing w:before="40" w:after="40" w:line="360" w:lineRule="auto"/>'
            . '<w:ind w:left="' . $indent . '" w:hanging="240"/>'
            . '<w:jc w:val="both"/>'
            . '</w:pPr>'
            . '<w:r><w:rPr><w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman"/><w:sz w:val="26"/><w:szCs w:val="26"/><w:b/></w:rPr><w:t>•  </w:t></w:r>'
            . '<w:r><w:rPr><w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman"/><w:sz w:val="26"/><w:szCs w:val="26"/></w:rPr><w:t xml:space="preserve">' . $this->escape($text) . '</w:t></w:r>'
            . '</w:p>';
    }

    public function addTable(array $headers, array $rows, array $colWidths = []): void {
        $totalWidth = 9071; // 16cm
        $numCols = count($headers);
        if (empty($colWidths)) {
            $w = (int)($totalWidth / $numCols);
            $colWidths = array_fill(0, $numCols, $w);
        }

        $xml = '<w:tbl>';
        $xml .= '<w:tblPr>';
        $xml .= '<w:tblStyle w:val="TableGrid"/>';
        $xml .= '<w:tblW w:w="' . $totalWidth . '" w:type="dxa"/>';
        $xml .= '<w:tblBorders>';
        $xml .= '<w:top w:val="single" w:sz="6" w:space="0" w:color="002060"/>';
        $xml .= '<w:left w:val="single" w:sz="4" w:space="0" w:color="CCCCCC"/>';
        $xml .= '<w:bottom w:val="single" w:sz="6" w:space="0" w:color="002060"/>';
        $xml .= '<w:right w:val="single" w:sz="4" w:space="0" w:color="CCCCCC"/>';
        $xml .= '<w:insideH w:val="single" w:sz="4" w:space="0" w:color="E0E0E0"/>';
        $xml .= '<w:insideV w:val="single" w:sz="4" w:space="0" w:color="E0E0E0"/>';
        $xml .= '</w:tblBorders>';
        $xml .= '<w:tblCellMar><w:top w:w="100" w:type="dxa"/><w:left w:w="120" w:type="dxa"/><w:bottom w:w="100" w:type="dxa"/><w:right w:w="120" w:type="dxa"/></w:tblCellMar>';
        $xml .= '</w:tblPr>';

        $xml .= '<w:tblGrid>';
        foreach ($colWidths as $w) {
            $xml .= '<w:gridCol w:w="' . $w . '"/>';
        }
        $xml .= '</w:tblGrid>';

        // Header Row
        $xml .= '<w:tr><w:trPr><w:tblHeader/></w:trPr>';
        foreach ($headers as $i => $h) {
            $w = $colWidths[$i] ?? 1000;
            $xml .= '<w:tc><w:tcPr><w:tcW w:w="' . $w . '" w:type="dxa"/><w:shd w:val="clear" w:color="auto" w:fill="D9E1F2"/></w:tcPr>';
            $xml .= '<w:p><w:pPr><w:spacing w:before="60" w:after="60"/><w:jc w:val="center"/></w:pPr>';
            $xml .= '<w:r><w:rPr><w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman"/><w:b/><w:bCs/><w:sz w:val="22"/><w:szCs w:val="22"/><w:color w:val="002060"/></w:rPr><w:t>' . $this->escape($h) . '</w:t></w:r>';
            $xml .= '</w:p></w:tc>';
        }
        $xml .= '</w:tr>';

        // Data Rows
        foreach ($rows as $rIdx => $row) {
            $bg = ($rIdx % 2 === 1) ? 'F9FAFB' : 'FFFFFF';
            $xml .= '<w:tr>';
            foreach ($row as $i => $val) {
                $w = $colWidths[$i] ?? 1000;
                $align = ($i === 0 && count($row) > 3) ? 'center' : 'left';
                $xml .= '<w:tc><w:tcPr><w:tcW w:w="' . $w . '" w:type="dxa"/>' . ($bg !== 'FFFFFF' ? '<w:shd w:val="clear" w:color="auto" w:fill="' . $bg . '"/>' : '') . '</w:tcPr>';
                $xml .= '<w:p><w:pPr><w:spacing w:before="40" w:after="40"/><w:jc w:val="' . $align . '"/></w:pPr>';
                $xml .= '<w:r><w:rPr><w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman"/><w:sz w:val="22"/><w:szCs w:val="22"/></w:rPr><w:t xml:space="preserve">' . $this->escape((string)$val) . '</w:t></w:r>';
                $xml .= '</w:p></w:tc>';
            }
            $xml .= '</w:tr>';
        }

        $xml .= '</w:tbl>';
        $this->p[] = $xml;
        $this->p[] = '<w:p><w:pPr><w:spacing w:before="40" w:after="40"/></w:pPr></w:p>';
    }

    /**
     * Chèn hình ảnh kèm chú thích căn giữa
     * $cx, $cy tính bằng EMUs (1 inch = 914400 EMUs, 15cm = 5400000 EMUs)
     */
    public function addImage(string $relId, string $caption, int $cx = 5400000, int $cy = 3035432): void {
        $docPrId = rand(100, 9999);
        $xml = '<w:p><w:pPr><w:jc w:val="center"/><w:spacing w:before="140" w:after="60"/></w:pPr>'
            . '<w:r><w:drawing>'
            . '<wp:inline distT="0" distB="0" distL="0" distR="0">'
            . '<wp:extent cx="' . $cx . '" cy="' . $cy . '"/>'
            . '<wp:effectExtent l="0" t="0" r="0" b="0"/>'
            . '<wp:docPr id="' . $docPrId . '" name="' . $this->escape($caption) . '"/>'
            . '<wp:cNvGraphicFramePr><a:graphicFrameLocks xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" noChangeAspect="1"/></wp:cNvGraphicFramePr>'
            . '<a:graphic xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main">'
            . '<a:graphicData uri="http://schemas.openxmlformats.org/drawingml/2006/picture">'
            . '<pic:pic xmlns:pic="http://schemas.openxmlformats.org/drawingml/2006/picture">'
            . '<pic:nvPicPr>'
            . '<pic:cNvPr id="' . $docPrId . '" name="' . $this->escape($caption) . '"/>'
            . '<pic:cNvPicPr/>'
            . '</pic:nvPicPr>'
            . '<pic:blipFill>'
            . '<a:blip r:embed="' . $relId . '"/>'
            . '<a:stretch><a:fillRect/></a:stretch>'
            . '</pic:blipFill>'
            . '<pic:spPr>'
            . '<a:xfrm><a:off x="0" y="0"/><a:ext cx="' . $cx . '" cy="' . $cy . '"/></a:xfrm>'
            . '<a:prstGeom prst="rect"><a:avLst/></a:prstGeom>'
            . '</pic:spPr>'
            . '</pic:pic>'
            . '</a:graphicData>'
            . '</a:graphic>'
            . '</wp:inline>'
            . '</w:drawing></w:r></w:p>';

        $xml .= '<w:p><w:pPr><w:jc w:val="center"/><w:spacing w:before="40" w:after="160"/></w:pPr>'
            . '<w:r><w:rPr>'
            . '<w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman"/>'
            . '<w:sz w:val="22"/><w:szCs w:val="22"/><w:i/><w:b/><w:color w:val="1F4E79"/>'
            . '</w:rPr><w:t>' . $this->escape($caption) . '</w:t></w:r></w:p>';

        $this->p[] = $xml;
    }

    public function getDocumentXml(): string {
        $xml = '<?xml version="1.0" encoding="utf-8" standalone="yes"?>';
        $xml .= '<w:document xmlns:wpc="http://schemas.microsoft.com/office/word/2010/wordprocessingCanvas" xmlns:cx="http://schemas.microsoft.com/office/drawing/2014/chartex" xmlns:mc="http://schemas.openxmlformats.org/markup-compatibility/2006" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" xmlns:m="http://schemas.openxmlformats.org/officeDocument/2006/math" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:wp14="http://schemas.microsoft.com/office/word/2010/wordprocessingDrawing" xmlns:wp="http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing" xmlns:w10="urn:schemas-microsoft-com:office:word" xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main" xmlns:w14="http://schemas.microsoft.com/office/word/2010/wordml" xmlns:w15="http://schemas.microsoft.com/office/word/2012/wordml" xmlns:w16="http://schemas.microsoft.com/office/word/2018/wordml" xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" xmlns:pic="http://schemas.openxmlformats.org/drawingml/2006/picture">';
        $xml .= '<w:body>';
        foreach ($this->p as $item) {
            $xml .= $item;
        }
        // Section properties: Margins Top 2cm, Bottom 2cm, Left 3cm, Right 2cm
        $xml .= '<w:sectPr>';
        $xml .= '<w:pgSz w:w="11906" w:h="16838" w:orient="portrait"/>';
        $xml .= '<w:pgMar w:top="1134" w:right="1134" w:bottom="1134" w:left="1701" w:header="720" w:footer="720" w:gutter="0"/>';
        $xml .= '<w:cols w:space="720"/>';
        $xml .= '<w:titlePg w:val="1"/>';
        $xml .= '<w:headerReference w:type="default" r:id="R29aa59276aee42a6"/>';
        $xml .= '<w:headerReference w:type="first" r:id="R1e09901cb4bb40cf"/>';
        $xml .= '<w:footerReference w:type="default" r:id="R39a86449b6ec469f"/>';
        $xml .= '<w:footerReference w:type="first" r:id="Rf7adf82dac034015"/>';
        $xml .= '</w:sectPr>';
        $xml .= '</w:body></w:document>';
        return $xml;
    }
}
