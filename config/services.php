<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
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

    /*
    |--------------------------------------------------------------------------
    | Firebase Cloud Messaging (FCM)
    |--------------------------------------------------------------------------
    |
    | Push notification ke aplikasi Android. Gunakan FCM HTTP v1 API yang
    | otentikasinya memakai Service Account (file JSON dari Firebase Console:
    | Project Settings > Service accounts > Generate new private key).
    |
    | FCM_CREDENTIALS_FILE : path absolut ke file JSON service account.
    | FCM_PROJECT_ID       : opsional; bila kosong diambil dari file JSON.
    |
    */
    'fcm' => [
        'credentials' => env('FCM_CREDENTIALS_FILE', storage_path('app/firebase/firebase-credentials.json')),
        'project_id' => env('FCM_PROJECT_ID'),

        // Firebase Web SDK config (dari Firebase Console → Project Settings → General → Your apps → Web).
        // Values ini bersifat PUBLIC (bukan secret) — dipakai client-side untuk init Firebase Web SDK.
        'web' => [
            'api_key'             => env('FIREBASE_WEB_API_KEY'),
            'auth_domain'         => env('FIREBASE_WEB_AUTH_DOMAIN'),
            'project_id'          => env('FIREBASE_WEB_PROJECT_ID'),
            'storage_bucket'      => env('FIREBASE_WEB_STORAGE_BUCKET'),
            'messaging_sender_id' => env('FIREBASE_WEB_MESSAGING_SENDER_ID'),
            'app_id'              => env('FIREBASE_WEB_APP_ID'),
            // VAPID public key (dari Cloud Messaging → Web Push certificates → Generate key pair)
            'vapid_key'           => env('FIREBASE_WEB_VAPID_KEY'),
        ],
    ],

];
