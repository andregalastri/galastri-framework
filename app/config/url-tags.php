<?php

/**
 * This is the URL alias configuration file. This file stores an array with pre defined URLs that
 * works as an alias to an URL to be used by the Redirect class. Any redirection parameter also
 * checks this file before its execution.
 *
 * Example: Suppose that you have a project that needs, for some reason, redirect a request to the
 * URL '/my/url'. Instead of using:
 *
 *      Redirect::to('/my/url');
 *
 * You can set an alias here and instead of declaring the URL, you can declare the alias name in
 * this configuration file here, like this:
 *
 *      'myAlias' => '/my/url'
 *
 * And then, when calling the Redirect class, use it like this:
 *
 *      Redirect::to('myAlias');
 * 
 * This is good because if you need to change the URL, you don't need to change the alias name,
 * which means that every Redirect will still work, even if you change the URL of the alias.
 */
return [
    'index' => '/',
];
