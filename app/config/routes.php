<?php
return [
    '/' => [
        'solver' => 'view',

        'viewBaseTemplate' => '/app/templates/main.php',

        '@main' => [
            'requestMethod' => [
                'GET' => '@myPutMethod',
                'POST' => '@myPostMethod',
            ],
        ],
       
        '@not-found' => [
        ],

        '/?page1' => [
            'solver' => 'json',
            'notFoundRedirect' => 'index',

            '@main' => [
                
                'aaa' => true,
            ],

            // 'snippetExecAfter' => ['\app\snippets\MySnippet', '\app\snippets\OtherSnippet'],
            
            '/page2' => [

                '@main' => [
                    'aaa' => true,
                ],
                '@test' => [
                    'aaa' => true,
                ],
                '/page3' => [
    
                    '@main' => [
                        'aaa' => true,
                    ],
                    '@test' => [
                        'aaa' => true,
                    ],

                    '/page4' => [
    
                        '@main' => [
                            'aaa' => true,
                        ],
                        '@test' => [
                            'aaa' => true,
                        ]
                    ],
                ],
            ],
        ],
        '/page2' => [

        ],
    ],
];