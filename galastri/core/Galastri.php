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
    const DEFAULT_NODE_NAMESPACE = '\app\controllers';

    const OFFLINE_CODE = 'OFFLINE';
    const UNDEFINED_SOLVER = ['UNDEFINED_SOLVER', "There is no parameter 'solver' defined to this route. Configure it in the '\app\config\routes.php'."];
    const INVALID_SOLVER   = ['INVALID_SOLVER', "Invalid solver '%s'. Inform a valid solver: view, json, file or text."];
    const ERROR_404 = ['ERROR_404', "Error 404: The requested route was not found."];
    const CONTROLLER_NOT_FOUND = ['CONTROLLER_NOT_FOUND', "Requested controller '%s' doesn't exist. Check if the file '%s.php' exists in directory '%s' or if its namespace was correctly set."];

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
        try {
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
            self::checkParentNodeClass();

        } catch (Exception $e) {
            PerformanceAnalysis::store(PERFORMANCE_ANALYSIS_LABEL);
            Debug::setError($e->getMessage(), $e->getCode(), $e->getData())::print();
        }

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

        $offline = Route::getGlobalParams('offline');

        if ($offline) {
            $offlineMessage = Route::getGlobalParams('messages')['offline'];
                
            throw new Exception($offlineMessage, self::OFFLINE_CODE);
        }

        PerformanceAnalysis::flush(PERFORMANCE_ANALYSIS_LABEL);
    }
    
    /**
     * Checks if the resolved route has the global parameter 'forceRedirect'
     * sets as true. In this case, the request is redirected.
     *
     * @return void
     */
    private static function checkForceRedirect()
    {
        $forceRedirect = Route::getGlobalParams('forceRedirect');

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

        $solver = Route::getGlobalParams('solver');

        if ($solver) {
            if (!F::arrayValueExists($solver, ['view', 'json', 'file', 'text'])) {
                throw new Exception(self::INVALID_SOLVER[1], self::INVALID_SOLVER[0], [$solver]);
            }
        } else {
            throw new Exception(self::UNDEFINED_SOLVER[1], self::UNDEFINED_SOLVER[0]);
        }

        PerformanceAnalysis::flush(PERFORMANCE_ANALYSIS_LABEL);
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

        $parentNodeName = Route::getParentNodeName();
        $childNodeName = Route::getChildNodeName();
        $solver = Route::getGlobalParams('solver');
        $notFoundRedirect = Route::getGlobalParams('notFoundRedirect');

        $error = false;

        if ($parentNodeName === false and $childNodeName === false or $childNodeName === false) {
            if ($solver === 'view' or $solver === 'file') {
                PerformanceAnalysis::flush(PERFORMANCE_ANALYSIS_LABEL);
                Redirect::to($notFoundRedirect);
            } else {
                throw new Exception(self::ERROR_404[1], self::ERROR_404[0], [$solver]);
            }
        }

        PerformanceAnalysis::flush(PERFORMANCE_ANALYSIS_LABEL);
    }
    
    /**
     * Checks if there is a class to be called, based on the resolved URL nodes
     * in \galastri\Core\Route.
     *
     * If there is a specific controller set, then that is the controller that
     * will be call. If not, then the default way to call controllers will be
     * executed:
     *
     * \base\namespace\ParentNode
     *
     * If the call is to a child node, then:
     *
     * \base\namespace\ParentNode\ChildNode
     *
     * The base namespace can be the default \app\controller or a custom one,
     * set in the route configuration 'namespace' parameter. When this parameter
     * isn't set, the default one is used.
     *
     * @return void
     */
    private static function checkParentNodeClass()
    {
        Debug::setBacklog();

        $controllerNamespace = Route::getControllerNamespace();

        if ($nodeController = Route::getParentNodeParams('controller')) {
            $controllerCall = $nodeController;
        } else {
            $baseNodeNamespace = Route::getGlobalParams('namespace') ?: self::DEFAULT_NODE_NAMESPACE;
            $controllerCall = $baseNodeNamespace.implode($controllerNamespace);
        }

        if (class_exists($controllerCall) === false) {
            $workingController = explode('\\', $controllerCall);
            
            $notFoundClass = str_replace('\\', '', array_pop($workingController));
            $notFoundNamespace = implode('/', $workingController);

            throw new Exception(self::CONTROLLER_NOT_FOUND[1], self::CONTROLLER_NOT_FOUND[0], [$controllerCall, $notFoundClass, $notFoundNamespace]);
        }
    }
}
