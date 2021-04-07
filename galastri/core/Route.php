<?php
namespace galastri\core;

use \galastri\modules\Functions as F;
use \galastri\modules\PerformanceAnalysis;

/**
 * This class resolves the URL routing.
 *
 * Every routing configuration is done in \app\config\routes.php, which uses a
 * nested multiarray, which every key is a node. Every node can have children
 * nodes, which represents the continuity of the route, in a directory-like
 * structure.
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
 * In the URL, each slash bar (/) represents a node and every subsequent slash
 * bar represents its children. So, mydomain.com/node-a/node-ab and so on. Every
 * child node can be parent for other children and this is the way the URL
 * routing is configured.
 */
class Route
{
    /**
     * Stores the URL nodes in array format which will be worked to extract the
     * parent's parameters, define the node that will be called, its child and
     * local parameters.
     *
     * @var array
     */
    private static $urlWorkingArray;

    /**
     * urlParams
     *
     * @var array
     */
    private static $urlParams = [];

    /**
     * Stores the parent node's parameters, including child nodes and child
     * nodes that are parents too.
     *
     * @var array
     */
    private static $nodeWorkingArray = [];

    /**
     * After all process to define the parent node, there are more nodes after
     * that. This remaining nodes will be worked to define the child node's name
     * its parameters and if there are additional url parameters.
     *
     * @var array
     */
    private static $remainingUrlNodes = [];

    /**
     * Stores the parent node name in the given URL. When false means that no
     * parent node was found in the routing configuration file.
     *
     * @var string|bool
     */
    private static $parentNodeName = false;

        /**
     * Stores parent nodes specific parameters.
     *
     * @key bool|string controller      Defines custom controller to the node
     *                                  and its children, instead the default
     *                                  \app\controller.
     * @var array
     */
    private static $parentNodeParams = [
        'controller' => false,
    ];

    /**
     * Stores the namespace in case of the parent node be a controller.
     *
     * @var array
     */
    private static $controllerNamespace = [];


    /**
     * When the global parameter 'namespace' is found in the parent's node, it
     * is set to true temporarily and all data stored in $controllerNamespace
     * attribute is resetted.
     *
     * @var bool
     */
    private static $resetNamespace = false;

    /**
     * Stores the child node name in the given URL. When false means that no
     * child node was found in the routing configuration file.
     *
     * @var string|bool
     */
    private static $childNodeName = false;

    /**
     * Stores child nodes specific parameters.
     *
     * @key bool fileDownloadable       Works only with File solvers. Defines if
     *                                  the file is downloadable.
     *
     * @key bool|string fileBaseFolder  Works only with File solvers. Defines a
     *                                  custom folder where the file is located.
     *
     * @key bool|string viewFilePath    Works only with View solvers. Defines a
     *                                  custom view file instead the default.
     *
     * @key bool|array requestMethod    Points to an internal method that will
     *                                  be called based on the request method.
     *                                  The key of the array needs to have the
     *                                  name of request method (POST, GET, PUT,
     *                                  etc..) and its value needs to be the
     *                                  method to be called, always starting
     *                                  with @, for better identification.
     * @var array
     */
    private static $childNodeParams = [
        'fileDownloadable' => false,
        'fileBaseFolder' => false,
        'viewFilePath' => false,
        'requestMethod' => false,
    ];
    
    /**
     * Stores the tag names of dynamic nodes and its values in the URL. Dynamic
     * nodes are like url parameters, but in reverse position: url parameters
     * are after the child nodes, while the dynamic nodes goes before. Dynamic
     * nodes also calls for dynamic controllers when required.
     *
     * @var array
     */
    private static $dynamicNodeValues = [];

    /**
     * Stores global parameters, inherited by parents nodes.
     *
     * @key bool|string projectTitle    Defines a custom app title instead of
     *                                  the default defined in the
     *                                  \app\config\project.php file.
     *
     * @key bool|string authFailRedirect Defines a URL, path or url alias to
     *                                  redirect the users that requests paths
     *                                  that needs authorization to access but
     *                                  doesn't have.
     *
     * @key bool|string authTag         Defines a tag string that the user
     *                                  session needs to have access to the node
     *                                  and its children. When false, defines
     *                                  that the node or child doesn't need that
     *                                  authorization.
     *
     * @key bool|int browserCache       Defines a cache time to the node and its
     *                                  children (in seconds). When false, the
     *                                  node won't be cached.
     *
     * @key bool|string namespace       Defines custom namespace for controllers
     *                                  to the node and its children, instead
     *                                  the default \app\controller.
     *
     * @key bool|string viewBaseFolder  Works only with View solvers. Defines a
     *                                  custom folder where views are located.
     *
     * @key bool|string notFoundRedirect Defines a custom URL, path or URL alias
     *                                  when a file or URL path is not found
     *                                  (error 404).
     *
     * @key bool offline                Defines that the node and its children
     *                                  is offline. No scripts are executed when
     *                                  it is defined as true. Useful when doing
     *                                  maintenance.
     *
     * @key bool|string pageTitle       Defines a static page title to the node
     *                                  and its children. It can be changed in
     *                                  controller if the page title needs to be
     *                                  dynamic.
     *
     * @key string solver               Defines which solver will be used in the
     *                                  node and its children. A solver is a
     *                                  trait that will return the data into a
     *                                  type. The currently solvers are:
     *
     *                                  - File: returns a file;
     *                                  - View: returns a HTML;
     *                                  - Json: returns data in json format;
     *                                  - Text: returns data in plain text.
     *
     * @key bool|array viewTemplate     Works only with View solvers. Defines
     *                                  the template files where the view will
     *                                  be printed. It is an associative array,
     *                                  which the key is the tag label and its
     *                                  value is the path of the template file.
     *
     * @key bool|string forceRedirect   Force the request to be redirected to a
     *                                  URL, path or URL alias when the node or
     *                                  its children is accessed.
     *
     * @key bool|string message         Defines a custom set of messages instead
     *                                  of the ones defined in
     *                                  \app\config\project.php file.
     *
     * @var array
     */
    private static $globalParamValues = [
        'projectTitle' => GALASTRI_PROJECT['projectTitle'],
        'authFailRedirect' => false,
        'authTag' => false,
        'browserCache' => false,
        'namespace' => false,
        'viewBaseFolder' => false,
        'notFoundRedirect' => GALASTRI_PROJECT['notFoundRedirect'],
        'offline' => GALASTRI_PROJECT['offline'],
        'pageTitle' => false,
        'solver' => false,
        'viewTemplate' => GALASTRI_PROJECT['viewTemplate'],
        'forceRedirect' => false,
        'message' => GALASTRI_PROJECT['message'],
    ];

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
     * Execute a chain of methods that resolves the URL string, searching for
     * nodes in \app\config\routes.php that matches the requested URL and
     * storing its parameters.
     *
     * @return void
     */
    public static function resolve()
    {
        self::prepareUrlArray();
        self::resolveRouteNodes(GALASTRI_ROUTES);
        self::defineChildNode();
        self::resolveChildNodeParams();
        self::getAdditionalParams();

        if (count(self::$controllerNamespace) > 1) {
            array_shift(self::$controllerNamespace);
        }
    }

    /**
     * Prepare an array with the URL, which will be used as nodes.
     *
     * Everything in URL that is between the domain and the beginning of the
     * querystring (? char), will be divided into parts, inside an array.
     *
     * Example: mydomain.com/foo/bar?val1=baz
     *
     * - The domain and querystring will be ignored. Only the /foo/bar will be
     *   get as a string.
     *
     * - The string '/foo/bar' will be divided into an array('foo', 'bar')
     *
     * - Every key value will receive a '/' char in the beginning of their value
     *   like this: array('/foo', '/bar')
     *
     * The result array is stored inside $urlWorkingArray attribute.
     *
     * @return void
     */
    private static function prepareUrlArray()
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
            $value = '/'.$value;
        }
        unset($value);

        
        self::$urlWorkingArray = $urlWorkingArray;

        PerformanceAnalysis::flush(PERFORMANCE_ANALYSIS_LABEL);
    }
            
    /**
     * This method gets te configuration file \app\config\routes.php and search
     * for a key that matches with the URL nodes stored in the $urlWorkingArray.
     *
     * It starts with a foreach, looking if de first URL node exists in the
     * configuration file. If it exists, then it execute a closure function in
     * the variable $resolveNode.
     *
     * This function:
     * 1. Removes the current key off the $urlWorkingArray
     * 2. Stores the node parameters in $nodeWorkingArray attribute
     * 3. Add the key label in the $controllerNamespace attribute
     * 4. Calls the method again to repeat the process.
     *
     * However, if there is no key that matches with the URL node, then he
     * searchs if there is a dynamic node there. Dynamic nodes always starts
     * with '/?' and its label doesn't have to matche the URL node.
     *
     * If there is a dynamic node, then its label and value is stored in the
     * $dynamicNodeValues attribute and then it execute a closure function in
     * the variable $resolveNode explained above.
     *
     * If there ir no node nor dynamic node, then this means that the URL node
     * doesn't exist, so the $parentNodeName is set as false.
     *
     *
     * After all the tests, all the remaining URL nodes is stored in the
     * $remainingUrlNodes attribute.
     *
     * @param  array $routeNodes        Multidimensional array with the
     *                                  configuration of the project routing.
     * @return void
     */
    private static function resolveRouteNodes(array $routeNodes)
    {
        $found = false;

        $resolveNode = function (array $routeNodes, string $key) {
            array_shift(self::$urlWorkingArray);
            self::$nodeWorkingArray = $routeNodes[$key];
            self::resolveParentParams($routeNodes[$key]);
            self::resolveGlobalParams($routeNodes[$key]);
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
                if ($dynamicNode = F::arrayKeySearch('/?', $routeNodes, MATCH_START)) {
                    self::storeDynamicNode(key($dynamicNode), $urlNode);
                    $found = $resolveNode($routeNodes, key($dynamicNode));
                    break;
                }
            }

            if (!$found) {
                self::$parentNodeName = false;
            }
        }

        self::$remainingUrlNodes = self::$urlWorkingArray;
        PerformanceAnalysis::flush(PERFORMANCE_ANALYSIS_LABEL);
    }

        
    /**
     * Searchs in the found node if there is any parent global parameter. This
     * parameters are inherited by the subsequent nodes. If there is any, its
     * value is overwrited by the new value.
     *
     * NOTE: When the global parameter 'namespace' exists, a new namespace needs
     * to be set as starting point from the node. This means that all the stored
     * $controllerNamespace attribute needs to restart. That is why there is a
     * test to check if the parameter 'namespaces' exists.
     *
     * @param  array $nodeFound         Multidimensional array with the node
     *                                  parameters found in the routing
     *                                  configuration.
     * @return void
     */
    private static function resolveGlobalParams(array $nodeFound)
    {
        foreach (self::$globalParamValues as $param => &$value) {
            if (array_key_exists($param, $nodeFound)) {
                $value = (function($param, $nodeFound){
                    switch ($param) {
                        case 'namespace':
                            self::$resetNamespace = true;
                            return F::convertCase($nodeFound[$param], PASCAL_CASE);
                        default:
                            return $value = $nodeFound[$param];
                    }
                })($param, $nodeFound);
            }
        }
    }

    private static function resolveParentParams(array $nodeFound)
    {
        foreach (self::$parentNodeParams as $param => &$value) {
            if (array_key_exists($param, $nodeFound)) {
                $value = $nodeFound[$param];
            } else {
                $value = false;
            }
        }
    }
    
    /**
     * Stores the dynamic label tag found in routing configuration file and its
     * value in the URL node.
     *
     * @param  string $dynamicNodeTag   The key label of the dynamic node.
     *
     * @param  astring $urlNode         The value of the node in the URL.
     *
     * @return void
     */
    private static function storeDynamicNode(string $dynamicNodeTag, string $urlNode)
    {
        $dynamicNodeTag = substr($dynamicNodeTag, 2);
        self::$dynamicNodeValues[$dynamicNodeTag] = ltrim($urlNode, '/');
    }
    
    /**
     * Stores the parent node name to create a namespace path. It will be used
     * when calling a controller with the name of the node which its path is the
     * same as the URL path.
     *
     * @param  string $parentNodeName   The parent node name that will be part of
     *                                  the namespace
     * @return void
     */
    private static function addControllerNamespacePath(string $parentNodeName)
    {
        $parentNodeName = F::trim($parentNodeName, '/', '?');
        $parentNodeName = $parentNodeName ?: 'index';
        $parentNodeName = F::convertCase($parentNodeName, PASCAL_CASE);

        self::$parentNodeName = $parentNodeName;

        if (self::$resetNamespace) {
            self::$controllerNamespace = [];
            self::$resetNamespace = false;
        }

        self::$controllerNamespace[] = '\\'.$parentNodeName;
    }
    
    /**
     * Defines the name of the child node, based on the remaining URL nodes. If
     * there are no remaining URL nodes, then the child node name will be
     * 'main'. If there are remaining URL nodes, then the first node will be the
     * name of the child node and that will be dropped from the
     * $remainingUrlNodes attribute.
     *
     * @return void
     */
    private static function defineChildNode()
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
     * This method searchs for a parent node parameter that matches with the
     * name of the child parameter that is the last of the chain (in short, the
     * node that starts with @).
     *
     * If it exists, then the parameters are stored. If not, this means that
     * there is no child node with that name configured in the routing
     * configuration file, so, the child node name will be set as false.
     *
     * @return void
     */
    private static function resolveChildNodeParams()
    {
        $found = false;

        foreach (self::$nodeWorkingArray as $param => $value) {
            if ($param === '@'.self::$childNodeName) {
                $found = true;
                $childNodeParams = $value;
                break;
            }
        }

        if ($found) {
            foreach (self::$childNodeParams as $key => $value) {
                self::$childNodeParams[$key] = $childNodeParams[$key] ?? false;
            }
            self::resolveGlobalParams($childNodeParams);
        } else {
            self::$childNodeName = false;
        }
        PerformanceAnalysis::flush(PERFORMANCE_ANALYSIS_LABEL);

    }
    
    /**
     * Gets all the remaining URL nodes and stores in the $urlParams attributes
     * since there is a 'parameters' parameter configured in the routing
     * configuration.
     *
     * There are two ways to configure parameters in the routing configuration
     * file:
     *
     *      'parameters' => '/tag1/tag2',
     *
     *      Or
     *
     *      'parameters' => ['tag1', 'tag2'],
     *
     * The tag name will be stored as a key label on the $urlParams attribute,
     * while its value in the url will be stored as value of this key.
     *
     * @return void
     */
    private static function getAdditionalParams()
    {
        $childNodeParams = self::$childNodeParams;
        if (isset($childNodeParams['parameters'])) {
            $urlParams = $childNodeParams['parameters'];

            if (gettype($urlParams) === 'string') {
                $urlParams = explode('/', $urlParams);
                if (empty($urlParams[0])) {
                    array_shift($urlParams);
                }
            }

            foreach (self::$remainingUrlNodes as $key => $value) {
                $keyLabel = $urlParams[$key];
                self::$urlParams[$keyLabel] = ltrim($value, '/');
            }
        }
        PerformanceAnalysis::flush(PERFORMANCE_ANALYSIS_LABEL);
    }
    
    /**
     * Return the $urlParams attribute.
     *
     * @return array
     */
    public static function getUrlParams()
    {
        return self::$urlParams;
    }
    
    /**
     * Return the $parentNodeName attribute.
     *
     * @return string|bool
     */
    public static function getParentNodeName()
    {
        return self::$parentNodeName;
    }
    
    /**
     * Return the $nodeWorkingArray attribute.
     *
     * @param  string|bool $key         Specify which key will be returned.
     * 
     * @return array
     */
    public static function getParentNodeParams(mixed $key = false)
    {
        return $key === false ? self::$parentNodeParams : self::$parentNodeParams[$key];
    }
    
    /**
     * Return the $controllerNamespace attribute.
     *
     * @return array
     */
    public static function getControllerNamespace()
    {
        return self::$controllerNamespace;
    }
    
    /**
     * Return the $childNodeName attribute.
     *
     * @return string|bool
     */
    public static function getChildNodeName()
    {
        return self::$childNodeName;
    }
    
    /**
     * Return the $childNodeParams attribute.
     *
     * @param  string|bool $key         Specify which key will be returned.
     * 
     * @return array
     */
    public static function getChildNodeParams(mixed $key = false)
    {
        return $key === false ? self::$childNodeParams : self::$childNodeParams[$key];
    }
    
    /**
     * Return the $dynamicNodeValues attribute.
     *
     * @return array
     */
    public static function getDynamicNodeValues()
    {
        return self::$dynamicNodeValues;
    }
        
    /**
     * Return the $globalParamValues attribute.
     *
     * @param  string|bool $key         Specify which key will be returned.
     *
     * @return mixed
     */
    public static function getGlobalParams(mixed $key = false)
    {
        return $key === false ? self::$globalParamValues : self::$globalParamValues[$key];
    }
}
