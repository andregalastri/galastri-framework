<?php

namespace galastri\core;

use galastri\core\Debug;
use galastri\core\Controller;
use galastri\core\Parameters;
use galastri\extensions\Exception;
use galastri\extensions\output\View;
use galastri\extensions\output\Json;
use galastri\extensions\output\Text;
use galastri\extensions\output\File;
use galastri\modules\Redirect;
use galastri\modules\PerformanceAnalysis;

/**
 * This is the main core class. Here we will verify if the classes, methods and parameters defined
 * in the /app/config/routes.php are valid and then call the controller, if it is required, and
 * finally call the output, a script that will resolve the request and return a type of data.
 */
final class Galastri implements \Language
{
    use View;
    use Json;
    use Text;
    use File;

    const DEFAULT_NODE_NAMESPACE = 'app\controllers';
    const VIEW_BASE_FOLDER = '/app/views';
    const CORE_CONTROLLER = '\galastri\Core\Controller';

    private static string $routeControllerName;
    private static Controller $routeController;

    private static bool $checkedOffline = false;
    private static bool $checkedForceRedirect = false;
    private static bool $checkedRouteNodesExists = true;
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
             * Starts the resolution of the URL routes and its configurations in
             * the /app/config/routes.php file.
             */
            Route::resolve();

            self::checkOffline();
            self::checkForceRedirect();
            self::checkRouteNodesExists();
            self::checkOutput();
            self::checkController();
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

        if (Parameters::getOffline()) {
            throw new Exception(Parameters::getOfflineMessage(), self::DEFAULT_OFFLINE_MESSAGE[0]);
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
        $forceRedirect = Parameters::getForceRedirect();

        if ($forceRedirect !== null) {
            Redirect::bypassUrlRoot()::to($forceRedirect);
        }

        self::$checkedForceRedirect = true;
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

        if (
            Route::getParentNodeName() === null and
            Route::getChildNodeName() === null or
            Route::getChildNodeName() === null
        ) {
            self::$checkedRouteNodesExists = false;
        }

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

        if (!self::$checkedRouteNodesExists) {
            if (Parameters::getNotFoundRedirect() === null or Parameters::getOutput() === 'json' or Parameters::getOutput() === 'text') {
                throw new Exception(self::ERROR_404[1], self::ERROR_404[0]);
            } else {
                PerformanceAnalysis::flush(PERFORMANCE_ANALYSIS_LABEL);
                Redirect::to(Parameters::getNotFoundRedirect());
            }
        } else {
            if (Parameters::getOutput() === null) {
                throw new Exception(self::UNDEFINED_OUTPUT[1], self::UNDEFINED_OUTPUT[0]);
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
        $controllerIsRequired = self::{Parameters::getOutput().'RequiresController'}();

        if ($nodeController = Parameters::getController()) {
            $routeController = $nodeController;
        } else {
            $baseNodeNamespace = Parameters::getNamespace() ?: self::DEFAULT_NODE_NAMESPACE;
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

        self::checkControllerExtendsCore();
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

        if (is_subclass_of(self::$routeControllerName, self::CORE_CONTROLLER) === false) {
            throw new Exception(self::CONTROLLER_DOESNT_EXTENDS_CORE[1], self::CONTROLLER_DOESNT_EXTENDS_CORE[0], [self::$routeControllerName]);
        }

        self::$checkedControllerExtendsCore = true;
        PerformanceAnalysis::flush(PERFORMANCE_ANALYSIS_LABEL);

        self::checkControllerMethod();
    }

    /**
     * Checks if the child node exists as a method inside the route controller. If it isn't, an
     * exception is thrown.
     *
     * It also checks if there is a request method defined in the route parameter requestMethod and,
     * if it is, checks if it exists in the route controller.
     *
     * @return void
     */
    private static function checkControllerMethod(): void
    {
        Debug::setBacklog();

        $method = Route::getChildNodeName();
        $requestMethod = Parameters::getRequestMethod();

        if (method_exists(self::$routeControllerName, $method) === false) {
            throw new Exception(self::CONTROLLER_METHOD_NOT_FOUND[1], self::CONTROLLER_METHOD_NOT_FOUND[0], [self::$routeControllerName, $method]);
        }

        if (!empty($requestMethod)) {
            if (!method_exists(self::$routeControllerName, $requestMethod)){
                throw new Exception(self::CONTROLLER_METHOD_NOT_FOUND[1], self::CONTROLLER_METHOD_NOT_FOUND[0], [self::$routeControllerName, $requestMethod]);
            }
        }

        self::$checkedControllerMethod = true;
        PerformanceAnalysis::flush(PERFORMANCE_ANALYSIS_LABEL);

        self::callController();
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
