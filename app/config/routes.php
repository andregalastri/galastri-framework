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
    '/' => [
        'output' => 'view',

        '@main' => [
        ],
    ],
];
