<?php

return [

    // API riêng được ưu tiên; Gemini là dịch vụ dự phòng khi API riêng gặp lỗi.
    'question_ai' => [
        'base_url' => env('QUESTION_AI_BASE_URL', ''),
        'api_key' => env('QUESTION_AI_API_KEY', ''),
        'model' => env('QUESTION_AI_MODEL', ''),
        'image_model' => env('QUESTION_AI_IMAGE_MODEL', 'ag/gemini-3.1-flash-image'),
        'timeout' => (int) env('QUESTION_AI_TIMEOUT', 60),
        'connect_timeout' => (int) env('QUESTION_AI_CONNECT_TIMEOUT', 10),
    ],

    'gemini' => [
        'api_keys' => env('GEMINI_API_KEYS', env('GEMINI_API_KEY', '')),
        'image_model' => env('GEMINI_IMAGE_MODEL', 'gemini-3.1-flash-image'),
    ],

    'brevo' => [
        'key' => env('BREVO_API_KEY'),
        'sender_email' => env('BREVO_SENDER_EMAIL', env('MAIL_FROM_ADDRESS', 'kun.code.1311@gmail.com')),
        'sender_name' => env('MAIL_FROM_NAME', 'IC3 Adventure'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'payos' => [
        // Khóa PayOS chỉ lấy từ biến môi trường (.env / Railway), tuyệt đối không viết cứng trong mã nguồn
        'client_id' => env('PAYOS_CLIENT_ID', ''),
        'api_key' => env('PAYOS_API_KEY', ''),
        'checksum_key' => env('PAYOS_CHECKSUM_KEY', ''),
        'endpoint' => env('PAYOS_ENDPOINT', 'https://api-merchant.payos.vn'),
    ],

    'telegram' => [
        'bot_token' => env('TELEGRAM_BOT_TOKEN', '8567786883:AAENmm-bG97sn7uBZnxw7sVZooGRI4NbuEk'),
        'admin_chat_id' => env('TELEGRAM_ADMIN_CHAT_ID', '8952266086'),
        'webhook_secret' => env('TELEGRAM_WEBHOOK_SECRET', ''),
    ],

    // Tổng đài gọi điện Stringee (gọi khách từ trang Live Chat, có ghi âm cuộc gọi)
    'stringee' => [
        'key_sid' => env('STRINGEE_KEY_SID', ''),
        'key_secret' => env('STRINGEE_KEY_SECRET', ''),
        'from_number' => env('STRINGEE_FROM_NUMBER', ''),
        'api_base' => env('STRINGEE_API_BASE', 'https://api.stringee.com/v1'),
        'record' => (bool) env('STRINGEE_RECORD', true),
        'webhook_base' => env('STRINGEE_WEBHOOK_BASE') ?: env('APP_URL'),
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI', env('APP_URL', 'https://mos.app') . '/auth/google/callback'),
    ],

];
