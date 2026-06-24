<?php 
return [
    'enabled'    => env('CHATBOT_ENABLED', true),
    'mode'       => env('CHATBOT_MODE', 'rule'), // rule | llm
    'llm'        => [
        'provider' => env('LLM_PROVIDER', 'openai'),
        'api_key'  => env('LLM_API_KEY'),
        'model'    => env('LLM_MODEL', 'gpt-4o-mini'),
        'timeout'  => 10,
    ],
    'routing'    => [
        'prefix' => 'chatbot',
    ],
];
?>