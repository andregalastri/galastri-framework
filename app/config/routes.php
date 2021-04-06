<?php
return [
    '/' => [
        'solver' => 'view',
        '@main' => [
            // 'requestType' => 'GET|POST'
        ],
        '@not-found' => [
        ],
        '/page1' => [
            'solver' => 'json',
            'notFoundRedirect' => 'index',

            '@main' => [
                'aaa' => true,
            ],

            // 'snippetExecAfter' => ['\app\snippets\MySnippet', '\app\snippets\OtherSnippet'],
            
            '/page1-2' => [
                '@main' => [
                    'aaa' => true,
                ],
                '@test' => [
                    'aaa' => true,
                ]
            ],
        ],
        '/page2' => [

        ],
    ],
];