<?php

/**
 * This is the route configuration file. This is the most important configuration file of the
 * framework because it is here that the routing will be configured.
 *
 * -------------------------------------------------------------------------------------------------
 *               NOTE: To understand how to configure this file, access this page:
 *                                   -- NO DOCUMENTATION YET --
 * -------------------------------------------------------------------------------------------------
 *
 * - Parent nodes always starts with slash bar char /
 * - Child nodes always starts with at char @
 * - Parameters doesn't start with special chars
 *
 * The first key of the array below needs to be the '/' which means the index of the project and it
 * is the main parent node of the project. Do not change this nor its name. Every parent node needs
 * to have at least one child node called 'main'.
 *
 * A parent node will mostly refers to controller files and child nodes will mostly refers to a
 * method inside the controller. This is true for most of the routes, excepts if the route points to
 * a File output, which doesn't need a controller to work.
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
     *      'urlRoot' => '/mysubfolder/'
     *
     * With that, every request that has mydomain.com/mysubfolder, will be controlled by the
     * framework. Every other request that doen't contain this address will be controlled by the
     * server directory system.
     *
     * string
     */
    '/' => [

        /**
        * Configures the default template file for the View output. Most of sites have only one
        * template, which can be configured only here. However, if your project used multiple
        * templates for View Files, then you can define this parameter in the route configuration
        * file.
        *
        * string
        */
        'templateFile' => '/app/templates/main.php',

        '@main' => [
            'output' => 'view',
        ],

        '@images' => [
            'output' => 'file',
            'baseFolder' => '/app/images',
        ],
    ],
];
