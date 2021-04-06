<?php
namespace galastri\core;

use \galastri\core\Debug;
use \galastri\extensions\Exception;
use \galastri\modules\Redirect;
use \galastri\modules\PerformanceAnalysis;
use \galastri\modules\Functions as F;

/**
 * This is the main core class. Here we will verify if the classes, methods and
 * parameters defined in the /app/config/routes.php are valid and then call the
 * controller, if it is required, and finally call the solver, a script that
 * will resolve the request and return a type of data.
 */
class Galastri
{
    const OFFLINE_CODE = 'OFFLINE_001';
    
    const UNDEFINED_SOLVER = ['SOLVER_001', "There is no parameter 'solver' defined to this route. Configure it in the '\app\config\routes.php'."];
    const INVALID_SOLVER   = ['SOLVER_002', "Invalid solver '%s'. Inform a valid solver: view, json, file or text."];
    
    const ERROR_404 = ['NOT_FOUND', "Error 404: The requested route was not found."];

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

        self::checkOffline();
        self::checkForceRedirect();
        self::checkRouteNodesExists();
        self::checkSolver();

        PerformanceAnalysis::store(PERFORMANCE_ANALYSIS_LABEL);
    }
    
    /**
     * Checks if the resolved route has the global parameter 'offline' sets as
     * true. In this case, a offline message is shown.
     *
     * @return void
     */
    private static function checkOffline()
    {
        Debug::setBacklog()::bypassGenericMessage();

        try {
            $offline = Route::getGlobalParamValues('offline');
            if ($offline) {
                $offlineMessage = Route::getGlobalParamValues('messages')['offline'];
                
                throw new Exception($offlineMessage, self::OFFLINE_CODE);
            }

            PerformanceAnalysis::flush(PERFORMANCE_ANALYSIS_LABEL);

        } catch (Exception $e) {
            Debug::setError($e->getMessage(), $e->getCode())::print();
        }
    }
    
    /**
     * Checks if the resolved route has the global parameter 'forceRedirect'
     * sets as true. In this case, the request is redirected.
     *
     * @return void
     */
    private static function checkForceRedirect()
    {
        $forceRedirect = Route::getGlobalParamValues('forceRedirect');

        if ($forceRedirect) {
            Redirect::bypassUrlRoot()::to($forceRedirect);
        }

        PerformanceAnalysis::flush(PERFORMANCE_ANALYSIS_LABEL);
    }
    
    /**
     * Checks if the resolved route has the global parameter 'solver' set
     * properly. If not, an exception is thrown.
     *
     * @return void
     */
    private static function checkSolver()
    {
        Debug::setBacklog();

        try {
            $solver = Route::getGlobalParamValues('solver');

            if ($solver) {
                if (!F::arrayValueExists($solver, ['view', 'json', 'file', 'text'])) {
                    throw new Exception(self::INVALID_SOLVER[1], self::INVALID_SOLVER[0], [$solver]);
                }
            } else {
                throw new Exception(self::UNDEFINED_SOLVER[1], self::UNDEFINED_SOLVER[0]);
            }

            PerformanceAnalysis::flush(PERFORMANCE_ANALYSIS_LABEL);
        } catch (Exception $e) {
            Debug::setError($e->getMessage(), $e->getCode(), $e->getData())::print();
        }
    }
    
    /**
     * Checks if the route configuration file has the url nodes informed by the
     * request. If the child node and parent node exists in the route
     * configuration, then everything is ok, but if it is not, then the request
     * is redirected to the 404 page (if the solver is view of file) or return
     * an exception text (if the solver is json or text).
     *
     * @return void
     */
    private static function checkRouteNodesExists()
    {
        Debug::setBacklog()::bypassGenericMessage();

        try {
            $parentNodeName = Route::getParentNodeName();
            $childNodeName = Route::getChildNodeName();
            $solver = Route::getGlobalParamValues('solver');
            $notFoundRedirect = Route::getGlobalParamValues('notFoundRedirect');

            if ($parentNodeName === false and $childNodeName === false or $childNodeName === false) {
                if ($solver === 'view' or $solver === 'file') {
                    Redirect::to($notFoundRedirect);
                } else {
                    throw new Exception(self::ERROR_404[1], self::ERROR_404[0], [$solver]);
                }
            }

            PerformanceAnalysis::flush(PERFORMANCE_ANALYSIS_LABEL);
        } catch (Exception $e) {
            Debug::setError($e->getMessage(), $e->getCode())::print();
        }
    }
}
