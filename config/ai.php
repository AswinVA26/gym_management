<?php

return [

    /*
    |--------------------------------------------------------------------------
    | AI Provider
    |--------------------------------------------------------------------------
    |
    | The integration speaks the OpenAI Chat Completions protocol so any
    | free or paid provider implementing it will work.
    |
    |   ollama       -> fully local & free (no API key required)
    |   openai       -> paid OpenAI
    |   groq         -> free tier
    |   openrouter   -> includes many free models
    |   custom       -> any OpenAI-compatible endpoint
    |
    */

    'provider' => env('AI_PROVIDER', 'ollama'),

    /*
    |--------------------------------------------------------------------------
    | Connection Settings
    |--------------------------------------------------------------------------
    */

    'base_url' => env('AI_BASE_URL', 'http://localhost:11434/v1'),

    'api_key' => env('AI_API_KEY', ''),

    'model' => env('AI_MODEL', 'llama3.1'),

    'timeout' => (int) env('AI_TIMEOUT', 60),

    /*
    |--------------------------------------------------------------------------
    | Model Presets
    |--------------------------------------------------------------------------
    |
    | Convenience presets per provider. The active values above always win.
    |
    */

    'presets' => [
        'ollama' => [
            'base_url' => 'http://localhost:11434/v1',
            'model' => 'llama3.1',
            'api_key' => '',
        ],
        'openai' => [
            'base_url' => 'https://api.openai.com/v1',
            'model' => 'gpt-4o-mini',
        ],
        'groq' => [
            'base_url' => 'https://api.groq.com/openai/v1',
            'model' => 'llama-3.3-70b-versatile',
        ],
        'openrouter' => [
            'base_url' => 'https://openrouter.ai/api/v1',
            'model' => 'meta-llama/llama-3.3-70b-instruct:free',
        ],
    ],

];
