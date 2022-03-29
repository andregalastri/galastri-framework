<?php

/**
 * This is the bootstrap file of the framework. It imports the configuration files and initializes
 * some of the parameters. Every request need to pass over here because it will redirect the request
 * to the files of the configured route.
 */

 /**
  * The core of the framework uses a namespace called 'galastri', following the directory structure
  * to import its modules, extensions, etc. The app that the users will create are placed in the
  * 'app' folder and will use the 'app' namespace.
  */
namespace galastri;

/**
 * Importing classes used in this file.
 */
use galastri\core\Parameters;
use galastri\extensions\Exception;
use galastri\modules\PerformanceAnalysis;

/**
 * This is the first try/catch block. When an exception is thrown, some data is returned in JSON
 * format informing about the error. The data returned need to be the same used in the Debug class
 * and some can ask why not use this class here. The problem is that it is too early to use this
 * class.
 *
 * Everything is still being set up and the Debug class uses some of the configurations that are
 * being set here. If there is a misconfigutarion in some of the parameters used by the Debug class,
 * it would be impossible to thrown an exception to the Debug class to return. That is why the
 * bootstrap file uses its own way to return exception data.
 */
try {
    /**
     * Importing files:
     *
     * - The const.php file has the base constants used by the framework.
     * - The vardump.php file has the vardump global function, a superset for the var_dump function
     *   that return a more readable data. There is also the jsondump global function to return the
     *   var_dump data in JSON format.
     */
    require('const.php');
    require('vardump.php');

    /**
     * One of the biggest problems in programing is the directory handling. The best way to import,
     * create or get a file is using its absolute path, but it is pretty inconvenient. The best way
     * o resolve this is to store the base location of the project in a constant and use it when
     * necessary.
     */
    define('GALASTRI_PROJECT_DIR', (function () {
        $currentDir = explode(DIRECTORY_SEPARATOR, __DIR__);
        array_pop($currentDir);
        return implode(DIRECTORY_SEPARATOR, $currentDir);
    })());

    /**
     * Importing the autoload file. This file is handled by Composer.
     */
    require(GALASTRI_PROJECT_DIR.'/vendor/autoload.php');

    /**
     * Importing the first configuration file: debug.php
     *
     * All parameters are set in the Parameters class, which validates the data and thrown an
     * exception if there are misconfigurations. All parameters are described in the file
     * app/config/debug.php.
     *
     * The content of the file is stored in a variable, instead of a constant, because it will be
     * removed from the memory after being used.
     */
    $GALASTRI_DEBUG = require(GALASTRI_PROJECT_DIR.'/app/config/debug.php');

    Parameters::setDisplayErrors($GALASTRI_DEBUG['displayErrors']);
    Parameters::setShowBacklogData($GALASTRI_DEBUG['showBacklogData']);
    Parameters::setPerformanceAnalysis($GALASTRI_DEBUG['performanceAnalysis']);
    Parameters::setLanguage($GALASTRI_DEBUG['language'] ?? null);

    unset($GALASTRI_DEBUG);

    /**
     * The displayErrors parameter is used here, defining if the PHP will show internal errors to
     * the user or not. It also defines if the Debug class will show detailed errors or will only
     * show a generic message.
     */
    ini_set('display_errors', Parameters::getDisplayErrors());

    /**
     * The framework has language support. The messages of errors are stored in interface files,
     * located in the galastri/lang directory. Each file refers to a language.
     *
     * The problem is: each interface file has a different names but use the same constants to store
     * the messages, which make them impossible to be implemented to the classes in the same time.
     *
     * Because of this, instead of importing all language files into the classes, only the defined
     * language file will be imported. PHP doesn't allow to make dynamic implementation of
     * interfaces, so, the dynamic interface is stored a class alias first, called 'Language', and
     * only after that, it is implemented in the classes by calling the 'Language' alias.
     */
    class_alias('\galastri\\lang\\'.Parameters::getLanguage(), 'Language');

    /**
     * Importing other configuration files: project.php and routes.php.
     *
     * The content of these files are stored in variables, instead of constants, because they will
     * be removed from the memory after being used.
     *
     * For more information aboute this files and its parameters, see the files
     * app/config/project.php and app/config/routes.php.
     */
    $GALASTRI_PROJECT = require(GALASTRI_PROJECT_DIR.'/app/config/project.php');
    $GALASTRI_ROUTES = require(GALASTRI_PROJECT_DIR.'/app/config/routes.php');

    /**
     * Importing other configuration files: database.php, url-tags.php, mime-type.php and the
     * framework version.
     *
     * The content of these files will be used in other parts of the framework.
     */
    define('GALASTRI_DATABASE', require(GALASTRI_PROJECT_DIR.'/app/config/database.php'));
    define('GALASTRI_URL_TAGS', require(GALASTRI_PROJECT_DIR.'/app/config/url-tags.php'));
    define('GALASTRI_MIME_TYPE', require(GALASTRI_PROJECT_DIR.'/app/config/mime-type.php'));
    define('GALASTRI_VERSION', trim(file_get_contents(GALASTRI_PROJECT_DIR.'/galastri/VERSION')));

    /**
     * Beginning of the PerformanceAnalysis class. This class is used to measure the performance of
     * the framework. It can be used in standalone way, to measure specific parts of the users code.
     * However, it is defined here because of the debug parameter  'performanceAnalysis'. When it is
     * set, all the request will be measured. More information in the app/config/debug.php file.
     */
    PerformanceAnalysis::begin(PERFORMANCE_ANALYSIS_LABEL, Parameters::getPerformanceAnalysis());

    /**
     * After all these preliminary settings, the core of the Galastri Framework is executed.
     */
    core\Galastri::execute();

} catch (Exception $e) {
    /**
     * Here are the data and the returning execution if an error occurs in the configuration.
     */
    $data = [
        'code' => $e->getCode(),
        'origin' => null,
        'line' => null,
        'message' => vsprintf($e->getMessage(), $e->getData()),
        'warning' => true,
        'error' => true,
    ];
    header('Content-Type: application/json');
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
}
