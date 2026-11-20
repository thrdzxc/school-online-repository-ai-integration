<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Existing Third Party Services
    |--------------------------------------------------------------------------
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
    | School Repository AI Assessment
    |--------------------------------------------------------------------------
    */

    'ai' => [
        'provider' => env('AI_PROVIDER', 'groq'),
        'auto_evaluate' => filter_var(
            env('AI_AUTO_EVALUATE', false),
            FILTER_VALIDATE_BOOL
        ),
        'max_chars' => (int) env('AI_MAX_CHARS', 80000),
    ],

    /*
    |--------------------------------------------------------------------------
    | Groq API
    |--------------------------------------------------------------------------
    */

    'groq' => [
        'key' => env('GROQ_API_KEY'),
        'model' => env('GROQ_MODEL', 'openai/gpt-oss-20b'),
        'base_url' => env(
            'GROQ_BASE_URL',
            'https://api.groq.com/openai/v1'
        ),
        'timeout' => (int) env('GROQ_TIMEOUT', 120),
        'max_completion_tokens' => (int) env('GROQ_MAX_COMPLETION_TOKENS', 8192),
    ],

];
