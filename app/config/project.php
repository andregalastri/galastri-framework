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
     * Works only with View output. Defines the template file where the view
     * will be printed.
     *
     * NOTE: It isn't the view itself, the view will be other file. This
     * parameter here refers just to the template file, the skeleton which the
     * view and other stuff will be part.
     *
     * If your project have multiple templates, you can ignore this as empty and
     * configure the template path in the route configuration, in
     * \app\config\routes.php file.
     *
     * @key title array
     */
    'viewTemplateFile' => '/app/templates/main.php',

    'offline' => false,

    'notFoundRedirect' => null,

    /**
     * Default defaultmessage for a differents cases.
     *
     * @key timezone string
     */
    # 'offlineMessage' => "This area is currently offline. Please, try again later.",
    # 'authFailMessage' => "You aren't authorized to access this area.",
    # 'permissionFailMessage' => "You don't have permission to execute this action.",

    /**
     * (Optional) Default timezone of the project. To use the server's timezone,
     * set it to false. It can be defined in the controller too, in case of
     * dynamic timezones.
     *
     * @key timezone string|boolean
     */
    'timezone' => 'America/Sao_Paulo',
];
