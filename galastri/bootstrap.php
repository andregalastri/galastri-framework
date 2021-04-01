<?php
namespace galastri;
use \galastri\modules\Functions as F;

ini_set('display_errors', 1);

require_once('const.php');
require_once('vardump.php');

define('GALASTRI_PROJECT_DIR', (function(){
    $currentDir = explode(DIRECTORY_SEPARATOR, __DIR__);
    array_pop($currentDir);
    return implode(DIRECTORY_SEPARATOR, $currentDir);
})());

require_once('autoload.php');

define('GALASTRI_DEBUG', F::importFile('/app/config/debug.php'));

ini_set('display_errors', GALASTRI_DEBUG['displayErrors']);

define('GALASTRI_PROJECT', F::importFile('/app/config/project.php'));
define('GALASTRI_VERSION', F::getFileContents('/galastri/VERSION'));

define('GALASTRI_ROUTES', F::importFile('/app/config/routes.php'));

core\Galastri::execute();