<?php
return [
    /**
     * The bootstrap root path, inside the public folder. The root of the URLs
     * for routing is based on where your bootstrap is located. If it is inside
     * the root of the public folder, then your URLs will be like this:
     *
     * Configuration: 'bootstrapPath' => '/'
     * URL: mydomain.com/paths/controlled/by/framework
     *
     * But if your bootstrap file is located inside subfolders, then this path
     * will fail. You need to inform the correct path where the bootstrap is
     * located. For exemple, if it is inside /mysubfolder, then:
     *
     * Configuration: 'bootstrapPath' => '/mysubfolder'
     * URL: mydomain.com/mysubfolder/paths/controlled/by/framework
     *
     * @key bootstrapPath string
     */
    'bootstrapPath' => '/',

    /**
     * Configures the title that will be shown inside tags <title></title>, when
     * displaying a View HTML.
     *
     * @key title array
     */
    'title' => [
        
        /**
         * The name of the project and the default page title.
         *
         * @key appTitle string
         */
        'appTitle' => 'Galastri Framework',

        /**
         * The way how the title will be shown. There are 2 keywords that can be
         * used:
         *
         * - <pageTitle> : Will be replaced for the title of the page, defined in
         *                 the route configuration or in the controller.
         * - <appTitle>  : Will be replaced for the string configured above.
         *
         * @key titleTemplate string
         */
        'titleTemplate' => '<pageTitle> | <appTitle>',
    ],

    /**
     * (Optional) Default timezone of the project. To use the server's timezone,
     * set it to false.
     *
     * @key timezone string|boolean
     */
    'timezone' => 'America/Sao_Paulo',

    'defaultMessages' => [
        'offline' => "This area is offline. Please, try again later.",
        'authFail' => "You aren't authorized to access this area.",
        'permissionFail' => "You don't have permission to execute this action.",
    ]
];
