<?php
/**
 * This file configures pre defined URLs, tagging them with labels that
 * identifies them. Any redirection parameter, function or class in the
 * framework checks this file first when a redirection is called. If the
 * location informed matches a key in this array, then the location used will be
 * the key's value.
 */

return [
    /* These are required default tags, used by the framework. You can change
    their values, but not their key labels. */
    'index' => '/',
    '404'   => '/not-found',
];
