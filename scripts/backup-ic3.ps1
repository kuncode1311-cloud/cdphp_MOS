param(
    [string]$BaseUrl = 'https://ic3review.iigvietnam.edu.vn/',
    [string]$OutputRoot = "$PSScriptRoot\..\public\legacy\ic3"
)

$ErrorActionPreference = 'Stop'
$levels = @(
    @{ Page = 'k3gs6.html'; Grade = 3 },
    @{ Page = 'k4gs6.html'; Grade = 4 },
    @{ Page = 'k5gs6.html'; Grade = 5 }
)
$assets = @('index.html', 'data/player.js', 'data/browsersupport.js', 'data/favicon.ico', 'data/apple-touch-icon.png')
$manifest = @()

New-Item -ItemType Directory -Force -Path $OutputRoot | Out-Null

foreach ($level in $levels) {
    $pageUrl = [Uri]::new([Uri]$BaseUrl, $level.Page).AbsoluteUri
    $html = (Invoke-WebRequest -Uri $pageUrl -UseBasicParsing -TimeoutSec 60).Content
    $links = [regex]::Matches($html, 'href\s*=\s*["'']([^"'']*newdata/[^"'']+/index\.html)["'']', 'IgnoreCase') |
        ForEach-Object { $_.Groups[1].Value } | Sort-Object -Unique

    foreach ($link in $links) {
        $courseUrl = [Uri]::new([Uri]$BaseUrl, $link).AbsoluteUri
        $courseUri = [Uri]$courseUrl
        $segments = $courseUri.AbsolutePath.Trim('/').Split('/')
        $originalName = [Uri]::UnescapeDataString($segments[-2])
        $slug = ($originalName -replace '\s*\(Published\)\s*', '' -replace '[^A-Za-z0-9-]+', '-').Trim('-').ToLowerInvariant()
        $relativeRoot = "grade-$($level.Grade)/$slug"
        $localRoot = Join-Path $OutputRoot $relativeRoot
        New-Item -ItemType Directory -Force -Path (Join-Path $localRoot 'data') | Out-Null

        foreach ($asset in $assets) {
            $assetUrl = [Uri]::new($courseUri, $asset).AbsoluteUri
            $target = Join-Path $localRoot ($asset -replace '/', '\')
            if (Test-Path -LiteralPath $target) { continue }
            try {
                Invoke-WebRequest -Uri $assetUrl -UseBasicParsing -TimeoutSec 120 -OutFile $target
            } catch {
                if ($asset -in @('index.html', 'data/player.js', 'data/browsersupport.js')) { throw }
            }
        }

        $indexPath = Join-Path $localRoot 'index.html'
        $index = Get-Content -Raw -LiteralPath $indexPath
        $index = $index -replace '<script type="module" src="https://static\.cloudflareinsights\.com/beacon[^<]+</script>', ''
        Set-Content -LiteralPath $indexPath -Value $index -Encoding UTF8

        $manifest += [ordered]@{
            grade = $level.Grade
            name = $originalName
            slug = $slug
            source_url = $courseUrl
            local_path = "/legacy/ic3/$relativeRoot/index.html"
        }
        Write-Output "Saved grade $($level.Grade): $originalName"
    }
}

$manifest | ConvertTo-Json -Depth 4 | Set-Content -LiteralPath (Join-Path $OutputRoot 'manifest.json') -Encoding UTF8
Write-Output "Completed: $($manifest.Count) courses"
