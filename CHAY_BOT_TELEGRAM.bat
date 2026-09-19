@echo off
title IC3 Quest - Telegram Bot Daemon (@trikun_cdphp_bot)
color 0B
echo ======================================================================
echo    🤖 DANG KHOI CHAY TELEGRAM BOT DAEMON CHO HE THONG IC3 QUEST
echo    Bot: @trikun_cdphp_bot
echo ======================================================================
echo.
echo [!] Luu y: Vui long giu nguyen cua so nay de bot luon lang nghe va
echo     phan hoi tin nhan / lenh / nut bam tu Telegram.
echo.
cd /d %~dp0

where php >nul 2>&1
if %ERRORLEVEL% EQU 0 (
    php artisan telegram:poll
) else (
    "C:\laragon\bin\php\php-8.3.28-Win32-vs16-x64\php.exe" artisan telegram:poll
)

pause
