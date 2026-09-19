<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// 1. Test public support check
$res1 = $kernel->handle(Illuminate\Http\Request::create('/ho-tro/tin-nhan/kiem-tra?id=1', 'GET'));
echo "Public Support Check (ID: 1):\n" . $res1->getContent() . "\n\n";

// 2. Test admin poll (with admin user)
$user = \App\Models\User::where('role', 'admin')->first();
if ($user) {
    auth()->login($user);
    $res2 = $kernel->handle(Illuminate\Http\Request::create('/quan-tri/tin-nhan/realtime-poll?last_id=0&active_id=1', 'GET'));
    echo "Admin Realtime Poll:\n" . $res2->getContent() . "\n";
}
