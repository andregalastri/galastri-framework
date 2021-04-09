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
final class Galastri
{
    const DEFAULT_NODE_NAMESPACE = '\app\controllers';

    const OFFLINE_CODE = 'OFFLINE';
    const UNDEFINED_SOLVER = ['UNDEFINED_SOLVER', "There is no parameter 'solver' defined to this route. Configure it in the '\app\config\routes.php'."];
    const INVALID_SOLVER   = ['INVALID_SOLVER', "Invalid solver '%s'. Inform a valid solver: view, json, file or text."];
    const ERROR_404 = ['ERROR_404', "Error 404: The requested route was not found."];
    const CONTROLLER_NOT_FOUND = ['CONTROLLER_NOT_FOUND', "Requested controller '%s' doesn't exist. Check if the file '%s.php' exists in directory '%s' or if its namespace was correctly set."];
    const CONTROLLER_DOESNT_EXTENDS_CORE = ['CONTROLLER_DOESNT_EXTENDS_CORE', "Controller '%s' is not extending the core class \galastri\core\Controller. Add the core class to your controller class."];
    const CONTROLLER_METHOD_NOT_FOUND = ['CONTROLLER_METHOD_NOT_FOUND', "Controller '%s' doesn't have the requested method '@%s'."];
    const CONTROLLER_PARENT_CONSTRUCT_NOT_CALLED = ['CONTROLLER_PARENT_CONSTRUCT_NOT_CALLED', "Controller '%s' has a __construct() method that is not calling the core's __contruct(). Please, add the code parent::__construct(); right after the definition of your controller's __construct()."];
    const VALIDATION_ERROR = ['VALIDATION_ERROR', "The validation '%s' was returned as invalid. The execution cannot proceed."];

    private static $controller = false;

    private static $checkedOffline = false;
    private static $checkedForceRedirect = false;
    private static $checkedRouteNodesExists = false;
    private static $checkedSolver = false;
    private static $checkedController = false;
    private static $checkedControllerExtendsCore = false;
    private static $checkedControllerMethod = false;


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
            self::checkController();
            self::checkControllerExtendsCore();
            self::checkControllerMethod();
            self::callController();

            PerformanceAnalysis::store(PERFORMANCE_ANALYSIS_LABEL);
        } catch (Exception $e) {
            PerformanceAnalysis::store(PERFORMANCE_ANALYSIS_LABEL);
            Debug::setError($e->getMessage(), $e->getCode(), $e->getData())::print();
        }
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

        $offline = Route::getGlobalParam('offline');

        if ($offline) {
            $offlineMessage = Route::getGlobalParam('defaultmessage')['offline'];
                
            throw new Exception($offlineMessage, self::OFFLINE_CODE);
        }

        self::$checkedOffline = true;

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
        $forceRedirect = Route::getGlobalParam('forceRedirect');

        if ($forceRedirect) {
            Redirect::bypassUrlRoot()::to($forceRedirect);
        }
        
        self::$checkedForceRedirect = true;
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

        $solver = Route::getGlobalParam('solver');

        if ($solver) {
            if (!F::arrayValueExists($solver, ['view', 'json', 'file', 'text'])) {
                throw new Exception(self::INVALID_SOLVER[1], self::INVALID_SOLVER[0], [$solver]);
            }
        } else {
            throw new Exception(self::UNDEFINED_SOLVER[1], self::UNDEFINED_SOLVER[0]);
        }
        
        self::$checkedRouteNodesExists = true;
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
        $solver = Route::getGlobalParam('solver');
        $notFoundRedirect = Route::getGlobalParam('notFoundRedirect');

        $error = false;

        if ($parentNodeName === false and $childNodeName === false or $childNodeName === false) {
            if ($solver === 'view' or $solver === 'file') {
                PerformanceAnalysis::flush(PERFORMANCE_ANALYSIS_LABEL);
                Redirect::to($notFoundRedirect);
            } else {
                throw new Exception(self::ERROR_404[1], self::ERROR_404[0], [$solver]);
            }
        }
        
        self::$checkedSolver = true;
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
    private static function checkController()
    {
        Debug::setBacklog();

        $controllerNamespace = Route::getControllerNamespace();

        if ($nodeController = Route::getParentNodeParam('controller')) {
            $controller = $nodeController;
        } else {
            $baseNodeNamespace = Route::getGlobalParam('namespace') ?: self::DEFAULT_NODE_NAMESPACE;
            $controller = $baseNodeNamespace.implode($controllerNamespace);
        }

        if (class_exists($controller) === false) {
            $workingController = explode('\\', $controller);
            
            $notFoundClass = str_replace('\\', '', array_pop($workingController));
            $notFoundNamespace = implode('/', $workingController);

            throw new Exception(self::CONTROLLER_NOT_FOUND[1], self::CONTROLLER_NOT_FOUND[0], [$controller, $notFoundClass, $notFoundNamespace]);
        }

        self::$controller = $controller;
        
        self::$checkedController = true;
        PerformanceAnalysis::flush(PERFORMANCE_ANALYSIS_LABEL);
    }

    
    /**
     * checkControllerExtendsCore
     *
     * @return void
     */
    private static function checkControllerExtendsCore()
    {
        Debug::setBacklog();

        if (is_subclass_of(self::$controller, '\galastri\Core\Controller') === false) {
            throw new Exception(self::CONTROLLER_DOESNT_EXTENDS_CORE[1], self::CONTROLLER_DOESNT_EXTENDS_CORE[0], [self::$controller]);
        }
        
        self::$checkedControllerExtendsCore = true;
        PerformanceAnalysis::flush(PERFORMANCE_ANALYSIS_LABEL);
    }
    
    /**
     * checkControllerMethod
     *
     * @return void
     */
    private static function checkControllerMethod()
    {
        Debug::setBacklog();
        
        $method = Route::getChildNodeName();
        
        if (method_exists(self::$controller, $method) === false) {
            throw new Exception(self::CONTROLLER_METHOD_NOT_FOUND[1], self::CONTROLLER_METHOD_NOT_FOUND[0], [self::$controller, $method]);
        }

        self::$checkedControllerMethod = true;
        PerformanceAnalysis::flush(PERFORMANCE_ANALYSIS_LABEL);
    }
    
    /**
     * callController
     *
     * @return void
     */
    private static function callController()
    {
        Debug::setBacklog();

        $error = (function () {
            if (!self::$checkedOffline) {
                return 'checkedOffline';
            }
            if (!self::$checkedForceRedirect) {
                return 'checkedForceRedirect';
            }
            if (!self::$checkedRouteNodesExists) {
                return 'checkedRouteNodesExists';
            }
            if (!self::$checkedSolver) {
                return 'checkedSolver';
            }
            if (!self::$checkedController) {
                return 'checkedController';
            }
            if (!self::$checkedControllerExtendsCore) {
                return 'checkedControllerExtendsCore';
            }
            if (!self::$checkedControllerMethod) {
                return 'checkedControllerMethod';
            }
            
            return false;
        })();

        if ($error) {
            throw new Exception(self::VALIDATION_ERROR[1], self::VALIDATION_ERROR[0], [$error]);
        }

        self::$controller = new self::$controller();

        PerformanceAnalysis::flush(PERFORMANCE_ANALYSIS_LABEL);
    }
}
