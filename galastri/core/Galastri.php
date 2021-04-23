<?php

namespace galastri\core;

use galastri\core\Debug;
use galastri\core\Controller;
use galastri\extensions\Exception;
use galastri\extensions\output\View;
use galastri\modules\Redirect;
use galastri\modules\PerformanceAnalysis;
use galastri\modules\Toolbox;

/**
 * This is the main core class. Here we will verify if the classes, methods and parameters defined
 * in the /app/config/routes.php are valid and then call the controller, if it is required, and
 * finally call the output, a script that will resolve the request and return a type of data.
 */
final class Galastri implements \Language
{
    use View;
    
    const DEFAULT_NODE_NAMESPACE = 'app\controllers';
    const VIEW_BASE_FOLDER = '/app/views';

    private static string $routeControllerName;
    private static Controller $routeController;

    private static bool $checkedOffline = false;
    private static bool $checkedForceRedirect = false;
    private static bool $checkedRouteNodesExists = false;
    private static bool $checkedOutput = false;
    private static bool $checkedController = false;
    private static bool $checkedControllerExtendsCore = false;
    private static bool $checkedControllerMethod = false;

    /**
     * This is a singleton class, so, the __construct() method is private to avoid user to
     * instanciate it.
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
    public static function execute(): void
    {
        try {
            /**
             * Sets the timezone if it is configured in /app/config/project.php. If it is false, the
             * timezone will not be configured here.
             */
            if (GALASTRI_PROJECT['timezone']) {
                date_default_timezone_set(GALASTRI_PROJECT['timezone']);
            }

            /**
             * Starts the resolution of the URL routes and its configurations in
             * the /app/config/routes.php file.
             */
            Route::resolve();

            self::checkOffline();
            self::checkForceRedirect();
            self::checkOutput();
            self::checkRouteNodesExists();
            self::checkController();
            self::checkControllerExtendsCore();
            self::checkControllerMethod();
            self::callController();
            self::callOutput();
        } catch (Exception $e) {
            Debug::setError($e->getMessage(), $e->getCode(), $e->getData())::print();
        } finally {
            PerformanceAnalysis::store(PERFORMANCE_ANALYSIS_LABEL);
        }
    }

    /**
     * Checks if the resolved route has the route parameter 'offline' sets as true. In this case, a
     * offline message is shown.
     *
     * @return void
     */
    private static function checkOffline(): void
    {
        Debug::setBacklog();

        $offline = Route::getRouteParam('offline');

        if ($offline) {
            $offlineMessage = Route::getRouteParam('defaultmessage')['offline'];

            throw new Exception($offlineMessage, self::OFFLINE[0]);
        }

        self::$checkedOffline = true;

        PerformanceAnalysis::flush(PERFORMANCE_ANALYSIS_LABEL);
    }

    /**
     * Checks if the resolved route has the route parameter 'forceRedirect' sets as true. In this
     * case, the request is redirected.
     *
     * @return void
     */
    private static function checkForceRedirect(): void
    {
        $forceRedirect = Route::getRouteParam('forceRedirect');

        if ($forceRedirect) {
            Redirect::bypassUrlRoot()::to($forceRedirect);
        }

        self::$checkedForceRedirect = true;
        PerformanceAnalysis::flush(PERFORMANCE_ANALYSIS_LABEL);
    }

    /**
     * Checks if the resolved route has the route parameter 'output' set properly. If not, an
     * exception is thrown.
     *
     * @return void
     */
    private static function checkOutput(): void
    {
        Debug::setBacklog();

        $output = Route::getRouteParam('output');

        if ($output) {
            if (!Toolbox::arrayValueExists($output, ['view', 'json', 'file', 'text'])) {
                throw new Exception(self::INVALID_OUTPUT[1], self::INVALID_OUTPUT[0], [$output]);
            }
        } else {
            throw new Exception(self::UNDEFINED_OUTPUT[1], self::UNDEFINED_OUTPUT[0]);
        }

        self::$checkedRouteNodesExists = true;
        PerformanceAnalysis::flush(PERFORMANCE_ANALYSIS_LABEL);
    }

    /**
     * Checks if the route configuration file has the url nodes informed by the request. If the
     * child node and parent node exists in the route configuration, then everything is ok, but if
     * it is not, then the request is redirected to the 404 page (if the output is view of file) or
     * return an exception text (if the output is json or text).
     *
     * @return void
     */
    private static function checkRouteNodesExists(): void
    {
        Debug::setBacklog()::bypassGenericMessage();

        $parentNodeName = Route::getParentNodeName();
        $childNodeName = Route::getChildNodeName();
        $output = Route::getRouteParam('output');
        $notFoundRedirect = Route::getRouteParam('notFoundRedirect');

        if ($parentNodeName === null and $childNodeName === null or $childNodeName === null) {
            if ($output === 'view' or $output === 'file') {
                PerformanceAnalysis::flush(PERFORMANCE_ANALYSIS_LABEL);
                Redirect::to($notFoundRedirect);
            } else {
                throw new Exception(self::ERROR_404[1], self::ERROR_404[0], [$output]);
            }
        }

        self::$checkedOutput = true;
        PerformanceAnalysis::flush(PERFORMANCE_ANALYSIS_LABEL);
    }

    /**
     * Checks if there is a class to be called, based on the resolved URL nodes in
     * \galastri\Core\Route.
     *
     * If there is a specific controller set, then that is the controller that will be call. If not,
     * then the default way to call controllers will be executed:
     *
     * \base\namespace\ParentNode
     *
     * If the call is to a child node, then:
     *
     * \base\namespace\ParentNode\ChildNode
     *
     * The base namespace can be the default \app\controller or a custom one, set in the route
     * configuration 'namespace' parameter. When this parameter isn't set, the default one is used.
     *
     * @return void
     */
    private static function checkController(): void
    {
        Debug::setBacklog();

        $controllerNamespace = Route::getControllerNamespace();

        if ($nodeController = Route::getParentNodeParam('controller')) {
            $routeController = $nodeController;
        } else {
            $baseNodeNamespace = Route::getRouteParam('namespace') ?: self::DEFAULT_NODE_NAMESPACE;
            $routeController = $baseNodeNamespace . implode($controllerNamespace);
        }

        if (class_exists($routeController) === false) {
            $workingController = explode('\\', $routeController);

            $notFoundClass = str_replace('\\', '', array_pop($workingController));
            $notFoundNamespace = implode('/', $workingController);

            throw new Exception(self::CONTROLLER_NOT_FOUND[1], self::CONTROLLER_NOT_FOUND[0], [$routeController, $notFoundClass, $notFoundNamespace]);
        }

        self::$routeControllerName = $routeController;

        self::$checkedController = true;
        PerformanceAnalysis::flush(PERFORMANCE_ANALYSIS_LABEL);
    }


    /**
     * Checks if the route controller extends the core controller, which is required. If it isn't,
     * an exception is thrown.
     *
     * @return void
     */
    private static function checkControllerExtendsCore(): void
    {
        Debug::setBacklog();

        if (is_subclass_of(self::$routeControllerName, '\galastri\Core\Controller') === false) {
            throw new Exception(self::CONTROLLER_DOESNT_EXTENDS_CORE[1], self::CONTROLLER_DOESNT_EXTENDS_CORE[0], [self::$routeControllerName]);
        }

        self::$checkedControllerExtendsCore = true;
        PerformanceAnalysis::flush(PERFORMANCE_ANALYSIS_LABEL);
    }

    /**
     * Checks if the child node exists as a method inside the route controller. If it isn't, an
     * exception is thrown.
     *
     * @return void
     */
    private static function checkControllerMethod(): void
    {
        Debug::setBacklog();

        $method = Route::getChildNodeName();

        if (method_exists(self::$routeControllerName, $method) === false) {
            throw new Exception(self::CONTROLLER_METHOD_NOT_FOUND[1], self::CONTROLLER_METHOD_NOT_FOUND[0], [self::$routeControllerName, $method]);
        }

        self::$checkedControllerMethod = true;
        PerformanceAnalysis::flush(PERFORMANCE_ANALYSIS_LABEL);
    }

    /**
     * Checks if all stages of validation are true and then calls for the controller creating a
     * instance of it inside the $routeController property.
     *
     * @return void
     */
    private static function callController(): void
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
            if (!self::$checkedOutput) {
                return 'checkedOutput';
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

        self::$routeController = new self::$routeControllerName();

        // if (!empty(ob_get_contents())) {
        //     throw new Exception('You cannot print data in the controller.', 'CONTROLLER_CANT_PRINT_DATA');
        // }

        PerformanceAnalysis::flush(PERFORMANCE_ANALYSIS_LABEL);
    }

    private static function callOutput(): void
    {
        Debug::setBacklog();
        
        $output = self::$routeController->getOutput();

        self::$output();

        PerformanceAnalysis::flush(PERFORMANCE_ANALYSIS_LABEL);
    }
}
