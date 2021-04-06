<?php
/**
 * This is the bootstrap of the framework, basically defines main constants,
 * autoload behavior and imports config files.
 */

namespace galastri;

use \galastri\modules\Functions as F;
use \galastri\modules\PerformanceAnalysis;

ini_set('display_errors', 1); // Will remove it. Just kept it here for debugging.

/**
 * Importing files:
 *
 * - const.php : has all constants, which each value is bitwise ready to be used
 *   in many functions.
 *
 * - vardump.php : has vardump() function, an alternative to the PHP's
 *   var_dump() function. It shows data in more readable way.
 */
require_once('const.php');
require_once('vardump.php');

/**
 * Stores the root of the project. This helps to always call the same current
 * project folder.
 *
 * It gets the current __DIR__, which will return a directory like this:
 *
 * Current path:  /home/project/galastri
 *
 * The last direct of this path is removed, so with this we have the project
 * directory.
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
 * - autoload.php : has the behavior of the autoload. More information inside
 *   this file.
 */
require_once('autoload.php');

/**
 * Stores the debug configuration.
 */
define('GALASTRI_DEBUG', F::importFile('/app/config/debug.php'));

/**
 * Based on debug configuration, defines if PHP will display errors or not.
 */
ini_set('display_errors', GALASTRI_DEBUG['displayErrors']);

/**
 * Definition of many constants
 *
 * - GALASTRI_PROJECT : stores the project configuration.
 * - GALASTRI_VERSION : stores the framework version (only used on the default
 *   template).
 * - GALASTRI_ROUTES : stores all the routes configured, which will be used to
 *   define how framework will work based on the URL.
 */
define('GALASTRI_PROJECT', F::importFile('/app/config/project.php'));
define('GALASTRI_ROUTES', F::importFile('/app/config/routes.php'));
define('GALASTRI_URL_TAGS', F::importFile('/app/config/url-tags.php'));
define('GALASTRI_VERSION', F::getFileContents('/galastri/VERSION'));

PerformanceAnalysis::begin(PERFORMANCE_ANALYSIS_LABEL, GALASTRI_DEBUG['performanceAnalysis']);

/**
 * Starts the framework.
 */
core\Galastri::execute();
