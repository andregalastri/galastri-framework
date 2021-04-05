<?php
namespace galastri\core;

use \galastri\core\Debug;
use \galastri\extensions\Exception;
use \galastri\modules\Redirect;

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
     * This is a singleton class, so, the __construct() method is private to
     * avoid user to instanciate it.
     *
     * @return void
     */
    private function __construct()
    {
    }

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

        self::checkOffline()
            ::checkForceRedirect();
    }
    
    /**
     * Checks if the resolved route has the global parameter 'offline' sets as
     * true. In this case, a offline message is shown.
     *
     * @return \galastri\core\Galastri
     */
    private static function checkOffline()
    {
        Debug::setBacklog(debug_backtrace()[0])::bypassGenericMessage();

        try {
            $offline = Route::getGlobalParamValues('offline');
            if ($offline) {
                $offlineMessage = Route::getGlobalParamValues('messages')['offline'];
                
                throw new Exception($offlineMessage, self::OFFLINE_CODE);
            }
            return __CLASS__;
        } catch (Exception $e) {
            Debug::setError($e->getMessage(), $e->getCode())::print();
        }
    }
    
    /**
     * Checks if the resolved route has the global parameter 'forceRedirect'
     * sets as true. In this case, the request is redirected.
     *
     * @return \galastri\core\Galastri
     */
    private static function checkForceRedirect()
    {
        // Debug::setBacklog(debug_backtrace()[0]);

        // try {
        $forceRedirect = Route::getGlobalParamValues('forceRedirect');
        if ($forceRedirect) {
            Redirect::bypassUrlRoot()::to($forceRedirect);
            //
                // throw new Exception('', '');
        }

        return __CLASS__;
        // } catch (Exception $e) {
        //     Debug::setError($e->getMessage(), $e->getCode())::print();
        // }
    }
}
