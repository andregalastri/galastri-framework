<?php

namespace galastri\core;

use galastri\core\Parameters;
use galastri\extensions\Exception;
use galastri\modules\types\TypeString;
use galastri\modules\PerformanceAnalysis;

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
final class Route implements \Language
{
    /**
     * Stores route parameters, inherited by parents nodes.
     *
     * @var array
     */
    private static array $routeParameters;

    /**
     * Stores the URL nodes in array format which will be worked to extract the parent's parameters,
     * define the node that will be called, its child and local parameters.
     *
     * @var array
     */
    private static array $urlArray;

    /**
     * Stores the parent node's parameters, including child nodes and child nodes that are parents
     * too.
     *
     * @var array
     */
    private static array $childArray;

    /**
     * Stores the tag names of dynamic nodes and its values in the URL. Dynamic nodes are like url
     * parameters, but in reverse position: url parameters are after the child nodes, while the
     * dynamic nodes goes before. Dynamic nodes also calls for dynamic controllers when required.
     *
     * @var array
     */
    private static array $dynamicNodes = [];

    /**
     * Stores the parent node name in the given URL. When null means that no parent node was found
     * in the routing configuration file.
     *
     * @var null|string
     */
    private static ?string $parentNodeName;

    /**
     * Stores parent nodes specific parameters.
     *
     * @key null|string controller                  Defines custom controller to the node and its
     *                                              children, instead the default \app\controller.
     *
     * @var array
     */
    private static array $parentParameters = [
        'controller' => null,
    ];

    /**
     * Stores the namespace in case of the parent node be a controller.
     *
     * @var array
     */
    private static array $controllerNamespace;

    /**
     * When the route parameter 'namespace' is found in the parent's node, it is set to true
     * temporarily and all data stored in $controllerNamespace property is resetted.
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
     * @key bool fileDownloadable                   Works only with File output. Defines if the
     *                                              file is downloadable.
     *
     * @key null|string fileBaseFolder              Works only with File output. Defines a custom
     *                                              folder where the file is located.
     *
     * @key null|string viewFilePath                Works only with View output. Defines a custom
     *                                              view file instead the default.
     *
     * @key array|null|string request               Points to an internal method that will be called
     *                                              based on the request method. The key of the
     *                                              array needs to have the name of request method
     *                                              (POST, GET, PUT, etc..) and its value needs to
     *                                              be the method to be called, always starting with
     *                                              @, for better identification.
     *
     * @key null|string parameters                  Stores the url nodes that comes after the child
     *                                              node.
     *
     * @var array
     */
    private static array $childParameters = [
        'fileDownloadable' => false,
        'fileBaseFolder' => null,
        'viewFilePath' => null,
        'request' => null,
        'parameters' => null,
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
        self::setup();
        self::prepareUrlArray();
        self::resolveRoutes($GLOBALS['GALASTRI_ROUTES']);

        unset($GLOBALS['GALASTRI_ROUTES']);

        self::resolveChildNode();

        if (count(self::$controllerNamespace) > 1) {
            array_shift(self::$controllerNamespace);
        }

        self::validateAndStoreParameters();

        unset($GLOBALS['GALASTRI_PROJECT']);
    }

    /**
     * Stores route parameters, inherited by parents nodes.
     *
     * @key bool offline                            Defines that the node and its children is
     *                                              offline. No scripts are executed when it is
     *                                              defined as true. Useful when doing maintenance.
     *
     * @key null|string projectTitle                Defines a custom app title instead of the
     *                                              default defined in the \app\config\project.php
     *                                              file.
     *
     * @key null|string pageTitle                   Defines a static page title to the node and its
     *                                              children. It can be changed in controller if the
     *                                              page title needs to be dynamic.
     *
     * @key null|string authTag                     Defines a tag string that the user session needs
     *                                              to have access to the node and its children.
     *                                              When null, defines that the node or child
     *                                              doesn't need that authorization.
     *
     * @key null|string authFailRedirect            Defines a URL, path or url alias to redirect the
     *                                              users that requests paths that needs
     *                                              authorization to access but doesn't have.
     *
     * @key null|string forceRedirect               Force the request to be redirected to a URL,
     *                                              path or URL alias when the node or its children
     *                                              is accessed.
     *
     * @key null|string namespace                   Defines custom namespace for controllers to the
     *                                              node and its children, instead the default
     *                                              \app\controller.
     *
     * @key null|string notFoundRedirect            Defines a custom URL, path or URL alias when a
     *                                              file or URL path is not found (error 404).
     *
     * @key string output                           Defines which output will be used in the node
     *                                              and its children. A output is a trait that will
     *                                              return the data into a type. The currently
     *                                              outputs are: - File: returns a file; - View:
     *                                              returns a HTML; - Json: returns data in json
     *                                              format; - Text: returns data in plain text.
     *
     * @key null|int browserCache                   Defines a cache time to the node and its
     *                                              children (in seconds). When null, the node won't
     *                                              be cached.
     *
     * @key null|string viewTemplateFile            Works only with View output. Defines the
     *                                              template base file where the view will be
     *                                              printed. This template base file can have
     *                                              template parts, defined in the parameter
     *                                              'viewTemplateParts' which can store the path of
     *                                              other parts that can be imported inside the base
     *                                              template.
     *
     * @key null|string viewBaseFolder              Works only with View output. Defines a custom
     *                                              folder where views are located.
     *
     * @key array offlineMessage                    Defines a custom offline message to the node and
     *                                              its children.
     *
     * @key array authFailMessage                   Defines a custom authentication fail message to
     *                                              the node and its children.
     *
     * @key array permissionFailMessage             Defines a custom permission access restriction
     *                                              message to the node and its children.
     *
     * @return void
     */
    private static function validateAndStoreParameters(): void
    {
        Parameters::setTimezone($GLOBALS['GALASTRI_PROJECT']['timezone'] ?? null);
        Parameters::setController(self::$parentParameters['controller']);

        Parameters::setOffline(self::$routeParameters['offline']);
        Parameters::setOfflineMessage(self::$routeParameters['offlineMessage']);
        Parameters::setForceRedirect(self::$routeParameters['forceRedirect']);
        Parameters::setOutput(self::$routeParameters['output']);
        Parameters::setNotFoundRedirect(self::$routeParameters['notFoundRedirect']);
        Parameters::setNamespace(self::$routeParameters['namespace']);
        Parameters::setProjectTitle(self::$routeParameters['projectTitle']);
        Parameters::setPageTitle(self::$routeParameters['pageTitle']);
        Parameters::setAuthTag(self::$routeParameters['authTag']);
        Parameters::setAuthFailRedirect(self::$routeParameters['authFailRedirect']);
        Parameters::setViewTemplateFile(self::$routeParameters['viewTemplateFile']);
        Parameters::setViewBaseFolder(self::$routeParameters['viewBaseFolder']);

        Parameters::setFileDownloadable(self::$childParameters['fileDownloadable']);
        Parameters::setFileBaseFolder(self::$childParameters['fileBaseFolder']);
        Parameters::setViewFilePath(self::$childParameters['viewFilePath']);
        Parameters::setRequest(self::$childParameters['request']);
        Parameters::setUrlParameters(self::$childParameters['parameters']);

        PerformanceAnalysis::flush(PERFORMANCE_ANALYSIS_LABEL);
    }

    /**
     * setup
     *
     * @return void
     */
    private static function setup(): void
    {
        self::$routeParameters = [
            'offline' => $GLOBALS['GALASTRI_PROJECT']['offline'],
            'projectTitle' => $GLOBALS['GALASTRI_PROJECT']['projectTitle'] ?? null,
            'pageTitle' => null,
            'authTag' => null,
            'authFailRedirect' => null,
            'forceRedirect' => null,
            'namespace' => null,
            'notFoundRedirect' => $GLOBALS['GALASTRI_PROJECT']['notFoundRedirect'] ?? null,
            'output' => null,
            'browserCache' => null,
            'viewTemplateFile' => $GLOBALS['GALASTRI_PROJECT']['viewTemplateFile'] ?? null,
            'viewBaseFolder' => null,
            'offlineMessage' => $GLOBALS['GALASTRI_PROJECT']['offlineMessage'] ?? null,
            'authFailMessage' => $GLOBALS['GALASTRI_PROJECT']['authFailMessage'] ?? null,
            'permissionFailMessage' => $GLOBALS['GALASTRI_PROJECT']['permissionFailMessage'] ?? null,
        ];
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
     * The result array is stored inside $urlArray property.
     *
     * @return void
     */
    private static function prepareUrlArray(): void
    {
        /**
         * The URL root that will be controlled by the framework.
         */
        Parameters::setUrlRoot($GLOBALS['GALASTRI_PROJECT']['urlRoot'] ?? null);

        $urlArray = explode('?', str_replace(Parameters::getUrlRoot(), '', $_SERVER['REQUEST_URI']));
        $urlArray = explode('/', $urlArray[0]);

        if (empty($urlArray[1])) {
            array_shift($urlArray);
        }

        foreach ($urlArray as &$urlNode) {
            $urlNode = '/' . $urlNode;
        }
        unset($urlNode);

        self::$urlArray = $urlArray;

        PerformanceAnalysis::flush(PERFORMANCE_ANALYSIS_LABEL);
    }

    /**
     * This method gets te configuration file \app\config\routes.php and search for a key that
     * matches with the URL nodes stored in the $urlArray.
     *
     * It starts with a foreach, looking if de first URL node exists in the configuration file. If
     * it exists, then it execute a closure function in the variable $resolveParentNode.
     *
     * This function:
     * 1. Removes the current key off the $urlArray
     * 2. Stores the node parameters in $childArray property
     * 3. Add the key label in the $controllerNamespace property
     * 4. Calls the method again to repeat the process.
     *
     * However, if there is no key that matches with the URL node, then he searchs if there is a
     * dynamic node there. Dynamic nodes always starts with '/?' and its label doesn't have to
     * matche the URL node.
     *
     * If there is a dynamic node, then its label and value is stored in the $dynamicNodes property
     * and then it execute a closure function in the variable $resolveParentNode explained above.
     *
     * If there ir no node nor dynamic node, then this means that the URL node doesn't exist, so the
     * $parentNodeName is set as null.
     *
     * @param  array $routeArray                    Multidimensional array with the configuration of
     *                                              the project routing.
     * @return void
     */
    private static function resolveRoutes(array $routeArray): void
    {
        $found = false;

        // vardump();
        foreach (self::$urlArray as $urlNode) {
            if (isset($routeArray[$urlNode])) {
                $found = true;
                self::resolveParentNode($routeArray, $urlNode);
                break;
            }

            if (!$found) {
                $dynamicTag = null;

                foreach (array_keys($routeArray) as $routeNode) {
                    if (strpos($routeNode, '/?') === 0) {
                        $dynamicTag = $routeNode;
                        break;
                    }
                }

                if ($dynamicTag) {
                    $found = true;

                    self::storeDynamicNode($dynamicTag, $urlNode);
                    self::resolveParentNode($routeArray, $dynamicTag);
                    break;
                }
            }

            if (!$found) {
                self::$parentNodeName = null;
            }
        }

        PerformanceAnalysis::flush(PERFORMANCE_ANALYSIS_LABEL);
    }

    /**
     * resolveParentNode
     *
     * @param  mixed $routeArray
     * @param  mixed $urlNode
     * @return void
     */
    private static function resolveParentNode(array $routeArray, string $urlNode)
    {
        array_shift(self::$urlArray);

        self::$childArray = $routeArray[$urlNode];
        self::resolveParentParameters($routeArray[$urlNode]);
        self::resolveRouteParameters($routeArray[$urlNode]);
        self::storeNamespace($urlNode);

        self::resolveRoutes($routeArray[$urlNode]);
    }

    /**
     * Stores the dynamic label tag found in routing configuration file and its value in the URL
     * node.
     *
     * @param  string $dynamicTag                   The key label of the dynamic node.
     *
     * @param  string $urlNode                      The value of the node in the URL.
     *
     * @return void
     */
    private static function storeDynamicNode(string $dynamicTag, string $urlNode): void
    {
        $dynamicTag = ltrim($dynamicTag, '/?');

        self::$dynamicNodes[$dynamicTag] = ltrim($urlNode, '/');
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
    private static function resolveParentParameters(array $nodeFound): void
    {
        foreach (self::$parentParameters as $parameter => $value) {
            self::$parentParameters[$parameter] = $nodeFound[$parameter] ?? null;
        }
    }

    /**
     * Searchs in the found node if there is any parent route parameter. This parameters are
     * inherited by the subsequent nodes. If there is any, its value is overwrited by the new value.
     *
     * NOTE: When the route parameter 'namespace' exists, a new namespace needs to be set as
     * starting point from the node. This means that all the stored $controllerNamespace property
     * needs to restart. That is why there is a test to check if the parameter 'namespaces' exists.
     *
     * @param  array $nodeFound                     Multidimensional array with the node parameters
     *                                              found in the routing configuration.
     *
     * @return void
     */
    private static function resolveRouteParameters(array $nodeFound): void
    {
        foreach (self::$routeParameters as $parameter => $value) {
            if (array_key_exists($parameter, $nodeFound)) {
                if ($parameter === 'namespace') {
                    self::$resetNamespace = true;
                }

                self::$routeParameters[$parameter] = $nodeFound[$parameter];
            }
        }
    }

    /**
     * Stores the parent node name to create a namespace path. It will be used when calling a
     * controller with the name of the node which its path is the same as the URL path.
     *
     * @param  string $urlNode                      The parent node name that will be part of the
     *                                              namespace
     *
     * @return void
     */
    private static function storeNamespace(string $urlNode): void
    {
        $urlNode = (new TypeString($urlNode))->trim('/', '?')->toPascalCase()->set(fn ($self) => $self->get() ?: 'Index')->get();


        self::$parentNodeName = $urlNode;

        if (self::$resetNamespace) {
            self::$controllerNamespace = [];
            self::$resetNamespace = false;
        }

        self::$controllerNamespace[] = '\\'.$urlNode;
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
    private static function resolveChildNode(): void
    {
        foreach (self::$urlArray as $key => $urlNode) {
            self::$urlArray[$key] = ltrim($urlNode, '/');
        }

        foreach ([self::$urlArray[0] ?? null, 'main'] as $urlNode) {
            if ($urlNode === null) {
                continue;
            }

            foreach (self::$childArray as $childParameterKey => $childParameterValue) {
                if (gettype($childParameterKey) !== 'string') {
                    throw new Exception(self::INVALID_KEY_PARAMETER_TYPE[1], self::INVALID_KEY_PARAMETER_TYPE[0]);
                }

                if ($childParameterKey === '@'.$urlNode) {
                    self::$childNodeName = (new TypeString($urlNode))->toCamelCase()->get();
                    if ((self::$urlArray[0] ?? null) === $urlNode) {
                        array_shift(self::$urlArray);
                    }

                    break 2;
                }
            }
        }

        self::resolveChildParameters($childParameterValue);
        self::resolveUrlParameters();
        self::resolveRequestMethod();
    }

    private static function resolveChildParameters(array $nodeFound): void
    {
        foreach (self::$childParameters as $parameter => $value) {
            if (array_key_exists($parameter, $nodeFound)) {
                self::$childParameters[$parameter] = $nodeFound[$parameter];
            }
        }
    }

    private static function resolveUrlParameters(): void
    {
        $urlParameters = self::$childParameters['parameters'];

        self::$childParameters['parameters'] = [];

        $urlParameters = explode('/', $urlParameters ?? '');

        if (empty($urlParameters[0])) {
            array_shift($urlParameters);
        }

        foreach ($urlParameters as $key => $tagName) {
            self::$childParameters['parameters'][$tagName] = self::$urlArray[$key] ?? null;

            if (isset(self::$urlArray[$key])) {
                unset(self::$urlArray[$key]);
            }
        }

        self::$urlArray = array_values(self::$urlArray);
    }

    private static function resolveRequestMethod(): void
    {
        $requestMethod = self::$childParameters['request'];

        if (isset($requestMethod)) {
            self::$childParameters['request'] = [];

            foreach ($requestMethod as $methodType => $methodToBeCalled) {
                $methodType = mb_strtolower($methodType);
                self::$childParameters['request'][$methodType] = $methodToBeCalled;
            }

            $serverRequest = mb_strtolower($_SERVER['REQUEST_METHOD']);

            self::$childParameters['request'] = self::$childParameters['request'][$serverRequest] ?? null;
        }
    }

    /**
     * Return the $urlArray property.
     *
     * @return array
     */
    public static function getUrlArray(): array
    {
        return self::$urlArray;
    }

    /**
     * Return the $parentNodeName property.
     *
     * @return null|string
     */
    public static function getParentNodeName(): ?string
    {
        return self::$parentNodeName;
    }

    /**
     * Return the $controllerNamespace property.
     *
     * @return array
     */
    public static function getControllerNamespace(): array
    {
        return self::$controllerNamespace;
    }

    /**
     * Return the $childNodeName property.
     *
     * @return null|string
     */
    public static function getChildNodeName(): ?string
    {
        return self::$childNodeName;
    }

    /**
     * Return the $dynamicNodes property.
     *
     * @return array
     */
    public static function getDynamicNodes(): array
    {
        return self::$dynamicNodes;
    }

    /**
     * Return specific dynamic node from $dynamicNodes property.
     *
     * @return string
     */
    public static function getDynamicNode(string $key): ?string
    {
        return self::$dynamicNodes[$key] ?? null;
    }
}
