<?php

namespace galastri\core;

use \galastri\core\Debug;
use \galastri\extensions\Exception;
use \galastri\modules\Toolbox;
use \galastri\modules\PerformanceAnalysis;

/**
 * This class resolves the URL routing.
 *
 * Every routing configuration is done in \app\config\routes.php, which uses a nested multiarray,
 * which every key is a node. Every node can have children nodes, which represents the continuity of
 * the route, in a directory-like structure.
 *
 * Example:
 *
 *      [
 *          '/' => [
 *              // Means the URL: mydomain.com
 *              // Index parameters.
 *
 *              '/node-a' => [
 *                  // Means the URL: mydomain.com/node-a
 *                  // Inherits Index node parameters and can overwrites it with
 *                  // its own parameters.
 *
 *                  'node-ab' => [
 *                      // Means the URL: mydomain.com/node-a/node-ab
 *                      // Inherits Index and node-a parameters and can
 *                      // overwrites it with its own parameters.
 *                  ],
 *              ],
 *              '/node-b' => [
 *                  // Means the URL: mydomain.com/node-b
 *                  // Inherits Index node parameters and can overwrites it with
 *                  // its own parameters.
 *              ],
 *          ],
 *      ];
 *
 * In the URL, each slash bar (/) represents a node and every subsequent slash bar represents its
 * children. So, mydomain.com/node-a/node-ab and so on. Every child node can be parent for other
 * children and this is the way the URL routing is configured.
 */
final class Route
{
    const INVALID_PARAM_TYPE = ['INVALID_PARAM_TYPE', "Invalid parameter configuration. Parameter '%s' needs to be '%s'. '%s' given."];
    const REQUEST_METHOD_STARTS_WITH_AT = ['REQUEST_METHOD_STARTS_WITH_AT', "Request method '%s' need to start with @ as the first character"];
    const INVALID_REQUEST_METHOD_NAME = ['INVALID_REQUEST_METHOD_NAME', "Request method '%s' has an invalid name."];

    /**
     * Stores the URL nodes in array format which will be worked to extract the parent's parameters,
     * define the node that will be called, its child and local parameters.
     *
     * @var array
     */
    private static array $urlWorkingArray;

    /**
     * Stores the parent node's parameters, including child nodes and child nodes that are parents
     * too.
     *
     * @var array
     */
    private static array $nodeWorkingArray = [];

    /**
     * After all process to define the parent node, there are more nodes after that. This remaining
     * nodes will be worked to define the child node's name its parameters and if there are
     * additional url parameters.
     *
     * @var array
     */
    private static array $remainingUrlNodes = [];

    /**
     * Stores the parent node name in the given URL. When null means that no parent node was found
     * in the routing configuration file.
     *
     * @var null|string
     */
    private static ?string $parentNodeName = null;

    /**
     * Stores parent nodes specific parameters.
     *
     * @key null|string controller                  Defines custom controller to the node and its
     *                                              children, instead the default \app\controller.
     * 
     * @var array
     */
    private static array $parentNodeParam = [
        'controller' => null,
    ];

    const PARENT_NODE_PARAM_VALID_TYPES = [
        'controller' => ['NULL', 'string'],
    ];

    /**
     * Stores the namespace in case of the parent node be a controller.
     *
     * @var array
     */
    private static array $controllerNamespace = [];

    /**
     * When the route parameter 'namespace' is found in the parent's node, it is set to true
     * temporarily and all data stored in $controllerNamespace attribute is resetted.
     *
     * @var bool
     */
    private static bool $resetNamespace = false;

    /**
     * Stores the child node name in the given URL. When null means that no child node was found in
     * the routing configuration file.
     *
     * @var null|string
     */
    private static ?string $childNodeName = null;

    /**
     * Stores child nodes specific parameters.
     *
     * @key bool fileDownloadable                   Works only with File solvers. Defines if the
     *                                              file is downloadable.
     *
     * @key null|string fileBaseFolder              Works only with File solvers. Defines a custom
     *                                              folder where the file is located.
     *
     * @key null|string viewFilePath                Works only with View solvers. Defines a custom
     *                                              view file instead the default.
     *
     * @key null|array requestMethod                Points to an internal method that will be called
     *                                              based on the request method. The key of the
     *                                              array needs to have the name of request method
     *                                              (POST, GET, PUT, etc..) and its value needs to
     *                                              be the method to be called, always starting with
     *                                              @, for better identification.
     * @var array
     */
    private static array $childNodeParam = [
        'fileDownloadable' => false,
        'fileBaseFolder' => null,
        'viewFilePath' => null,
        'requestMethod' => null,
    ];

    const CHILD_NODE_PARAM_VALID_TYPES = [
        'fileDownloadable' => ['boolean'],
        'fileBaseFolder' => ['NULL', 'string'],
        'viewFilePath' => ['NULL', 'string'],
        'requestMethod' => ['NULL', 'array'],
    ];

    /**
     * Stores the extra parameters of the URL that is defined in the route configuration.
     *
     * @var array                                   Parameters tags and its values.
     */
    private static array $urlParam = [];

    /**
     * Stores the tag names of dynamic nodes and its values in the URL. Dynamic nodes are like url
     * parameters, but in reverse position: url parameters are after the child nodes, while the
     * dynamic nodes goes before. Dynamic nodes also calls for dynamic controllers when required.
     *
     * @var array
     */
    private static array $dynamicNodeValues = [];

    /**
     * Stores route parameters, inherited by parents nodes.
     *
     * @key null|string projectTitle                Defines a custom app title instead of the
     *                                              default defined in the \app\config\project.php
     *                                              file.
     *
     * @key null|string authFailRedirect            Defines a URL, path or url alias to redirect the
     *                                              users that requests paths that needs
     *                                              authorization to access but doesn't have.
     *
     * @key null|string authTag                     Defines a tag string that the user session needs
     *                                              to have access to the node and its children.
     *                                              When null, defines that the node or child
     *                                              doesn't need that authorization.
     *
     * @key null|int browserCache                   Defines a cache time to the node and its
     *                                              children (in seconds). When null, the node won't
     *                                              be cached.
     *
     * @key null|string namespace                   Defines custom namespace for controllers to the
     *                                              node and its children, instead the default
     *                                              \app\controller.
     *
     * @key null|string viewBaseFolder              Works only with View solvers. Defines a custom
     *                                              folder where views are located.
     *
     * @key null|string notFoundRedirect            Defines a custom URL, path or URL alias when a
     *                                              file or URL path is not found (error 404).
     *
     * @key bool offline                            Defines that the node and its children is
     *                                              offline. No scripts are executed when it is
     *                                              defined as true. Useful when doing maintenance.
     *
     * @key null|string pageTitle                   Defines a static page title to the node and its
     *                                              children. It can be changed in controller if the
     *                                              page title needs to be dynamic.
     *
     * @key string solver                           Defines which solver will be used in the node
     *                                              and its children. A solver is a trait that will
     *                                              return the data into a type. The currently
     *                                              solvers are: - File: returns a file; - View:
     *                                              returns a HTML; - Json: returns data in json
     *                                              format; - Text: returns data in plain text.
     *
     * @key null|string viewTemplateFile            Works only with View solvers. Defines the
     *                                              template base file where the view will be
     *                                              printed. This template base file can have
     *                                              template parts, defined in the parameter
     *                                              'viewTemplateParts' which can store the path of
     *                                              other parts that can be imported inside the base
     *                                              template.
     *
     * @key null|string forceRedirect               Force the request to be redirected to a URL,
     *                                              path or URL alias when the node or its children
     *                                              is accessed.
     *
     * @key array defaultmessage                    Defines a custom set of defaultmessage instead
     *                                              of the ones defined in \app\config\project.php
     *                                              file.
     *
     * @var array
     */
    private static array $routeParam = [
        'offline' => GALASTRI_PROJECT['offline'],
        'projectTitle' => GALASTRI_PROJECT['projectTitle'],
        'pageTitle' => null,
        'authTag' => null,
        'authFailRedirect' => null,
        'forceRedirect' => null,
        'namespace' => null,
        'notFoundRedirect' => GALASTRI_PROJECT['notFoundRedirect'],
        'solver' => null,
        'browserCache' => null,
        'viewTemplateFile' => GALASTRI_PROJECT['viewTemplateFile'],
        'viewBaseFolder' => null,
        'defaultmessage' => GALASTRI_PROJECT['defaultmessage'],
    ];

    const ROUTE_PARAM_VALID_TYPES = [
        'offline' => ['boolean'],
        'projectTitle' => ['NULL', 'string'],
        'pageTitle' => ['NULL', 'string'],
        'authTag' => ['NULL', 'string'],
        'authFailRedirect' => ['NULL', 'string'],
        'forceRedirect' => ['NULL', 'string'],
        'namespace' => ['NULL', 'string'],
        'notFoundRedirect' => ['NULL', 'string'],
        'solver' => ['string'],
        'browserCache' => ['NULL', 'integer'],
        'viewTemplateFile' => ['NULL', 'string'],
        'viewBaseFolder' => ['NULL', 'string'],
        'defaultmessage' => ['array'],
    ];



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
     * Execute a chain of methods that resolves the URL string, searching for nodes in
     * \app\config\routes.php that matches the requested URL and storing its parameters.
     *
     * @return void
     */
    public static function resolve(): void
    {
        try {
            self::prepareUrlArray();
            self::resolveRouteNodes(GALASTRI_ROUTES);
            self::defineChildNode();
            self::resolveChildNodeParam();
            self::resolveChildNodeParamRequestMethod();
            self::resolveUrlParam();

            if (count(self::$controllerNamespace) > 1) {
                array_shift(self::$controllerNamespace);
            }

            // vardump(
            //     /*0*/ self::$parentNodeName,
            //     /*1*/ self::$parentNodeParam,
            //     /*2*/ self::$controllerNamespace,
            //     /*3*/ self::$childNodeName,
            //     /*4*/ self::$childNodeParam,
            //     /*5*/ self::$urlParam,
            //     /*6*/ self::$dynamicNodeValues,
            //     /*7*/ self::$routeParam
            // );
        } catch (Exception $e) {
            PerformanceAnalysis::store(PERFORMANCE_ANALYSIS_LABEL);
            Debug::setError($e->getMessage(), $e->getCode(), $e->getData())::print();
        } catch (\Error | \Throwable | \Exception | \TypeError $e) {
            Debug::setBacklog($e->getTrace());
            Debug::setError($e->getMessage(), $e->getCode())::print();
        }
    }

    /**
     * Prepare an array with the URL, which will be used as nodes.
     *
     * Everything in URL that is between the domain and the beginning of the querystring (? char),
     * will be divided into parts, inside an array.
     *
     * Example: mydomain.com/foo/bar?val1=baz
     *
     * - The domain and querystring will be ignored. Only the /foo/bar will be get as a string.
     *
     * - The string '/foo/bar' will be divided into an array('foo', 'bar')
     *
     * - Every key value will receive a '/' char in the beginning of their value like this:
     *   array('/foo', '/bar')
     *
     * The result array is stored inside $urlWorkingArray attribute.
     *
     * @return void
     */
    private static function prepareUrlArray(): void
    {
        /**
         * The URL root that will be controlled by the framework.
         */
        $bootstrapPath = ltrim(GALASTRI_PROJECT['urlRoot'], '/');

        $urlWorkingArray = explode('?', str_replace($bootstrapPath, '', $_SERVER['REQUEST_URI']));
        $urlWorkingArray = explode('/', $urlWorkingArray[0]);

        if (empty($urlWorkingArray[1])) {
            array_shift($urlWorkingArray);
        }

        foreach ($urlWorkingArray as &$value) {
            $value = '/' . $value;
        }
        unset($value);

        self::$urlWorkingArray = $urlWorkingArray;

        PerformanceAnalysis::flush(PERFORMANCE_ANALYSIS_LABEL);
    }

    /**
     * This method gets te configuration file \app\config\routes.php and search for a key that
     * matches with the URL nodes stored in the $urlWorkingArray.
     *
     * It starts with a foreach, looking if de first URL node exists in the configuration file. If
     * it exists, then it execute a closure function in the variable $resolveNode.
     *
     * This function:
     * 1. Removes the current key off the $urlWorkingArray
     * 2. Stores the node parameters in $nodeWorkingArray attribute
     * 3. Add the key label in the $controllerNamespace attribute
     * 4. Calls the method again to repeat the process.
     *
     * However, if there is no key that matches with the URL node, then he searchs if there is a
     * dynamic node there. Dynamic nodes always starts with '/?' and its label doesn't have to
     * matche the URL node.
     *
     * If there is a dynamic node, then its label and value is stored in the $dynamicNodeValues
     * attribute and then it execute a closure function in the variable $resolveNode explained
     * above.
     *
     * If there ir no node nor dynamic node, then this means that the URL node doesn't exist, so the
     * $parentNodeName is set as null.
     *
     * After all the tests, all the remaining URL nodes is stored in the $remainingUrlNodes
     * attribute.
     *
     * @param  array $routeNodes                    Multidimensional array with the configuration of
     *                                              the project routing.
     * @return void
     */
    private static function resolveRouteNodes(array $routeNodes): void
    {
        $found = false;

        $resolveNode = function (array $routeNodes, string $key) {
            array_shift(self::$urlWorkingArray);
            self::$nodeWorkingArray = $routeNodes[$key];
            self::resolveParentNodeParam($routeNodes[$key]);
            self::resolveRouteParam($routeNodes[$key]);
            self::addControllerNamespacePath($key);
            self::resolveRouteNodes($routeNodes[$key]);
            return true;
        };

        foreach (self::$urlWorkingArray as $urlNode) {
            if (isset($routeNodes[$urlNode])) {
                $found = $resolveNode($routeNodes, $urlNode);
                break;
            }

            if (!$found) {
                if ($dynamicNode = Toolbox::arrayKeySearch('/?', $routeNodes, MATCH_START)) {
                    self::storeDynamicNode(key($dynamicNode), $urlNode);
                    $found = $resolveNode($routeNodes, key($dynamicNode));
                    break;
                }
            }

            if (!$found) {
                self::$parentNodeName = null;
            }
        }

        self::$remainingUrlNodes = self::$urlWorkingArray;
        PerformanceAnalysis::flush(PERFORMANCE_ANALYSIS_LABEL);
    }

    /**
     * This method resolves parameters that are exclusive to the parent node and that aren't
     * inherited by the other nodes. The parent node parameter belongs only to the parent node and
     * is not trasmitted to other parent node.
     *
     * It also validates if the type of the values of the keys are allowed.
     *
     * @param  array $nodeFound                     The parent node's parameters
     *
     * @return void
     */
    private static function resolveParentNodeParam(array $nodeFound): void
    {


        foreach (self::$parentNodeParam as $param => &$value) {
            if (array_key_exists($param, $nodeFound)) {
                $value = self::hasValidType($nodeFound, $param, self::PARENT_NODE_PARAM_VALID_TYPES);
            } else {
                $value = null;
            }
        }
        unset($value);
    }

    /**
     * hasValidType
     *
     * @param  mixed $nodeFound
     * @param  mixed $param
     * 
     * @return mixed
     */
    private static function hasValidType(array $nodeFound, string $param, array $validTypes) // : mixed
    {
        Debug::setBacklog();

        foreach ($validTypes[$param] as $allowedType) {
            if (gettype($nodeFound[$param]) === $allowedType) {
                return $nodeFound[$param];
            }
        }

        throw new Exception(self::INVALID_PARAM_TYPE[1], self::INVALID_PARAM_TYPE[0], [$param, implode('|', $validTypes[$param]), gettype($nodeFound[$param])]);
    }

    /**
     * Searchs in the found node if there is any parent route parameter. This parameters are
     * inherited by the subsequent nodes. If there is any, its value is overwrited by the new value.
     *
     * NOTE: When the route parameter 'namespace' exists, a new namespace needs to be set as
     * starting point from the node. This means that all the stored $controllerNamespace attribute
     * needs to restart. That is why there is a test to check if the parameter 'namespaces' exists.
     *
     * @param  array $nodeFound                     Multidimensional array with the node parameters
     *                                              found in the routing configuration.
     *
     * @return void
     */
    private static function resolveRouteParam(array $nodeFound): void
    {
        foreach (self::$routeParam as $param => &$value) {
            if (array_key_exists($param, $nodeFound)) {
                if ($param === 'namespace') {
                    self::$resetNamespace = true;
                }

                $value = self::hasValidType($nodeFound, $param, self::ROUTE_PARAM_VALID_TYPES);
            }
        }
        unset($value);
    }

    /**
     * Stores the parent node name to create a namespace path. It will be used when calling a
     * controller with the name of the node which its path is the same as the URL path.
     *
     * @param  string $parentNodeName               The parent node name that will be part of the
     *                                              namespace
     *
     * @return void
     */
    private static function addControllerNamespacePath(string $parentNodeName): void
    {
        $parentNodeName = Toolbox::trim($parentNodeName, '/', '?');
        $parentNodeName = $parentNodeName ?: 'index';
        $parentNodeName = Toolbox::convertCase($parentNodeName, PASCAL_CASE);

        self::$parentNodeName = $parentNodeName;

        if (self::$resetNamespace) {
            self::$controllerNamespace = [];
            self::$resetNamespace = false;
        }

        self::$controllerNamespace[] = '\\' . $parentNodeName;
    }

    /**
     * Stores the dynamic label tag found in routing configuration file and its value in the URL
     * node.
     *
     * @param  string $dynamicNodeTag               The key label of the dynamic node.
     *
     * @param  string $urlNode                      The value of the node in the URL.
     *
     * @return void
     */
    private static function storeDynamicNode(string $dynamicNodeTag, string $urlNode): void
    {
        self::$dynamicNodeValues[substr($dynamicNodeTag, 2)] = ltrim($urlNode, '/');
    }


    /**
     * Defines the name of the child node, based on the remaining URL nodes. If there are no
     * remaining URL nodes, then the child node name will be 'main'. If there are remaining URL
     * nodes, then the first node will be the name of the child node and that will be dropped from
     * the $remainingUrlNodes attribute.
     *
     * @return void
     */
    private static function defineChildNode(): void
    {
        if (empty(self::$remainingUrlNodes)) {
            self::$childNodeName = 'main';
        } else {
            self::$childNodeName = ltrim(self::$remainingUrlNodes[0], '/');
            array_shift(self::$remainingUrlNodes);
        }

        PerformanceAnalysis::flush(PERFORMANCE_ANALYSIS_LABEL);
    }

    /**
     * This method searchs for a parent node parameter that matches with the name of the child
     * parameter that is the last of the chain (in short, the node that starts with @).
     *
     * If it exists, then the parameters are stored. If not, this means that there is no child node
     * with that name configured in the routing configuration file, so, the child node name will be
     * set as null.
     *
     * @return void
     */
    private static function resolveChildNodeParam(): void
    {
        $found = false;

        foreach (self::$nodeWorkingArray as $param => $value) {
            if ($param === '@' . self::$childNodeName) {
                $found = true;
                $childNodeParam = $value;
                break;
            }
        }

        if ($found) {
            foreach (self::$childNodeParam as $param => $value) {
                if (array_key_exists($param, $childNodeParam)) {
                    self::$childNodeParam[$param] = self::hasValidType($childNodeParam, $param, self::CHILD_NODE_PARAM_VALID_TYPES);
                }
            }

            self::resolveRouteParam($childNodeParam);
        } else {
            self::$childNodeName = null;
        }

        PerformanceAnalysis::flush(PERFORMANCE_ANALYSIS_LABEL);
    }

    /**
     * This method resolve the requestMethod parameter in child node. This parameter is an array
     * that points a method to be executed based on the method used in the request (like POST, GET,
     * etc.).
     *
     * The method gets the request method received by the server and checks if the requestMethod
     * stored in the $childNodeParam attribute.
     *
     * If it exists it compares the actual request method to the methods defined in the key of the
     * parameter. If it is equal, then the value is validated as a valid method name and, as
     * everything ok, it is stored. An invalid method name will throw an exception.
     *
     * If not, it will search until the end. If it doesn't exist, then the parameter requestMethod
     * is set as null.
     *
     * @return void
     */
    private static function resolveChildNodeParamRequestMethod(): void
    {
        Debug::setBacklog();

        $serverRequestMethod = Toolbox::lowerCase($_SERVER['REQUEST_METHOD']);

        if (self::$childNodeParam['requestMethod'] !== null) {
            foreach (self::$childNodeParam['requestMethod'] as $key => $value) {
                $key = Toolbox::lowerCase($key);

                if ($key === $serverRequestMethod) {
                    if (substr($value, 0, 1) !== '@') {
                        throw new Exception(self::REQUEST_METHOD_STARTS_WITH_AT[1], self::REQUEST_METHOD_STARTS_WITH_AT[0], [$value]);
                    } else {
                        $value = substr($value, 1, strlen($value));

                        preg_match_all('/^[0-9]|[^a-zA-Z0-9_]*/', $value, $checkValue);

                        if (implode($checkValue[0]) !== '') {
                            throw new Exception(self::INVALID_REQUEST_METHOD_NAME[1], self::INVALID_REQUEST_METHOD_NAME[0], [$value]);
                        } else {
                            self::$childNodeParam['requestMethod'] = [
                                $key => Toolbox::convertCase($value, CAMEL_CASE),
                            ];
                        }
                    }
                    break;
                } else {
                    self::$childNodeParam['requestMethod'] = null;
                }
            }
        }

        PerformanceAnalysis::flush(PERFORMANCE_ANALYSIS_LABEL);
    }

    /**
     * Gets all the remaining URL nodes and stores in the $urlParam attributes since there is a
     * 'parameters' parameter configured in the routing configuration.
     *
     * There are two ways to configure parameters in the routing configuration file:
     *
     *      'parameters' => '/tag1/tag2',
     *
     *      Or
     *
     *      'parameters' => ['tag1', 'tag2'],
     *
     * The tag name will be stored as a key label on the $urlParam attribute, while its value in the
     * url will be stored as value of this key.
     *
     * @return void
     */
    private static function resolveUrlParam(): void
    {
        $childNodeParam = self::$childNodeParam;

        if (isset($childNodeParam['parameters'])) {
            $urlParam = $childNodeParam['parameters'];

            if (gettype($urlParam) === 'string') {
                $urlParam = explode('/', $urlParam);
                if (empty($urlParam[0])) {
                    array_shift($urlParam);
                }
            }

            foreach (self::$remainingUrlNodes as $key => $value) {
                $keyLabel = $urlParam[$key];
                self::$urlParam[$keyLabel] = ltrim($value, '/');
            }
        }

        PerformanceAnalysis::flush(PERFORMANCE_ANALYSIS_LABEL);
    }

    /**
     * Return the $urlParam attribute.
     *
     * @return array
     */
    public static function getUrlParam(): array
    {
        return self::$urlParam;
    }

    /**
     * Return the $parentNodeName attribute.
     *
     * @return null|string
     */
    public static function getParentNodeName(): ?string
    {
        return self::$parentNodeName;
    }

    /**
     * Return the $nodeWorkingArray attribute.
     *
     * @param  null|string $key                     Specify which key will be returned.
     * 
     * @return mixed
     */
    public static function getParentNodeParam(?string $key = null) // : mixed
    {
        return $key === null ? self::$parentNodeParam : self::$parentNodeParam[$key];
    }

    /**
     * Return the $controllerNamespace attribute.
     *
     * @return array
     */
    public static function getControllerNamespace(): array
    {
        return self::$controllerNamespace;
    }

    /**
     * Return the $childNodeName attribute.
     *
     * @return null|string
     */
    public static function getChildNodeName(): ?string
    {
        return self::$childNodeName;
    }

    /**
     * Return the $childNodeParam attribute.
     *
     * @param  null|string $key                     Specify which key will be returned.
     * 
     * @return mixed
     */
    public static function getChildNodeParam(?string $key = null) // : mixed
    {
        return $key === null ? self::$childNodeParam : self::$childNodeParam[$key];
    }

    /**
     * Return the $dynamicNodeValues attribute.
     *
     * @return array|string
     */
    public static function getDynamicNodeValue(?string $key = null) // : array|string
    {
        return $key === null ? self::$dynamicNodeValues : self::$dynamicNodeValues[$key];
    }

    /**
     * Return the $routeParam attribute.
     *
     * @param  null|string $key                     Specify which key will be returned.
     *
     * @return mixed
     */
    public static function getRouteParam(?string $key = null) // : mixed
    {
        return $key === null ? self::$routeParam : self::$routeParam[$key];
    }
}
