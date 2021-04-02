<?php
/**
 * Default project settings.
 */

return [
    /**
     * The url root path refers to the starting point of the URL routing. In
     * short, it is the portion of the URL that will be ignored.
     *
     * Normally it is based on where your index.php is located, inside the
     * public folder. The root of the URLs for routing is based on where your
     * index.php is located, importing the \galastri\bootstrap.php file. If it
     * is inside the root of the public folder, then your URLs will be like
     * this:
     *
     *      mydomain.com/framework/controlled/paths
     *
     * But if your index.php file is located inside subfolders, then this path
     * will fail. You need to configure here the correct directory where your
     * index.php is located. For exemple, if it is located inside /mysubfolder,
     * then:
     *
     *      'urlRoot' => '/mysubfolder'
     *
     * With that, every request that has mydomain.com/mysubfolder, will be
     * controlled by the framework. Every other request that doen't contain this
     * address will be controlled by the server.
     *
     * @key urlRoot string
     */
    'urlRoot' => '/',

    /**
     * The name of the project.
     *
     * @key projectTitle string
     */
    'projectTitle' => 'Galastri Framework',

    /**
     * Works only with View solvers. Defines the template files where the view
     * will be printed. It is an associative array, which the key is the tag
     * label and its value is the path of the template file.
     *
     * Example:
     *
     *      'viewTemplate' => [
     *          // This key is required when using a view template.
     *          'main' => '/template/main.php'
     *
     *          // These are optional keys and can have any label.
     *          'sidemenu' => '/template/sidemenu.php'
     *          'myfooter' => '/template/sidemenu.php'
     *      ],
     *
     * The 'main' key is the only one that is required and the one that will be
     * called and it merges the other template parts. The others can be called
     * inside the main.
     *
     * You can define here multiple template parts and define the 'main' only in
     * the route configuration, in \app\config\routes.php file.
     *
     * @key title array
     */
    'viewTemplate' => [],

    /**
     * Default messages for a differents cases.
     *
     * @key timezone string
     */
    'messages' => [
        'offline' => "This area is currently offline. Please, try again later.",
        'authFail' => "You aren't authorized to access this area.",
        'permissionFail' => "You don't have permission to execute this action.",
    ],

    /**
     * (Optional) Default timezone of the project. To use the server's timezone,
     * set it to false. It can be defined in the controller too, in case of
     * dynamic timezones.
     *
     * @key timezone string|boolean
     */
    'timezone' => 'America/Sao_Paulo',
];
