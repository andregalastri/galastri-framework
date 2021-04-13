<?php
return [
    '/' => [
        'output' => 'view',


        '@main' => [
            // 'viewFilePath' => '/app/test/anhothner/asdasd/asdasd/asdasd/asd/a.php',

            // 'requestMethod' => [
            //     'GET' => '@ayPutMethod',
            //     'POST' => '@myPostMethod',
            // ],
        ],

        '@not-found' => [],

        '/page1' => [
            'output' => 'view',
            'notFoundRedirect' => 'index',

            '@main' => [
                'requestMethod' => [
                    'GET' => '@myPutMethod',
                    'POST' => '@myPostMethod',
                ],
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
        '/page2' => [],
    ],
];
