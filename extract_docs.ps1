Add-Type -AssemblyName System.IO.Compression
Add-Type -AssemblyName System.IO.Compression.FileSystem

function Read-Docx($filePath, $outputTxt) {
    if (-not (Test-Path $filePath)) {
        Write-Host "File does not exist: $filePath"
        return
    }
    Write-Host "Reading $filePath..."
    try {
        $fileStream = [System.IO.File]::Open($filePath, [System.IO.FileMode]::Open, [System.IO.FileAccess]::Read, [System.IO.FileShare]::ReadWrite)
        $zip = New-Object System.IO.Compression.ZipArchive($fileStream, [System.IO.Compression.ZipArchiveMode]::Read)
        $entry = $zip.GetEntry('word/document.xml')
        if (-not $entry) {
            Write-Host "Cannot find word/document.xml in $filePath"
            $zip.Dispose()
            $fileStream.Dispose()
            return
        }
        $stream = $entry.Open()
        $reader = New-Object System.IO.StreamReader($stream)
        $xmlContent = $reader.ReadToEnd()
        $stream.Close()
        $zip.Dispose()
        $fileStream.Dispose()

        $xml = [xml]$xmlContent
        $ns = New-Object System.Xml.XmlNamespaceManager($xml.NameTable)
        $ns.AddNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main')

        $paragraphs = $xml.SelectNodes('//w:p', $ns)
        $lines = @()
        foreach ($p in $paragraphs) {
            $styleNode = $p.SelectSingleNode('w:pPr/w:pStyle/@w:val', $ns)
            $style = if ($styleNode) { $styleNode.Value } else { "Normal" }
            $text = $p.InnerText
            if ($text -and $text.Trim().Length -gt 0) {
                $lines += "[$style] $text"
            }
        }
        [System.IO.File]::WriteAllLines($outputTxt, $lines, [System.Text.Encoding]::UTF8)
        Write-Host "Done: $($lines.Count) lines written to $outputTxt"
    }
    catch {
        Write-Host "Error reading $filePath : $_"
    }
}

Read-Docx "E:\Desktop\trikun\CD_php\vip_php.docx" "c:\laragon\www\MOS\vip_php_dump.txt"
Read-Docx "E:\Desktop\trikun\CD_php\Mẫu hướng dẫn trình bày báo cáo (4).docx" "c:\laragon\www\MOS\mau_huong_dan_dump.txt"
Read-Docx "E:\Desktop\trikun\CD_php\BAO_CAO_DO_AN_IC3_QUEST.docx" "c:\laragon\www\MOS\bao_cao_ic3_dump.txt"
Read-Docx "E:\Desktop\trikun\CD_php\HOVATEN_MSSV.docx" "c:\laragon\www\MOS\hovaten_dump.txt"
