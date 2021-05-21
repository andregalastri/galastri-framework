<?php

/**
 * This is the project configuration file. This file stores an array with project parameters that
 * configure some parameters in the project.
 *
 * Some of these parameters can be configured in the route configuration file too.
 */
return [
    /**
     * The url root path refers to the starting point of the URL routing. In short, it is the
     * portion of the URL that will be ignored.
     *
     * Normally it is based on where your index.php is located, inside the public folder. If the
     * index.php file is inside the root of the public folder, then your URLs will be like this:
     *
     *      mydomain.com/
     *
     * But if your index.php file is located inside subfolders, then this path above will fail. You
     * need to configure here the correct directory where your index.php is located. For exemple, if
     * it is located inside /mysubfolder, then:
     *
     *      'urlRoot' => '/mysubfolder'
     *
     * With that, every request that has mydomain.com/mysubfolder, will be controlled by the
     * framework. Every other request that doen't contain this address will be controlled by the
     * server directory system.
     *
     * string
     */
    'urlRoot' => '/',

    /**
     * Default timezone of the project. To use the server's timezone, set it to null or remove it
     * from this configuration file.
     *
     * null|string
     */
    'timezone' => 'America/Sao_Paulo',

    /**
     * The name of the project. This information can be retrieved by the route controllers and the
     * outputs. If you want to define different project titles for different routes, you can define
     * this parameter in the route configuration file.
     *
     * string
     */
    'projectTitle' => 'Galastri Framework',

    /**
     * Configures the default template file for the View output. Most of sites have only one
     * template, which can be configured only here. However, if your project used multiple templates
     * for View Files, then you can define this parameter in the route configuration file.
     *
     * string
     */
    'templateFile' => '/app/templates/main.php',

    /**
     * Defines if the application is offline. When true, the entire project won't be accessible and
     * an error message will be shown informing that the app is offline. If you want to define that
     * an specific route is offline, then you can define this parameter in the route configuration
     * file.
     *
     * bool
     */
    'offline' => false,

    /**
     * Defines a global URL to redirect the request when an error 404 occurs. is offline. When null,
     * an exception will be thrown showing the error 404 message.
     *
     * null|string
     */
    'notFoundRedirect' => null,

    /**
     * A default message to show when the 'offline' parameter is true. To set different messages to
     * different routes, you can define this parameter in the route configuration
     * file.
     */
    # 'offlineMessage' => "This area is currently offline. Please, try again later.",

    /**
     * A default message to show when an authentication fails. To set different messages to
     * different routes, you can define this parameter in the route configuration
     * file.
     */
    # 'authFailMessage' => "You aren't authorized to access this area.",


    /**
     * A default message to show when an permission fails. To set different messages to
     * different routes, you can define this parameter in the route configuration
     * file.
     */
    # 'permissionFailMessage' => "You don't have permission to execute this action.",
];
