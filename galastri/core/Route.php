<?php

namespace galastri\core;

use galastri\core\Debug;
use galastri\core\Parameters;
use galastri\extensions\Exception;
use galastri\modules\types\TypeString;
use galastri\modules\types\TypeArray;
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

    /**
     * Stores the namespace in case of the parent node be a controller.
     *
     * @var array
     */
    private static array $controllerNamespace = [];

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
     * @key null|array|string requestMethod         Points to an internal method that will be called
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
     * @var array
     */
    private static array $routeParam = [];

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
        self::resolveRouteNodes($GLOBALS['GALASTRI_ROUTES']);

        unset($GLOBALS['GALASTRI_ROUTES']);

        self::defineChildNode();
        self::resolveChildNodeParam();
        self::resolveChildNodeParamRequestMethod();
        self::resolveUrlParam();

        if (count(self::$controllerNamespace) > 1) {
            array_shift(self::$controllerNamespace);
        }

        self::validateAndStoreParameters();

        unset($GLOBALS['GALASTRI_PROJECT']);
    }

    private static function setup(): void
    {
        self::$routeParam = [
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
     * The result array is stored inside $urlWorkingArray property.
     *
     * @return void
     */
    private static function prepareUrlArray(): void
    {
        /**
         * The URL root that will be controlled by the framework.
         */
        Parameters::setUrlRoot($GLOBALS['GALASTRI_PROJECT']['urlRoot'] ?? null);
        $bootstrapPath = ltrim(Parameters::getUrlRoot(), '/');

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
     * 2. Stores the node parameters in $nodeWorkingArray property
     * 3. Add the key label in the $controllerNamespace property
     * 4. Calls the method again to repeat the process.
     *
     * However, if there is no key that matches with the URL node, then he searchs if there is a
     * dynamic node there. Dynamic nodes always starts with '/?' and its label doesn't have to
     * matche the URL node.
     *
     * If there is a dynamic node, then its label and value is stored in the $dynamicNodeValues
     * property and then it execute a closure function in the variable $resolveNode explained
     * above.
     *
     * If there ir no node nor dynamic node, then this means that the URL node doesn't exist, so the
     * $parentNodeName is set as null.
     *
     * After all the tests, all the remaining URL nodes is stored in the $remainingUrlNodes
     * property.
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
                $dynamicNode = new TypeArray($routeNodes);
                if ($dynamicNode->searchKey('/?', MATCH_START)->isNotEmpty()) {
                    $dynamicKey = $dynamicNode->get(KEY);

                    self::storeDynamicNode($dynamicKey, $urlNode);
                    $found = $resolveNode($routeNodes, $dynamicKey);
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
            $value = $nodeFound[$param] ?? null;
        }
        unset($value);
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
    private static function resolveRouteParam(array $nodeFound): void
    {
        foreach (self::$routeParam as $param => &$value) {
            if (array_key_exists($param, $nodeFound)) {
                if ($param === 'namespace') {
                    self::$resetNamespace = true;
                }

                $value = $nodeFound[$param];
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
        $parentNodeName = new TypeString($parentNodeName);

        $parentNodeName
            ->trim('/', '?')
            ->toPascalCase()
            ->set(function ($self) {
                return $self->get() ?: 'Index';
            });


        self::$parentNodeName = $parentNodeName->get();

        if (self::$resetNamespace) {
            self::$controllerNamespace = [];
            self::$resetNamespace = false;
        }

        self::$controllerNamespace[] = '\\' . $parentNodeName->get();
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
     * the $remainingUrlNodes property.
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
            $param = (new TypeString(null))
            ->onError([
                self::INVALID_KEY_PARAMETER_TYPE[1],
                self::INVALID_KEY_PARAMETER_TYPE[0]
            ], gettype($param))
            ->set($param);

            if ($param->get() === '@' . self::$childNodeName) {
                $found = true;
                $childNodeParam = $value;
                self::$childNodeName = $param->trimStart('@')->toCamelCase()->get();
                break;
            }
        }
        
        if ($found) {
            foreach (self::$childNodeParam as $param => $value) {
                if (array_key_exists($param, $childNodeParam)) {
                    self::$childNodeParam[$param] = $childNodeParam[$param];
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
     * stored in the $childNodeParam property.
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

        $serverRequestMethod = new TypeString($_SERVER['REQUEST_METHOD']);
        $serverRequestMethod->toLowerCase()->set();

        if (self::$childNodeParam['requestMethod'] !== null) {
            foreach (self::$childNodeParam['requestMethod'] as $key => $value) {
                $key = new TypeString($key);
                $key->toLowerCase()->set();

                $value = new TypeString($value);

                if ($key->get() === $serverRequestMethod->get()) {
                    if ($value->substring(0, 1)->get() !== '@') {
                        throw new Exception(self::REQUEST_METHOD_STARTS_WITH_AT[1], self::REQUEST_METHOD_STARTS_WITH_AT[0], [$value->get()]);
                    } else {
                        $checkValue = $value->trimStart('@')->set()->regexMatch('/^[0-9]|[^a-zA-Z0-9_]*/')->get()->key(0)->join()->get();
                        
                        if ($checkValue->get() !== '') {
                            throw new Exception(self::INVALID_REQUEST_METHOD_NAME[1], self::INVALID_REQUEST_METHOD_NAME[0], [$value->get()]);
                        } else {
                            self::$childNodeParam['requestMethod'] = $value->toCamelCase()->get();
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
     * Gets all the remaining URL nodes and stores in the $urlParam properties since there is a
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
     * The tag name will be stored as a key label on the $urlParam property, while its value in the
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
    
    private static function validateAndStoreParameters(): void
    {
        Parameters::setTimezone($GLOBALS['GALASTRI_PROJECT']['timezone'] ?? null);
        Parameters::setController(self::$parentNodeParam['controller']);

        Parameters::setOffline(self::$routeParam['offline']);
        Parameters::setOfflineMessage(self::$routeParam['offlineMessage']);
        Parameters::setForceRedirect(self::$routeParam['forceRedirect']);
        Parameters::setOutput(self::$routeParam['output']);
        Parameters::setNotFoundRedirect(self::$routeParam['notFoundRedirect']);
        Parameters::setNamespace(self::$routeParam['namespace']);
        Parameters::setProjectTitle(self::$routeParam['projectTitle']);
        Parameters::setPageTitle(self::$routeParam['pageTitle']);
        Parameters::setAuthTag(self::$routeParam['authTag']);
        Parameters::setAuthFailRedirect(self::$routeParam['authFailRedirect']);
        Parameters::setViewTemplateFile(self::$routeParam['viewTemplateFile']);
        Parameters::setViewBaseFolder(self::$routeParam['viewBaseFolder']);
        
        Parameters::setRequestMethod(self::$childNodeParam['requestMethod']);
        Parameters::setFileDownloadable(self::$childNodeParam['fileDownloadable']);
        Parameters::setFileBaseFolder(self::$childNodeParam['fileBaseFolder']);
        Parameters::setViewFilePath(self::$childNodeParam['viewFilePath']);

        PerformanceAnalysis::flush(PERFORMANCE_ANALYSIS_LABEL);
    }

    /**
     * Return the $urlParam property.
     *
     * @return array
     */
    public static function getUrlParam(): array
    {
        return self::$urlParam;
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
     * Return the $nodeWorkingArray property.
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
     * Return the $childNodeParam property.
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
     * Return the $dynamicNodeValues property.
     *
     * @return array|string
     */
    public static function getDynamicNodeValue(?string $key = null) // : array|string
    {
        return $key === null ? self::$dynamicNodeValues : self::$dynamicNodeValues[$key];
    }

    /**
     * Return the $routeParam property.
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
