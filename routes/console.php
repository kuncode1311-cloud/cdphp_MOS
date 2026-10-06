<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Lịch sử chat thành viên chỉ giữ 12 tháng (khớp với thông báo trong khung chat)
Schedule::command('support:prune-chats')->daily();
