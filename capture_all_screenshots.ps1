$chrome = 'C:\Program Files\Google\Chrome\Application\chrome.exe'
$outDir = 'c:\laragon\www\MOS\storage\app\report_screenshots'
$baseUrl = 'http://localhost/MOS/public'

$shots = @(
    @{ Name = 'hinh3_01_dang_nhap.png'; Url = "$baseUrl/dang-nhap" },
    @{ Name = 'hinh3_02_dashboard_admin.png'; Url = "$baseUrl/dev-login/1?to=quan-tri" },
    @{ Name = 'hinh3_03_quan_ly_nguoi_dung.png'; Url = "$baseUrl/dev-login/1?to=quan-tri/quan-ly" },
    @{ Name = 'hinh3_04_ic3_question_studio.png'; Url = "$baseUrl/dev-login/1?to=quan-tri/bo-de-cau-hoi" },
    @{ Name = 'hinh3_05_cai_dat_tro_choi.png'; Url = "$baseUrl/dev-login/1?to=quan-tri/tro-choi/cai-dat" },
    @{ Name = 'hinh3_06_goi_dich_vu_admin.png'; Url = "$baseUrl/dev-login/1?to=quan-tri/goi-dich-vu" },
    @{ Name = 'hinh3_07_cong_hoc_tap.png'; Url = "$baseUrl/dev-login/5?to=/" },
    @{ Name = 'hinh3_08_danh_muc_khoa_hoc.png'; Url = "$baseUrl/dev-login/5?to=hoc-tap" },
    @{ Name = 'hinh3_09_ban_do_chu_de.png'; Url = "$baseUrl/dev-login/5?to=chuong-trinh/khoi-3-spark-level-1" },
    @{ Name = 'hinh3_10_chuan_bi_lam_bai.png'; Url = "$baseUrl/dev-login/5?to=bai-luyen/k3-cd1-bai-1/lam-bai" },
    @{ Name = 'hinh3_11_bang_thanh_tich.png'; Url = "$baseUrl/dev-login/5?to=thanh-tich" },
    @{ Name = 'hinh3_12_khu_tro_choi_doi_sao.png'; Url = "$baseUrl/dev-login/5?to=tro-choi" },
    @{ Name = 'hinh3_13_goc_phu_huynh.png'; Url = "$baseUrl/dev-login/5?to=phu-huynh" },
    @{ Name = 'hinh3_14_bang_gia_dich_vu.png'; Url = "$baseUrl/bang-gia" },
    @{ Name = 'hinh3_15_thanh_toan_vietqr.png'; Url = "$baseUrl/bang-gia/thanh-toan/MOS-202609-OQBPB" }
)

$i = 0
foreach ($s in $shots) {
    $i++
    $targetPath = Join-Path $outDir $s.Name
    if (Test-Path $targetPath) {
        $existing = (Get-Item $targetPath).Length
        if ($existing -gt 50000) {
            Write-Host "[$i/$($shots.Count)] Skipping $($s.Name) (Already exists: $existing bytes)"
            continue
        }
    }
    Write-Host "[$i/$($shots.Count)] Capturing $($s.Name)..."
    $tempProfile = Join-Path $env:TEMP "chrome_snap_$i"
    if (Test-Path $tempProfile) { Remove-Item -Recurse -Force $tempProfile -ErrorAction SilentlyContinue }

    & $chrome --headless=new --user-data-dir="$tempProfile" --window-size=1600,1000 --hide-scrollbars --screenshot="$targetPath" "$($s.Url)"
    Start-Sleep -Milliseconds 800

    if (Test-Path $targetPath) {
        $size = (Get-Item $targetPath).Length
        Write-Host "  -> Success: $($size) bytes"
    } else {
        Write-Host "  -> Failed!"
    }
    Remove-Item -Recurse -Force $tempProfile -ErrorAction SilentlyContinue
}
