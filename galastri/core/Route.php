<?php
namespace galastri\core;

use \galastri\modules\Functions as F;

class Route
{
    private static $urlArray;
    private static $controllerName = false;
    private static $controllerParams = [];
    private static $controllerPath = [];
    private static $dynamicNodes = [];
    private static $remainingUrlNodes = [];
    private static $methodName = false;
    private static $methodParams = [
        'downloadable' => false,
        'viewFile' => false,
        'fileBaseFolder' => false,
    ];
    private static $cascateParameters = [
        'appTitle' => false,
        'authFailRedirect' => false,
        'authTag' => false,
        'cache' => false,
        'controllerNamespace' => false,
        'viewDirectory' => false,
        'notFoundRedirect' => false,
        'offline' => false,
        'pageTitle' => false,
        'renderer' => false,
        'template' => false,
        'forceRedirect' => false,
    ];
    private static $additionalParams = [];

    public static function resolve()
    {
        self::prepareUrlArray();
        self::getControllerParams(GALASTRI_ROUTES);
        self::defineMethod();
        self::getMethodParams();
        self::getAdditionalParams();

        if (count(self::$controllerPath) > 1) {
            array_shift(self::$controllerPath);
        }

        vardump(self::$controllerPath, self::$methodName);
    }

    /**
     * Prepare an array with the URL nodes, which will be used as nodes.
     *
     * Everything in URL that is between the domain and the beginning of the
     * querystring (? key), will be divided into parts, inside an array.
     *
     * Example: mydomain.com/foo/bar?val1=baz&val2=qux
     *
     * - The domain and querystring will me ignored. Only the /foo/bar will be
     *   get as a string.
     * 
     * - The string '/foo/bar' will be divided into an array('foo', 'bar')
     * 
     * - Every key value will receive a '/' char in the beginning of their value
     *   like this: array('/foo', '/bar')
     * 
     * The result array is stored inside $urlArray attribute.
     * 
     * @return void
     */    
    private static function prepareUrlArray()
    {
        $bootstrapPath = ltrim(GALASTRI_PROJECT['bootstrapPath'], '/');

        $urlArray = explode('?', str_replace($bootstrapPath, '', $_SERVER['REQUEST_URI']));
        $urlArray = explode('/', $urlArray[0]);

        if (empty($urlArray[1])) {
            array_shift($urlArray);
        }

        foreach ($urlArray as &$value) {
            $value = '/'.$value;
        } unset($value);

        
        self::$urlArray = $urlArray;
    }
        
    private static function getControllerParams(array $routeNodes)
    {
        $found = false;

        $resolveNode = function(array $routeNodes, string $key)
        {
            array_shift(self::$urlArray);
            self::$controllerParams = $routeNodes[$key];
            self::defineCascateParams($routeNodes[$key]);
            self::storeControllerPath($key);
            self::getControllerParams($routeNodes[$key]);
            return true;
        };

        foreach (self::$urlArray as $urlNode) {
            if (isset($routeNodes[$urlNode])) {
                $found = $resolveNode($routeNodes, $urlNode);
                break;
            }

            if (!$found) {
                if ($dynamicNode = F::arrayKeySearch('/?', $routeNodes, MATCH_START)) {
                    self::storeDynamicNode($dynamicNode, $urlNode);
                    $found = $resolveNode($routeNodes, key($dynamicNode));
                    break;
                }
            }

            if (!$found) {
                self::$controllerName = false;
            }
        }

        self::$remainingUrlNodes = self::$urlArray;
    }

    private static function defineCascateParams(array $routeNodes)
    {
        foreach (self::$cascateParameters as $param => &$value) {
            if (array_key_exists($param, $routeNodes)) {
                $value = $routeNodes[$param];
            }
        }
    }

    private static function storeDynamicNode(array $dynamicNode, string $urlNode)
    {
        $dynamicNodeTag = substr(key($dynamicNode), 2);
        self::$dynamicNodes[$dynamicNodeTag] = ltrim($urlNode, '/');
    }

    private static function storeControllerPath(string $controllerName)
    {
        $controllerName = ltrim($controllerName, '/');
        $controllerName = empty($controllerName) ? 'index' : $controllerName;
        $controllerName = F::convertCase($controllerName, PASCAL_CASE);

        self::$controllerName = $controllerName;
        self::$controllerPath[] = '\\'.$controllerName;
    }

    private static function defineMethod()
    {
        if (empty(self::$remainingUrlNodes)) {
            self::$methodName = 'main';
        } else {
            self::$methodName = ltrim(self::$remainingUrlNodes[0], '/');
            array_shift(self::$remainingUrlNodes);
        }
    }

    private static function getMethodParams()
    {
        $found = false;

        foreach (self::$controllerParams as $param => $value) {
            if ($param === '@'.self::$methodName) {
                $found = true;
                $methodParams = $value;
                break;
            }
        }

        if ($found) {
            foreach (self::$methodParams as $key => $value) {
                self::$methodParams[$key] = $methodParams[$key] ?? false;
            }

            self::defineCascateParams(self::$methodParams);
        } else {
            self::$methodName = false;
        }
    }

    private static function getAdditionalParams()
    {
        $methodParams = self::$methodParams;
        if (isset($methodParams['parameters'])) {
            
            $additionalParams = $methodParams['parameters'];

            if (gettype($additionalParams) === 'string') {
                $additionalParams = explode('/', $additionalParams);
                if (empty($additionalParams[0])) {
                    array_shift($additionalParams);
                }
            }
            // vardump($additionalParams);

            foreach (self::$remainingUrlNodes as $key => $value) {
                $keyLabel = $additionalParams[$key];
                self::$additionalParams[$keyLabel] = ltrim($value, '/');
            }
        }
    }
}
