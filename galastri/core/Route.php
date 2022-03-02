<?php

namespace galastri\core;

use galastri\core\Parameters;
use galastri\extensions\Exception;
use galastri\modules\types\TypeString;
use galastri\modules\PerformanceAnalysis;

/**
 * This class do the route resolving using the URL of the request and the route configuration file.
 *
 * This class:
 * - Prepare the URL
 * - Resolve the routes based on the URL
 *   - Store dynamic nodes (when exists)
 *   - Resolve the parent node
 *     - Resolve parent parameters
 *     - Store a default namespace, based on the route resolving
 *   - Resolve route parameters
 *   - Resolve the child node
 *     - Resolve child parameters
 *     - Resolve URL parameters
 *     - Resolve request methods
 * - Store all the resolutions in the parameters
 */
final class Route implements \Language
{
    /**
     * Stores an array with all nodes of the URL, except the domain and query strings. This array is
     * used to define the parent, child and dynamic nodes and URL parameters of the route.
     *
     * @var array
     */
    private static array $urlArray;

    /**
     * Stores the values of the parent node, including the child node.
     *
     * @var array
     */
    private static array $parentValues;

    /**
     * Stores the dynamic nodes values from the URL when they are defined in the router
     * configuration. These values can be get in the route controller, for various purposes.
     *
     * This array need to follow the format:
     * - Key: stores the dynamic node tag, defined in the route configuration.
     * - Value: stores the dynamic node value, defined in the URL nodes.
     *
     * @var array
     */
    private static array $dynamicNodes = [];

    /**
     * Stores the namespace path which each value of this array refers to a namespace node.
     *
     * For example, a namespace 'This\Namespace' will be stored as ['This', 'Namespace].
     *
     * @var array
     */
    private static array $controllerNamespace;

    /**
     * Defines if the namespace need to be redefined. It is used for when a 'namespace' route
     * parameter is defined in the route.
     *
     * @var bool
     */
    private static bool $resetNamespace = false;

    /**
     * Stores the parent node name which will be used as the route controller name. When null means
     * that the parent node wasn't found in the route configuration.
     *
     * @var null|string
     */
    private static ?string $parentNodeName = null;

    /**
     * Stores the child node name, which will be used as the method from the route controller that
     * will be called. When null means that the child node wasn't found in the route configuration.
     *
     * @var null|string
     */
    private static ?string $childNodeName = null;

    /**
     * Stores a predefined parameters called 'route parameters'. Their values are changed as the
     * route resolving find them.
     *
     * The possible route parameters are:
     *
     * offline                bool                  Defines if the route is offline. When true,
     *                                              stops the execution and return an exception
     *                                              showing an error message.
     *
     * projectTitle           null|string           Defines the title of the project in this route.
     *                                              This parameter can be changed in the route
     *                                              controller. The value defined is returned to the
     *                                              output.
     *
     * pageTitle              null|string           Defines the title of the page in this route. It
     *                                              is meant to be used with View output, but its
     *                                              value is returned with the Json as well.
     *
     * authTag                null|string           Defines a tag that makes the route protected by
     *                                              an authentication requisite. If the user doesn't
     *                                              have the authTag defined here in its $_SESSION,
     *                                              then the request will be stopped from continue
     *                                              and will receive an exception error or will be
     *                                              redirected (only if an authFailRedirect
     *                                              parameter is set).
     *
     * authFailRedirect       null|string           Defines an URL (internal or external) to
     *                                              redirect the request when an authentication
     *                                              fails. As it is a route parameter, this works
     *                                              for the entire route from the node where it is
     *                                              defined.
     *
     * forceRedirect          null|string           Forces the route to be redirected to an URL
     *                                              (internal or external).
     *
     * namespace              null|string           Defines a custom namespace. When it is defined,
     *                                              the stored namespace until the node where it was
     *                                              set will be reseted and restarted from that
     *                                              point.
     *
     * notFoundRedirect       null|string           Defines a URL (internal or external) to redirect
     *                                              the request when the framework return an error
     *                                              404.
     *
     * output                 null|string           Defines a output script to the route. Can be one
     *                                              of the following: view, json, text or file.
     *
     * browserCache           array|null            Works only with File and View outputs. Defines a
     *                                              cache control that is returned to the browser.
     *                                              It is in array format because it can have two
     *                                              keys with values:
     *
     *                                              - Key 0  int     Stores the time that the cache
     *                                                               will last (in seconds).
     *                                              - Key 1  string  Optional. Sets cache control
     *                                                               directives, used by the
     *                                                               Cache-Control header.
     *
     * templateFile           null|string           Works only with View output. Defines a default
     *                                              template file to all View outputs to the route.
     *                                              It can be set in the project configuration or by
     *                                              the route controller.
     *
     * baseFolder             null|string           Works with File and View outputs. Defines the
     *                                              default directory location of the file or the
     *                                              view files.
     *
     * offlineMessage         string                Defines a default message to be returned to the
     *                                              request when 'offline' parameter is set to true.
     *
     * authFailMessage        string                Defines a default message to be returned to the
     *                                              request when an authentication fails and there
     *                                              is no authFailRedirect parameter defined.
     *
     * permissionFailMessage  string                Defines a default message to be returned to the
     *                                              request when the Permission class return that
     *                                              the permission failed. This message will be
     *                                              overwritten if the Permission class is
     *                                              configured with an 'onFail' method.
     *
     * ignoreMimeType       bool|null               Works only with File output. When true, ignores
     *                                              the validation that checks if the mime type of
     *                                              the file matches one of the defined in the mime
     *                                              type configuration file. When false, null or
     *                                              undefined, the validation will occur.
     * @var array
     */
    private static array $routeParameters;

    /**
     * Stores a predefined parameters called 'parent parameters'. Their values are set only by
     * parent nodes and the values is not passed throught other parent nodes.
     *
     * The possible parent parameters are:
     *
     * controller  null|string                      Defines a specific route controller to the
     *                                              parent node. It needs to be set in the namespace
     *                                              format, ie: 'ThisIs\MyCustom\Controller'. When
     *                                              null undefined, it will follow the namespace set
     *                                              by the route resolving.
     *
     * @var array
     */
    private static array $parentParameters;

    /**
     * Stores a predefined parameters called 'child parameters'. Their values are set only by child
     * nodes and the values is not passed throught other child nodes.
     *
     * The possible child parameters are:
     *
     * downloadable       bool|null                 Works only with File output. When true, defines
     *                                              that that file will be downloaded by the
     *                                              browser. When false, null or undefined, the file
     *                                              will be rendered in the browser body.
     *
     * allowedExtensions  array|null                Works only with File output. Defines a list of
     *                                              extensions that will be allowed to be accessed.
     *                                              When defined, the files with extensions that
     *                                              aren't in the list will return error 404, even
     *                                              if they exists in the folder.
     *
     * viewPath           null|string               Works only with View output. Defines a specific
     *                                              view file. When null or undefined, the view
     *                                              directory will follow the parent node namespace
     *                                              and the view file will be the same name as the
     *                                              child node.
     *
     * request            null|array                Defines a list of methods that will be called
     *                                              based on the request type (POST, GET, etc). It
     *                                              needs to follow the following format:
     *
     *                                              - Key: the request method type (POST, GET, etc).
     *                                              - Value: the name of the method in the route
     *                                              controller that will be called when the request
     *                                              method is the same of the defined in the key.
     *
     * parameters         null|string               Defines URL parameters that the URL can have.
     *                                              The parameters are URL nodes that come after the
     *                                              child node. They can be required parameters or
     *                                              optional (when set with the ? char in front of
     *                                              its tag name).
     *
     *                                              Example 1: parameters => 'param1/param2',
     *                                              This defines that after the child node, there
     *                                              are 2 required URL nodes that will be stored as
     *                                              param1 and param2.
     *
     *                                              Example 2: parameters => 'param1/?param2',
     *                                              This defines that after the child node, there
     *                                              are 1 required URL node and 1 optional URL node.
     *                                              They will be stored as param1 and param2. If the
     *                                              param2 isn't defined in the URL, its value will
     *                                              be 'null'.
     * @var array
     */
    private static array $childParameters;


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
     * This is the main method of the class and has a chain of methods that execute the route
     * resolving. The 'route resolving' means that this class will get the URL of the request
     * (except the domain and possible query strings) and will find, based on each 'node' of the
     * URL, what are the configurations that this URL points in the route configutarion file.
     *
     * The URL nodes are each value between / char in the URL. For example, in the URL
     * domain.com/page1/page2, the URL nodes are 'page1' and 'page2'.
     *
     * Each URL node take the URL resolving to a direction, based on the route configuration. The
     * final URL means the end of the route and points to a location in the route configuration.
     *
     * Example: domain.com/page1/pageB
     *
     * Route configuration:
     * '/' => [
     *      '/page1' => [
     *          'parameter' => 'value',
     *          @pageA => [parameters...],
     *          @pageB => [parameters...],
     *     ],
     * ];
     *
     * Taking the URL above, the route resolving will get the URL node 'page1' and will find if
     * there is a key in the route configuration exists. If it exists, then it will get its values
     * and find the next URL node 'pageB'. This ends the path finding and store the 'pageB'
     * parameters.
     *
     * While this path finding is occuring, other parameters, like the 'parameter' key in the
     * example above, is processed too.
     *
     * @return void
     */
    public static function resolve(): void
    {
        /**
         * First setup and preparation of the URL.
         */
        self::setupParameters();
        self::prepareUrlArray();

        /**
         * Execution of the route resolving, passing the route configuration as parameter.
         */
        self::resolveRoutes($GLOBALS['GALASTRI_ROUTES']);

        /**
         * After the route resolving, the array with the route configuration is removed from the
         * memory.
         */
        unset($GLOBALS['GALASTRI_ROUTES']);

        /**
         * The parent node found by the route resolving has the child node (or not) in its values.
         * This method will search for it and resolve its parameters when it exists.
         */
        self::resolveChildNode();

        /**
         * The first value of the $controllerNamespace property is always 'Index', but it can't be
         * like that if the URL points to a different parent node, or has more nodes, to create its
         * namespace. When this occur, the first one (the Index one) is removed.
         */
        if (count(self::$controllerNamespace) > 1) {
            array_shift(self::$controllerNamespace);
        }

        /**
         * All parameters resulted by the route resolving are validated and stored in the properties
         * of the Parameters class.
         */
        self::storeParameters();

        /**
         * The project configuration is removed from the memory because everything is stored in the
         * properties of the Parameters class.
         */
        unset($GLOBALS['GALASTRI_PROJECT']);
    }

    /**
     * This method only exists because the route parameters can't be initialized directly in its
     * definition with values from the $GLOBALS array. Here the values are set. To make things more
     * concise, the parent and child parameters are set here too.
     *
     * @return void
     */
    private static function setupParameters(): void
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
            'templateFile' => $GLOBALS['GALASTRI_PROJECT']['templateFile'] ?? null,
            'baseFolder' => null,
            'offlineMessage' => $GLOBALS['GALASTRI_PROJECT']['offlineMessage'] ?? null,
            'authFailMessage' => $GLOBALS['GALASTRI_PROJECT']['authFailMessage'] ?? null,
            'permissionFailMessage' => $GLOBALS['GALASTRI_PROJECT']['permissionFailMessage'] ?? null,
            'ignoreMimeType' => null,
        ];

        self::$parentParameters = [
            'controller' => null,
        ];

        self::$childParameters = [
            'downloadable' => false,
            'allowedExtensions' => null,
            'viewPath' => null,
            'request' => null,
            'parameters' => null,
        ];
    }

    /**
     * This method prepares the URL array, splitting it into parts and storing them in an array. The
     * only parts that are stored are the request, between the domain and the query string.
     *
     * Example: domain.com/page1/page2?key=value
     * In this URL the only part that will be stored will be 'part1' and 'part2'.
     *
     * In all the stored parts is added a / char at the start of the string. Following the example
     * above, the result will be '/part1' and '/part2'.
     *
     * @return void
     */
    private static function prepareUrlArray(): void
    {
        Parameters::setUrlRoot($GLOBALS['GALASTRI_PROJECT']['urlRoot'] ?? null);

        $urlArray = explode('?', (new TypeString($_SERVER['REQUEST_URI']))->replaceOnce(Parameters::getUrlRoot(), '')->get());
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
     * This method gets the route configuration array (from the route configuration file) and
     * untangle it based on the URL nodes from the URL array.
     *
     * @param  array $routeArray
     *
     * @return void
     */
    private static function resolveRoutes(array $routeArray): void
    {
        $found = false;

        /**
         * For each URL node, this method will search for a key in the route configuration that
         * matches its value. There are 3 possibilities: the exact match, which means that a static
         * parent node was found, the match with a key that starts with '/?', which means that a
         * dynamic parent node was found, and the unmatch.
         *
         * For any kind of match, the foreach is stoped because the resolveParentNode method is
         * called and it will recast the resolveRoutes parameter with the values of the key that was
         * found.
         */
        foreach (self::$urlArray as $urlNode) {

            /**
             * When a static parent node is found, the method is just redirect to another method
             * called resolveParentNode.
             */
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

                /**
                 * When a dynamic parent node is found, its URL node value is stored in the property
                 * $dynamicNodes and then the resolveParentNode is called.
                 */
                if ($dynamicTag) {
                    $found = true;

                    self::storeDynamicNode($dynamicTag, $urlNode);
                    self::resolveParentNode($routeArray, $dynamicTag);
                    break;
                }
            }

            /**
             * When there is no key found, then the $parentNodeName property is set as null.
             */
            if (!$found) {
                self::$parentNodeName = null;
            }
        }

        PerformanceAnalysis::flush(PERFORMANCE_ANALYSIS_LABEL);
    }

    /**
     * This method execute other 3 methods to resolve the parent nodes. The first thing that it does
     * is ro remove from the URL array the current key that was found. After that, it assumes that
     * the values inside the key that was found are the parent values. These values will be
     * overwritten aftwards if more parent nodes were found.
     *
     * Finally, the 3 methods are called, which each description are in their definitions below. The
     * last method called is the resolveRoutes, passing as parameter the current value of the key
     * that was found.
     *
     * @param  mixed $routeArray                    The array values that was found as parent node.
     *
     * @param  mixed $urlNode                       The current URL node value.
     *
     * @return void
     */
    private static function resolveParentNode(array $routeArray, string $urlNode)
    {
        array_shift(self::$urlArray);

        self::$parentValues = $routeArray[$urlNode];
        self::resolveParentParameters($routeArray[$urlNode]);
        self::resolveRouteParameters($routeArray[$urlNode]);
        self::storeNamespace($urlNode);

        self::resolveRoutes($routeArray[$urlNode]);
    }

    /**
     * This method pass throught each predefined parent parameter, stored in the $parentParameters
     * property, checking if it exists in the parent node that was found and store its value when it
     * exists. If the parameter doesn't exist, then its value is reseted to null.
     *
     * @param  array $nodeFound                     Array with the parent node values.
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
     * This method pass throught each predefined route parameter, stored in the $routeParameters
     * property, checking if it exists in the parent or child node that was found and store its
     * value when it exists. If the the parameter doesn't exist but was found in previous nodes, the
     * previous value is kept and is never reseted until another node overwrites its value.
     *
     * @param  array $nodeFound                     Array with the parent or child node values.
     *
     * @return void
     */
    private static function resolveRouteParameters(array $nodeFound): void
    {
        foreach (self::$routeParameters as $parameter => $value) {
            if (array_key_exists($parameter, $nodeFound)) {
                /**
                 * If the parameter is the 'namespace', it means that the stored value in the
                 * $controllerNamespace property needs to be cleared and restart. It needs to be
                 * that way because the 'namespace' route parameter sets a custom base namespace,
                 * which means that the route controller is stored in different directories instead
                 * of the default.
                 */
                if ($parameter === 'namespace') {
                    self::$resetNamespace = true;
                }

                self::$routeParameters[$parameter] = $nodeFound[$parameter];
            }
        }
    }

    /**
     * This method creates a sequence with each parent node found, which will be used to create the
     * namespace path. It is stored as an array because it is easier to handle than a string. The
     * result of this array will be a string afterwards.
     *
     * The name stored is always formatted in pascal case, which means that the name of the class of
     * the route controller and the file name need to follow the pascal case format.
     *
     * @param  string $urlNode                      The name of the node that will be stored in the
     *                                              array.
     *
     * @return void
     */
    private static function storeNamespace(string $urlNode): void
    {
        /**
         * The URL node gets filtered and converted to pascal case. Its value is check, if it is
         * empty or false, it means that the namespace will be the Index, otherwise, it will keep
         * the current value.
         */
        $urlNode = (new TypeString($urlNode))->trim('/', '?')->toPascalCase()->set(fn ($self) => $self->get() ?: 'Index')->get();

        self::$parentNodeName = $urlNode;

        /**
         * If there is a 'namespace' parameter set in the node, the stored value in the
         * $controllerNamespace will be reseted. Each time that this parameter is set in the route
         * configuration, the property will be reseted.
         */
        if (self::$resetNamespace) {
            self::$controllerNamespace = [];
            self::$resetNamespace = false;
        }

        self::$controllerNamespace[] = '\\'.$urlNode;
    }

    /**
     * This method store dynamic node values, creating an array which the key is the dynamic tag set
     * in the route configuration and its value is the value in the URL.
     *
     * Example:
     *
     * Route configuration:
     * '/' => [
     *      '/?page1' => [<parameters>]
     * ];
     *
     * URL: domain.com/my-page
     *
     * In the example above, the 'page1' is a dynamic node (starts with /?), which means that its
     * value in the URL doesn't need to be equal to 'page1', but can be any value. In the URL given
     * by the example, despite the name of the node in the route configuration is 'page1', the URL
     * node can be 'my-page' and no error will occur. The parent node will still be 'page1', and the
     * route controller to be called will be 'Page1'. The difference with static nodes is that the
     * value of the URL node 'my-page' will be stored and can be used in a route controller for any
     * purpose.
     *
     * @param  string $dynamicTag                   The tag name is the key of the node in the route
     *                                              configuration.
     *
     * @param  string $urlNode                      The name of the node that will be stored in the
     *                                              array.
     *
     * @return void
     */
    private static function storeDynamicNode(string $dynamicTag, string $urlNode): void
    {
        $dynamicTag = ltrim($dynamicTag, '/?');

        self::$dynamicNodes[$dynamicTag] = ltrim($urlNode, '/');
    }

    /**
     * This method do the route resolving. The last node is called child node and will be used as
     * the method that will be called from the route controller.
     *
     * If there is a parent value key that has the same name than the URL node starting with @
     * (which is the sign of the child nodes), it means that a child node exists. The parent value
     * that was found is handled by the resolveChildParameters and resolveRouteParameters methods,
     * and then it handles the 'request' and 'parameters' of the child node parameters (if these
     * parameters exist).
     *
     * @return void
     */
    private static function resolveChildNode(): void
    {
        /**
         * Remove the / char from the remaining URL nodes.
         */
        foreach (self::$urlArray as $key => $urlNode) {
            self::$urlArray[$key] = ltrim($urlNode, '/');
        }

        /**
         * This foreach loop makes shure that the search for the child node will occur in one of
         * these three ways:
         *
         * 1. When there are URL nodes in the URL array left and the first value will be searched as
         *    a child node in the parent values.
         * 2. When even if there is URL nodes left, the URL node value is not found in as a child
         *    node in the parent values and it need to do another search, now looking for a child
         *    node called 'main'.
         * 3. When there is no more URL nodes left and, because of that, it will assume that the
         *    child node to be searched is called 'main'.
         */
        foreach ([self::$urlArray[0] ?? null, 'main'] as $urlNode) {
            if ($urlNode === null) {
                continue;
            }

            /**
             * For each time that the previous loop is executed, this another loop will get the
             * parent values and will search for the child node in it.
             */
            foreach (self::$parentValues as $parentValueKey => $childNode) {
                if (gettype($parentValueKey) !== 'string') {
                    throw new Exception(self::INVALID_KEY_PARAMETER_TYPE[1], self::INVALID_KEY_PARAMETER_TYPE[0]);
                }

                /**
                 * If the child node is found in the parent values, it will convert to camel case
                 * and set it in the $childNodeName property. And if its name match the URL node
                 * from the URL array, this URL node is removed from the array.
                 */
                if ($parentValueKey === '@'.$urlNode) {
                    self::$childNodeName = (new TypeString($urlNode))->toCamelCase()->get();

                    if ((self::$urlArray[0] ?? null) === $urlNode) {
                        array_shift(self::$urlArray);
                    }

                    /**
                     * The child node found is handled to get child and possible route parameters
                     * defined in its values.
                     */
                    self::resolveChildParameters($childNode);
                    self::resolveRouteParameters($childNode);

                    /**
                     * Finally these methods will search for 'request' and 'parameters' parameters
                     * in the child node and resolve them.
                     */
                    self::resolveRequestMethod();
                    self::resolveUrlParameters();

                    break 2;
                }
            }
        }
    }


    /**
     * This method pass throught each predefined child parameter, stored in the $childParameters
     * property, checking if the parameter exists in the child node that was found and store its
     * value when it exists. If the parameter doesn't exist, then its value will be kept as null (as
     * set in the setupParameters method).
     *
     * @param  array $nodeFound                     Array with the parent node values.
     *
     * @return void
     */
    private static function resolveChildParameters(array $nodeFound): void
    {
        foreach (self::$childParameters as $parameter => $value) {
            if (array_key_exists($parameter, $nodeFound)) {
                self::$childParameters[$parameter] = $nodeFound[$parameter];
            }
        }
    }

    /**
     * This method check if there is a value stored in child parameter 'parameters'. If there is, it
     * will be converted to an array and for each expected parameter to be pass in the URL it will
     * store it as a tag name and the URL node will be stored as its value. When there is no URL
     * node for the parameter, it will be null.
     *
     * Each URL node set as a URL parameter it will be removed from the URL array.
     *
     * @return void
     */
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

        /**
         * The URL array has its keys reseted, because each time a key is removed from the URL array
         * it keeps their keys and this creates problems afterwards, so, they need to be reseted.
         */
        self::$urlArray = array_values(self::$urlArray);
    }

    /**
     * This method checks if there is a value stored in the child parameter 'request'. If there is,
     * each of the request method keys are converted to lower case to have its value compared to the
     * current server request method.
     *
     * If the server request method match one of the request methods defined in the 'request'
     * parameter, then it is stored. If not, the request parameter will be set as null.
     *
     * @return void
     */
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
     * This method stores all the child, parent and route parameters in their relative properties in
     * the Parameters class. It also stores the timezone, defined in the project configuration file.
     *
     * Each of the Parameters setter methods check if the values stored has the right variable
     * type and will return an exception if there is a misconfiguration in some of the parameters.
     *
     * @return void
     */
    private static function storeParameters(): void
    {
        Parameters::setTimezone($GLOBALS['GALASTRI_PROJECT']['timezone'] ?? null);
        Parameters::setController(self::$parentParameters['controller']);

        Parameters::setOffline(self::$routeParameters['offline']);
        Parameters::setProjectTitle(self::$routeParameters['projectTitle']);
        Parameters::setPageTitle(self::$routeParameters['pageTitle']);
        Parameters::setAuthTag(self::$routeParameters['authTag']);
        Parameters::setAuthFailRedirect(self::$routeParameters['authFailRedirect']);
        Parameters::setForceRedirect(self::$routeParameters['forceRedirect']);
        Parameters::setNamespace(self::$routeParameters['namespace']);
        Parameters::setNotFoundRedirect(self::$routeParameters['notFoundRedirect']);
        Parameters::setOutput(self::$routeParameters['output']);
        Parameters::setBrowserCache(self::$routeParameters['browserCache']);
        Parameters::setTemplateFile(self::$routeParameters['templateFile']);
        Parameters::setBaseFolder(self::$routeParameters['baseFolder']);
        Parameters::setOfflineMessage(self::$routeParameters['offlineMessage']);
        Parameters::setAuthFailMessage(self::$routeParameters['authFailMessage']);
        Parameters::setPermissionFailMessage(self::$routeParameters['permissionFailMessage']);
        Parameters::setIgnoreMimeType(self::$routeParameters['ignoreMimeType']);

        Parameters::setDownloadable(self::$childParameters['downloadable']);
        Parameters::setAllowedExtensions(self::$childParameters['allowedExtensions']);
        Parameters::setViewPath(self::$childParameters['viewPath']);
        Parameters::setRequest(self::$childParameters['request']);
        Parameters::setUrlParameters(self::$childParameters['parameters']);

        PerformanceAnalysis::flush(PERFORMANCE_ANALYSIS_LABEL);
    }

    /**
     * This method return the URL array. If it isn't empty it means that there are unused URL nodes.
     *
     * @return array
     */
    public static function getUrlArray(): array
    {
        return self::$urlArray;
    }

    /**
     * This method return the name of the parent node. When null it means that the parent node was
     * not found.
     *
     * @return string
     */
    public static function getParentNodeName(): ?string
    {
        return self::$parentNodeName;
    }

    /**
     * This method return the name of the child node. When null it means that the child node was not
     * found.
     *
     * @return string
     */
    public static function getChildNodeName(): ?string
    {
        return self::$childNodeName;
    }

    /**
     * This method return an array with the namespace of the route and which is used to define the
     * controller namespace.
     *
     * @return array
     */
    public static function getControllerNamespace(): array
    {
        return self::$controllerNamespace;
    }

    /**
     * This method return all the dynamic values stored by dynamic nodes in the URL.
     *
     * @return array
     */
    public static function getDynamicNodes(): array
    {
        return self::$dynamicNodes;
    }

    /**
     * This method return specific dynamic values for the given tag name stored by dynamic nodes in
     * the URL. It will return null if the tagName doesn't exist.
     *
     * @param  string $tagName                      Tag name defined in the route configuration that
     *                                              stores the value from the URL node.
     *
     * @return string
     */
    public static function getDynamicNode(string $tagName): ?string
    {
        return self::$dynamicNodes[$tagName] ?? null;
    }
}
