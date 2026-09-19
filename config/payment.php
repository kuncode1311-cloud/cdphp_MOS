<?php
/**
 * Cau hinh thanh toan & QR chuyen khoan
 * Cap nhat cac gia tri trong .env
 */
return [
    // Thong tin ngan hang de tao ma QR VietQR
    'bank_id'       => env('BANK_ID', 'MB'),
    'bank_name'     => env('BANK_NAME', 'MB Bank (Quân Đội)'),
    'bank_account'  => env('BANK_ACCOUNT', '0345151438'),
    'account_name'  => env('BANK_ACCOUNT_NAME', 'LE MINH TRI'),

    // PayOS
    'payos_client_id'    => env('PAYOS_CLIENT_ID'),
    'payos_api_key'      => env('PAYOS_API_KEY'),
    'payos_checksum_key' => env('PAYOS_CHECKSUM_KEY'),
    'payos_endpoint'     => env('PAYOS_ENDPOINT', 'https://api-merchant.payos.vn'),
];
