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

    'ai' => [
        'content' => [
            'api_key' => env('AI_CONTENT_API_KEY', env('OPENAI_CONTENT_API_KEY', '')),
            'base_url' => env('AI_CONTENT_BASE_URL', 'https://api.openai.com/v1'),
            'wire_api' => env('AI_CONTENT_WIRE_API', 'chat_completions'),
            'model' => env('AI_CONTENT_MODEL', 'gpt-4o-mini'),
            'max_tokens' => (int) env('AI_CONTENT_MAX_TOKENS', 4000),
        ],
        'image' => [
            'api_key' => env('AI_IMAGE_API_KEY', env('OPENAI_API_KEY', '')),
            'base_url' => env('AI_IMAGE_BASE_URL', 'https://api.openai.com/v1'),
            'model' => env('AI_IMAGE_MODEL', 'gpt-image-1'),
            'quality' => env('AI_IMAGE_QUALITY', 'medium'),
        ],
    ],

];
