<?php

return [
    'firebase' => [
        'credentials_file' => env('FIREBASE_CREDENTIALS', base_path('firebase_credentials.json')),
        'database_url' => env('FIREBASE_DATABASE_URL', ''),
    ],
];
