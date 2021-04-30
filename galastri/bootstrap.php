<?php

/**
 * This is the bootstrap of the framework, basically defines main constants, autoload behavior and
 * imports config files.
 */

namespace galastri;

use galastri\core\Parameters;
use galastri\extensions\Exception;
use galastri\modules\PerformanceAnalysis;

try {
    ini_set('display_errors', 1); // Will remove it. Just kept it here for debugging.

    /**
     * Importing files:
     *
     * - const.php : has all constants, which each value is bitwise ready to be used in many functions.
     *
     * - vardump.php : has vardump() function, an alternative to the PHP's var_dump() function. It shows
     *   data in more readable way.
     */
    require('const.php');
    require('vardump.php');

    /**
     * Stores the root of the project. This helps to always call the same current project folder.
     *
     * It gets the current __DIR__, which will return a directory like this:
     *
     * Current path:  /home/project/galastri
     *
     * The last direct of this path is removed, so with this we have the project directory.
     *
     * Project Path:  /home/project
     *
     */
    define('GALASTRI_PROJECT_DIR', (function () {
        $currentDir = explode(DIRECTORY_SEPARATOR, __DIR__);
        array_pop($currentDir);
        return implode(DIRECTORY_SEPARATOR, $currentDir);
    })());

    /**
     * Importing file:
     *
     * - autoload.php : has the behavior of the autoload. More information inside this file.
     */
    require(GALASTRI_PROJECT_DIR.'/vendor/autoload.php');

    /**
     * Stores the debug configuration temporarily.
     */
    $GALASTRI_DEBUG = require(GALASTRI_PROJECT_DIR.'/app/config/debug.php');

    Parameters::setDisplayErrors($GALASTRI_DEBUG['displayErrors']);
    Parameters::setShowBacklogData($GALASTRI_DEBUG['showBacklogData']);
    Parameters::setPerformanceAnalysis($GALASTRI_DEBUG['performanceAnalysis']);
    Parameters::setLanguage($GALASTRI_DEBUG['language'] ?? null);

    unset($GALASTRI_DEBUG);

    /**
     * Based on debug configuration, defines if PHP will display errors or not.
     */
    ini_set('display_errors', Parameters::getDisplayErrors());

    /**
     * 
     */
    class_alias('\galastri\\lang\\'.Parameters::getLanguage(), 'Language');

    /**
     * Definition of globals (will be unset after use);
     *
     * - GALASTRI_PROJECT : stores the project configuration.
     * - GALASTRI_ROUTES : stores all the routes configured, which will be used to define how framework
     */
    $GALASTRI_PROJECT = require(GALASTRI_PROJECT_DIR.'/app/config/project.php');
    $GALASTRI_ROUTES = require(GALASTRI_PROJECT_DIR.'/app/config/routes.php');

    /**
     * Definition of constants
     *
     * - GALASTRI_URL_TAGS : stores the framework version (only used on the default template).
     * - GALASTRI_VERSION : stores the framework version (only used on the default template).
     *   will work based on the URL.
     */
    define('GALASTRI_URL_TAGS', require(GALASTRI_PROJECT_DIR.'/app/config/url-tags.php'));
    define('GALASTRI_VERSION', file_get_contents(GALASTRI_PROJECT_DIR.'/galastri/VERSION'));

    PerformanceAnalysis::begin(PERFORMANCE_ANALYSIS_LABEL, Parameters::getPerformanceAnalysis());

    /**
     * Starts the framework.
     */
    core\Galastri::execute();
} catch (Exception $e) {
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

