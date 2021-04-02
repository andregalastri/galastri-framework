<?php
return [
    '/' => [
        '@main' => [
            'aaa' => true,
        ],
        '/?page1' => [
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