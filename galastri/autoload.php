<?php
/**
 * Defines the function that will seek for the PHP class file when a class is
 * called.
 *
 * The namespace is based on the directory structure of the framework.
 *
 * /galastri : contains the core files, modules, framework extensions (not the
 * project extensions) and global functions.
 *
 * /app      : contains the application files. The developer will its job there,
 * creating controllers, views, configurating and so.
 *
 * Every class, trait, interface need to have the namespace of the path of its
 * container folder declared.
 *
 * Example: A controller inside app/controller/MyRoute need have a namespace
 * declared like this:
 * 
 * namespace \app\controller\MyRoute;
 */
spl_autoload_register(function($className){
    $classFile = GALASTRI_PROJECT_DIR.'/'.str_replace('\\', '/', $className).'.php';

    if(file_exists($classFile))
        require_once($classFile);
});
