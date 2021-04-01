<?php
return [
    '/' => [
        '@main' => [
            'aaa' => true,
        ],
        '/page1' => [
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