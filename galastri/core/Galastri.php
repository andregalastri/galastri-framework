<?php
namespace galastri\core;

use \galastri\core\Debug;
use \galastri\extensions\Exception;

/**
 * This is the main core class. Here we will verify if the classes, methods and
 * parameters defined in the /app/config/routes.php are valid and then call the
 * controller, if it is required, and finally call the solver, a script that
 * will resolve the request and return a type of data.
 */
class Galastri
{
    const OFFLINE_CODE = 'OFFLINE_001';

    /**
     * Starts the chain of validations and executions.
     *
     * @return void
     */
    public static function execute()
    {
        /**
         * Sets the timezone if it is configured in /app/config/project.php. If
         * it is false, the timezone will not be configured here.
         */
        if (GALASTRI_PROJECT['timezone']) {
            date_default_timezone_set(GALASTRI_PROJECT['timezone']);
        }

        /**
         * Starts the resolution of the URL routes and its configurations in the
         * /app/config/routes.php file.
         */
        Route::resolve();

        self::checkOffline();
    }

    private static function checkOffline()
    {
        Debug::setBacklog(debug_backtrace()[0])::bypassGenericMessage();

        try {
            $offline = Route::getGlobalParamValues('offline');
            if (!$offline) {
                throw new Exception(GALASTRI_PROJECT['messages']['offline'], self::OFFLINE_CODE);
            }
        } catch (Exception $e) {
            Debug::setError($e->getMessage(), $e->getCode())::print();
        }
    }
}
