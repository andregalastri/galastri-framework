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
use galastri\modules\Authentication;
use galastri\modules\PerformanceAnalysis;

/**
 * This is the main class of the framework. It will do a series of executions based on the
 * parameters defined in the configuration files.
 *
 * This class:
 * - Execute the route resolving and will store its configurations.
 * - Check if the offline parameter is set.
 * - Check if the forceRedirect parameter is set.
 * - Check if the route exists.
 * - Check if an authTag is set and if the user have it in its session.
 * - If an output is set, it will check if it requires a controller and check if the route
 *   controller exists. If it doesn't require a controller, it is ignored.
 *   - Check if the route controller extends the core controller.
 *   - Check if the route controller have the method based on its child node.
 *   - Call the route controller.
 * - Check if the output is set.
 * - Call the output.
 */
final class Galastri implements \Language
{
    /**
     * All the processed data in this framework need to be returned by scripts called 'outputs'.
     * They are defined in the route configuration file or in a route controller.
     *
     * Right now there are the following outputs:
     *
     * - View: This output return a view, based on the MVC concept. All the data processed by the
     *   route controller are returned and made available to a template file, specified in the
     *   project or route configuration file, or by the route controller itself. In this template
     *   file, a view file can be imported to print a HTML to the client's browser. It requires a
     *   route controller to work.
     *
     * - Json: This output return a JSON with the data processed by the route controller. It
     *   requires a route controller to work.
     *
     * - Text: This output return a plain text with the data processed by the route controller. It
     *   is useful for command line outputs. It requires a route controller to work.
     *
     * - File: This output return a file. It doesn't require a route controller to work, but it
     *   needs a base folder set in the route configuration. When a controller isn't set to the
     *   output, all URL nodes are used as the file path. If the controller is set, then the
     *   controller can change this behavior or execute specific commands.
     */
    use View;
    use Json;
    use Text;
    use File;

    /**
     * Sets the default namespace for the route controllers. It will be overwritten if a namespace is
     * defined in the route configuration.
     */
    const DEFAULT_NODE_NAMESPACE = 'app\controllers';

    /**
     * Sets a default base folder to the view output. It will be overwritten if a custom base folder
     * is defined in the route configuration.
     */
    const VIEW_BASE_FOLDER = '/app/views';

    /**
     * Sets the core controller class.
     */
    const CORE_CONTROLLER = '\galastri\Core\Controller';

    /**
     * Stores route controller name that will be called based on the route configuration file.
     *
     * @var string
     */
    private static string $routeControllerName;

    /**
     * Stores route controller instance, created based on the $routeControllerName property.
     *
     * @var Controller|null
     */
    private static ?Controller $routeController = null;

    /**
     * Stores if the controller requires a controller or not. It is set by the output files.
     *
     * @var bool
     */
    private static bool $controllerIsRequired = true;

    /**
     * This is a singleton class, the __construct() method is private to avoid users to instanciate
     * it.
     *
     * @return void
     */
    private function __construct()
    {
    }

    /**
     * This is the main method of the framework. It has a chain of methods that execute the entire
     * framework. Each method is inside in the main try/catch block. Every extension or module of
     * the framework can thrown an exception that will be caught by it, in case of an error occurs.
     *
     * When an exception is thrown in any part of the framework, the Debug class will be responsible
     * to return the error data.
     *
     * @return void
     */
    public static function execute(): void
    {
        try {
            /**
             * The first thing that it will execute is the route resolution. For more information,
             * see the galastri\core\Route class file.
             */
            Route::resolve();

            /**
             * Next, a series of verifications are checked. Each of these methods are explained in
             * their definitions.
             */
            self::checkOffline();
            self::checkForceRedirect();
            self::checkRouteExists();
            self::checkAuthentication();
            self::checkController();
        } catch (Exception $e) {
            Debug::setError($e->getMessage(), $e->getCode(), $e->getData())::print();
        } finally {
            /**
             * After the execution of the method, if the performance analysis is set, it will store
             * the result.
             */
            PerformanceAnalysis::store(PERFORMANCE_ANALYSIS_LABEL);
        }
    }

    /**
     * This method checks if the 'offline' parameter is set in the project or the route
     * configuration. If it is true, it will return an exception showing a message that the route is
     * offline.
     *
     * The message can be defined by the 'offlineMessage' parameter, set in the project of route
     * parameter.
     *
     * @return void
     */
    private static function checkOffline(): void
    {
        Debug::setBacklog();

        if (Parameters::getOffline()) {
            throw new Exception(Parameters::getOfflineMessage(), self::DEFAULT_OFFLINE_MESSAGE[0]);
        }

        PerformanceAnalysis::flush(PERFORMANCE_ANALYSIS_LABEL);
    }

    /**
     * This method checks if the 'forceRedirect' parameter is set in the route configuration. If it
     * is true, the request will be redirected to the given URL.
     *
     * The URL can be internal or external.
     * - When internal, the starting point will always be from the domain and doesn't the relative
     *   URLs.
     * - When external, it needs to start with a protocol (like http, https, ftp, etc.).
     *
     * @return void
     */
    private static function checkForceRedirect(): void
    {
        $forceRedirect = Parameters::getForceRedirect();

        if ($forceRedirect !== null) {
            Redirect::bypassUrlRoot()::to($forceRedirect);
        }

        PerformanceAnalysis::flush(PERFORMANCE_ANALYSIS_LABEL);
    }

    /**
     * After the route resolving, this method checks if there is a parent node and a child node, or
     * at least the child node, set to the route.
     *
     * @return void
     */
    private static function checkRouteExists(): void
    {
        Debug::setBacklog()::bypassGenericMessage();

        /**
         * If the route doesn't exist, it will return error 404.
         */
        if (
            Route::getParentNodeName() === null and
            Route::getChildNodeName() === null or
            Route::getChildNodeName() === null
        ) {
            self::return404();

        /**
         * If the route exists, two verifications are checked:
         */
        } else {
            /**
             * 1. If there are URL nodes remaining, it means that there are too many nodes than
             *    necessary, which means that it is an invalid route. Because of this, the framework
             *    will return error 404 to routes that have more URL nodes than necessary (except if
             *    the output is defined as 'file', because the file output uses the extra url nodes
             *    as file path).
             */
            if (!empty(Route::getUrlArray()) and Parameters::getOutput() !== 'file') {
                self::return404();
            }

            /**
             * 2. If it pass the first test, it will check if there are URL parameters set in the
             *    'urlParameters' child parameter. If there are required parameters, but no url
             *    nodes to fill these parameters, then it will return error 404. It only passes if
             *    all required URL parameters are filled.
             */
            $urlParameters = [];

            foreach(Parameters::getUrlParameters() as $tagName => $tagValue) {
                if (strpos($tagName, '?') === false and $tagValue === null) {
                    self::return404();
                }

                /**
                 * If it pass the test, the stored optional parameters are filtered to remove the
                 * '?' character from their keys.
                 */
                $urlParameters[ltrim($tagName, '?')] = $tagValue;
            }

            Parameters::setUrlParameters($urlParameters);
        }

        PerformanceAnalysis::flush(PERFORMANCE_ANALYSIS_LABEL);
    }

    /**
     * This method checks if an 'authTag' route parameter is set to the route. The auth tags means
     * that the route is protected by an authentication requisite. If the user doesn't have the
     * given auth tag in its session, then the framework will return an authentication fail error.
     * For more information, check the galastri\modules\Authentication class.
     *
     * The authentication fail can return a JSON data or, if there is an 'authFailRedirect' route
     * parameter set in the route configuration, it will redirect the request to the given URL.
     *
     * @return void
     */
    private static function checkAuthentication(): void
    {
        if ($authTag = Parameters::getAuthTag()) {
            if (Authentication::validate($authTag) === false) {
                self::returnAuthFail();
            }
        }
    }

    /**
     * This method checks if there is a route controller set to the route.
     *
     * It do several verifications to define a valid route controller and if the output requires a
     * controller to work.
     *
     * @return void
     */
    private static function checkController(): void
    {
        Debug::setBacklog();

        /**
         * First, it defines the route controller name. If there is a specific controller set with
         * the 'controller' parent parameter in the route configuration, then it is set.
         *
         * However, if there is no specific controller defined, it sets the controller name based on
         * a base namespace (it can be the default namespace app/controller or a namespace defined
         * with the 'namespace' route parameter) and the namespace that follows the URL until the
         * parent node. The parent node is the controller class itself, and all the url nodes before
         * forms the namespace.
         */
        $controllerNamespace = Route::getControllerNamespace();

        if ($nodeController = Parameters::getController()) {
            $routeControllerName = $nodeController;
        } else {
            $baseNodeNamespace = Parameters::getNamespace() ?: self::DEFAULT_NODE_NAMESPACE;
            $routeControllerName = $baseNodeNamespace . implode($controllerNamespace);
        }

        /**
         * Checks if there is an output set in the route configuration. If there is, checks if the
         * defined output requires a controller to work.
         */
        if (Parameters::getOutput() !== null) {
            self::$controllerIsRequired = self::{Parameters::getOutput().'RequiresController'}();
        }

        /**
         * Next, it checks if the class exists.
         *
         * if the class exists, then its name is set in the $routeControllerName property and the
         * next method called is the checkControllerExtendsCore.
         */
        if (class_exists($routeControllerName)) {
            self::$routeControllerName = $routeControllerName;

            PerformanceAnalysis::flush(PERFORMANCE_ANALYSIS_LABEL);

            self::checkControllerExtendsCore();

        } else {
            /**
             * However, when the route controller doesn't exists, if the given output do not
             * requires a controller to work, it will jump to the checkOutputIsSet method and no
             * error will occur.
             */
            if (self::$controllerIsRequired === false) {
                self::checkOutputIsSet();
                return;
            }

            /**
             * However, if it requires a controller, then an exception is thrown.
             */
            $workingController = explode('\\', $routeControllerName);

            $notFoundClass = str_replace('\\', '', array_pop($workingController));
            $notFoundNamespace = implode('/', $workingController);

            throw new Exception(self::CONTROLLER_NOT_FOUND[1], self::CONTROLLER_NOT_FOUND[0], [$routeControllerName, $notFoundClass, $notFoundNamespace]);
        }
    }

    /**
     * This method will only be executed if the route controller exists.
     *
     * It checks if the route controller inherits the core controller class. If it isn't, an
     * exception will be thrown.
     *
     * @return void
     */
    private static function checkControllerExtendsCore(): void
    {
        Debug::setBacklog();

        if (is_subclass_of(self::$routeControllerName, self::CORE_CONTROLLER) === false) {
            throw new Exception(self::CONTROLLER_DOESNT_EXTENDS_CORE[1], self::CONTROLLER_DOESNT_EXTENDS_CORE[0], [self::$routeControllerName]);
        }

        PerformanceAnalysis::flush(PERFORMANCE_ANALYSIS_LABEL);

        self::checkControllerMethod();
    }

    /**
     * This method will only be executed if the route controller exists.
     *
     * It  checks if there is a method in the route controller that matches the name of the child
     * node name.
     *
     * @return void
     */
    private static function checkControllerMethod(): void
    {
        Debug::setBacklog();

        $method = Route::getChildNodeName();

        /**
         * If the method exists, then the route controller will be called. But before this, the
         * framework checks if there is a 'request' child parameter defined in the route
         * configuration. The request parameter sets a second method to be called in the route
         * controller, so, it is needed to check if this second method exists.
         */
        if (method_exists(self::$routeControllerName, $method)) {
            $request = Parameters::getRequest();

            /**
             * If the request method is set, but doesn't exist in the route controller, an exception
             * is thrown.
             */
            if (!empty($request)) {
                if (!method_exists(self::$routeControllerName, $request)){
                    throw new Exception(self::CONTROLLER_METHOD_NOT_FOUND[1], self::CONTROLLER_METHOD_NOT_FOUND[0], [self::$routeControllerName, $request]);
                }
            }

            PerformanceAnalysis::flush(PERFORMANCE_ANALYSIS_LABEL);

            self::callController();

        /**
         * If the method doesn't exist in the route controller, an exception is thrown.
         */
        } else {
            if (self::$controllerIsRequired) {
                throw new Exception(self::CONTROLLER_METHOD_NOT_FOUND[1], self::CONTROLLER_METHOD_NOT_FOUND[0], [self::$routeControllerName, $method]);
            } else {
                self::checkOutputIsSet();
            }
        }
    }

    /**
     * This method creates an instance of the route controller. The construct method of the core
     * controller is called and will do a series of method calls in these classes. More information
     * in the galastri\core\Controller class.
     *
     * After the instance creation, the method checkOutputIsSet is called.
     *
     * @return void
     */
    private static function callController(): void
    {
        Debug::setBacklog();

        self::$routeController = new self::$routeControllerName();

        PerformanceAnalysis::flush(PERFORMANCE_ANALYSIS_LABEL);

        self::checkOutputIsSet();
    }

    /**
     * This method check if an output is set. If it is not, then an exception is thrown.
     *
     * @return void
     */
    private static function checkOutputIsSet(): void
    {
        Debug::setBacklog();

        if (Parameters::getOutput() === null) {
            throw new Exception(self::UNDEFINED_OUTPUT[1], self::UNDEFINED_OUTPUT[0]);
        }

        PerformanceAnalysis::flush(PERFORMANCE_ANALYSIS_LABEL);

        self::callOutput();
    }

    /**
     * After all these methods, then the main output method is called.
     *
     * @return void
     */
    private static function callOutput(): void
    {
        Debug::setBacklog();

        $output = Parameters::getOutput();

        self::$output();

        PerformanceAnalysis::flush(PERFORMANCE_ANALYSIS_LABEL);
    }

    /**
     * This method is called when an error 404 need to be returned.
     *
     * @return void
     */
    private function return404()
    {
        Debug::setBacklog()::bypassGenericMessage();

        /**
         * It checks if there is a 'notFoundRedirect' route parameter defined in the route
         * configuration. When it doesn't exist, then an exception is thrown to return a JSON data
         * with the 404 error.
         */
        if (Parameters::getNotFoundRedirect() === null or Parameters::getOutput() === 'json' or Parameters::getOutput() === 'text'  or Parameters::getOutput() === null) {
            header("HTTP/1.0 404 Not Found");
            throw new Exception(self::ERROR_404[1], self::ERROR_404[0]);

        /**
         * However, if there is a 'notFoundRedirect' route parameter defined, then the request is
         * redirected to the given URL.
         */
        } else {
            PerformanceAnalysis::flush(PERFORMANCE_ANALYSIS_LABEL);
            Redirect::to(Parameters::getNotFoundRedirect());
        }
    }

    /**
     * This method is called when an authentication fails.
     *
     * @return void
     */
    private function returnAuthFail()
    {
        Debug::setBacklog()::bypassGenericMessage();

        /**
         * It checks if there is a 'authFailredirect' route parameter defined in the route
         * configuration. When it doesn't exist, then an exception is thrown to return a JSON data
         * with the error message.
         */
        if (Parameters::getAuthFailRedirect() === null or Parameters::getOutput() === 'json' or Parameters::getOutput() === 'text'  or Parameters::getOutput() === null) {
            throw new Exception(Parameters::getAuthFailMessage(), self::DEFAULT_AUTH_FAIL_MESSAGE[0]);

        /**
         * However, if there is a 'authFailredirect' route parameter defined, then the request is
         * redirected to the given URL.
         */
        } else {
            PerformanceAnalysis::flush(PERFORMANCE_ANALYSIS_LABEL);
            Redirect::to(Parameters::getAuthFailRedirect());
        }
    }
}
