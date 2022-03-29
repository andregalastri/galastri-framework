<?php

namespace galastri\core;

use galastri\extensions\Exception;

/**
 * This class stores all the parameters of the configuration of the route. Its is a continuity of
 * the Route class that puts the parameters in a more organized structure instead of putting all
 * these properties and methods in the Route class. It also allow to do the calls of the methods
 * using a more concise class name, instead calling the Route class.
 */
final class Parameters implements \galastri\lang\English
{
    /**
     * The directory where the language interfaces are stored.
     */
    const LANGUAGE_DIRECTORY = GALASTRI_PROJECT_DIR.'/galastri/lang';

    /**
     * Project parameters. For more information about these parameters, see the
     * app/config/project.php file.
     */

    /**
     * Stores the url root from the project configuration.
     *
     * @var string
     */
    private static string $urlRoot;

    /**
     * Stores the timezone from the project or route configuration.
     *
     * @var null|string
     */
    private static ?string $timezone = null;




    //////

    /**
     * Parent parameters. For more information about these parameters, see the
     * $parentParameters property description in the galastri/core/Route.php.
     */

    /**
     * Stores the controller parameter from the route configuration.
     *
     * @var null|string
     */
    private static ?string $controller = null;




    //////

    /**
     * Route parameters. For more information about these parameters, see the
     * $routeParameters property description in the galastri/core/Route.php.
     */

    /**
     * Stores the offline parameter from the route configuration.
     *
     * @var bool
     */
    private static bool $offline;

    /**
     * Stores the projectTitle parameter from the project or route configuration.
     *
     * @var null|string
     */
    private static ?string $projectTitle = null;

    /**
     * Stores the pageTitle parameter from the route configuration.
     *
     * @var null|string
     */
    private static ?string $pageTitle = null;

    /**
     * Stores the authTag parameter from the route configuration.
     *
     * @var null|string
     */
    private static ?string $authTag = null;

    /**
     * Stores the authFailRedirect parameter from the route configuration.
     *
     * @var null|string
     */
    private static ?string $authFailRedirect = null;

    /**
     * Stores the forceRedirect parameter from the route configuration.
     *
     * @var null|string
     */
    private static ?string $forceRedirect = null;

    /**
     * Stores the namespace parameter from the route configuration.
     *
     * @var null|string
     */
    private static ?string $namespace = null;

    /**
     * Stores the notFoundRedirect parameter from the route configuration.
     *
     * @var null|string
     */
    private static ?string $notFoundRedirect = null;

    /**
     * Stores the output parameter from the route configuration.
     *
     * @var null|string
     */
    private static ?string $output = null;

    /**
     * Stores the browserCache parameter from the route configuration.
     *
     * @var null|string
     */
    private static ?array $browserCache = null;

    /**
     * Stores the templateFile parameter from the route configuration.
     *
     * @var null|string
     */
    private static ?string $templateFile = null;

    /**
     * Stores the baseFolder parameter from the route configuration.
     *
     * @var null|string
     */
    private static ?string $baseFolder = null;

    /**
     * Stores the offlineMessage parameter from the project or route configuration.
     *
     * @var string
     */
    private static string $offlineMessage;

    /**
     * Stores the authFailMessage parameter from the project or route configuration.
     *
     * @var string
     */
    private static string $authFailMessage;

    /**
     * Stores the permissionFailMessage parameter from the project or route configuration.
     *
     * @var string
     */
    private static string $permissionFailMessage;

    /**
     * Stores the ignoreMimeType parameter from the route configuration.
     *
     * @var bool|null
     */
    private static ?bool $ignoreMimeType = null;

    /**
     * Stores the templateEngine parameter from the route configuration.
     *
     * @var array|null
     */
    private static ?array $templateEngine = null;



    //////

    /**
     * Child parameters. For more information about these parameters, see the
     * $childParameters property description in the galastri/core/Route.php.
     */

    /**
     * Stores the downloadable parameter from the route configuration.
     *
     * @var bool
     */
    private static ?bool $downloadable = null;

    /**
     * Stores the allowedExtensions parameter from the route configuration.
     *
     * @var array|null
     */
    private static ?array $allowedExtensions = null;

    /**
     * Stores the viewPath parameter from the route configuration.
     *
     * @var null|string
     */
    private static ?string $viewPath = null;

    /**
     * Stores the request parameter from the route configuration.
     *
     * @var null|string
     */
    private static ?string $request = null;

    /**
     * Stores the urlParameters parameter from the route configuration.
     *
     * @var array|null
     */
    private static ?array $urlParameters = null;




    //////

    /**
     * Debug parameters. For more information about these parameters, see the
     * app/config/debug.php file.
     */

    /**
     * Stores the displayErrors parameter from the debug configuration.
     *
     * @var bool
     */
    private static bool $displayErrors;

    /**
     * Stores the showBacklogData parameter from the debug configuration.
     *
     * @var bool
     */
    private static bool $showBacklogData;

    /**
     * Stores the performanceAnalysis parameter from the debug configuration.
     *
     * @var bool
     */
    private static bool $performanceAnalysis;

    /**
     * Stores the language parameter from the debug configuration.
     *
     * @var string
     */
    private static string $language = 'English';

    /**
     * This is a singleton class, the __construct() method is private to avoid users to instanciate
     * it.
     *
     * @return void
     */
    private function __construct()
    {
    }




    //////

    /**
     * Project parameters.
     */

    /**
     * Sets the urlRoot parameter. The value needs to be string and cannot be null.
     *
     * @param  string $value                        The urlRoot parameter value.
     *
     * @return void
     */
    public static function setUrlRoot($value): void
    {
        self::isOfTypeNotNull(
            'string', $value,
            self::INVALID_URL_ROOT_TYPE,
            self::UNDEFINED_URL_ROOT
        );
        self::$urlRoot = ltrim($value, '/');
    }

    /**
     * Returns the urlRoot parameter.
     *
     * @return string
     */
    public static function getUrlRoot(): string
    {
        return self::$urlRoot;
    }

    /**
     * Sets the timezone parameter and defines it internally. The value needs to be string or null.
     *
     * @param  null|string $value                   The timezone parameter value.
     *
     * @return void
     */
    public static function setTimezone($value): void
    {
        self::isOfType(
            'string', $value,
            self::INVALID_TIMEZONE_TYPE
        );
        self::$timezone = $value;

        if($value !== null) {
            date_default_timezone_set($value);
        }
    }

    /**
     * Returns the timezone parameter.
     *
     * @return null|string
     */
    public static function getTimezone(): ?string
    {
        return self::$timezone;
    }




    //////

    /**
     * Parent parameters.
     */

    /**
     * Sets the controller parameter. The value needs to be string or null.
     *
     * @param  null|string $value                   The controller parameter value.
     *
     * @return void
     */
    public static function setController($value): void
    {
        self::isOfType(
            'string', $value,
            self::INVALID_CONTROLLER_TYPE
        );
        self::$controller = $value;
    }

    /**
     * Returns the controller parameter.
     *
     * @return null|string
     */
    public static function getController(): ?string
    {
        return self::$controller;
    }



    //////

    /**
     * Route parameters.
     */

    /**
     * Sets the offline parameter. The value needs to be bool and cannot be null.
     *
     * @param  bool $value                        The offline parameter value.
     *
     * @return void
     */
    public static function setOffline($value): void
    {
        self::isOfTypeNotNull(
            'boolean', $value,
            self::INVALID_OFFLINE_TYPE,
            self::UNDEFINED_OFFLINE
        );
        self::$offline = $value;
    }

    /**
     * Returns the offline parameter.
     *
     * @return bool
     */
    public static function getOffline(): bool
    {
        return self::$offline;
    }

    /**
     * Sets the projectTitle parameter. The value needs to be string or null.
     *
     * @param  null|string $value                   The projectTitle parameter value.
     *
     * @return void
     */
    public static function setProjectTitle($value): void
    {
        self::isOfType(
            'string', $value,
            self::INVALID_PROJECT_TITLE_TYPE
        );
        self::$projectTitle = $value;
    }

    /**
     * Returns the projectTitle parameter.
     *
     * @return null|string
     */
    public static function getProjectTitle(): ?string
    {
        return self::$projectTitle;
    }

    /**
     * Sets the pageTitle parameter. The value needs to be string or null.
     *
     * @param  null|string $value                   The pageTitle parameter value.
     *
     * @return void
     */
    public static function setPageTitle($value): void
    {
        self::isOfType(
            'string', $value,
            self::INVALID_PAGE_TITLE_TYPE
        );
        self::$pageTitle = $value;
    }

    /**
     * Returns the pageTitle parameter.
     *
     * @return null|string
     */
    public static function getPageTitle(): ?string
    {
        return self::$pageTitle;
    }

    /**
     * Sets the authTag parameter. The value needs to be string or null.
     *
     * @param  null|string $value                   The authTag parameter value.
     *
     * @return void
     */
    public static function setAuthTag($value): void
    {
        self::isOfType(
            'string', $value,
            self::INVALID_AUTH_TAG_TYPE
        );
        self::$authTag = $value;
    }

    /**
     * Returns the authTag parameter.
     *
     * @return null|string
     */
    public static function getAuthTag(): ?string
    {
        return self::$authTag;
    }

    /**
     * Sets the authFailRedirect parameter. The value needs to be string or null.
     *
     * @param  null|string $value                   The authFailRedirect parameter value.
     *
     * @return void
     */
    public static function setAuthFailRedirect($value): void
    {
        self::isOfType(
            'string', $value,
            self::INVALID_AUTH_FAIL_REDIRECT_TYPE
        );
        self::$authFailRedirect = $value;
    }

    /**
     * Returns the authFailRedirect parameter.
     *
     * @return null|string
     */
    public static function getAuthFailRedirect(): ?string
    {
        return self::$authFailRedirect;
    }

    /**
     * Sets the forceRedirect parameter. The value needs to be string or null.
     *
     * @param  null|string $value                   The forceRedirect parameter value.
     *
     * @return void
     */
    public static function setForceRedirect($value): void
    {
        self::isOfType(
            'string', $value,
            self::INVALID_FORCE_REDIRECT_TYPE
        );
        self::$forceRedirect = $value;
    }

    /**
     * Returns the forceRedirect parameter.
     *
     * @return null|string
     */
    public static function getForceRedirect(): ?string
    {
        return self::$forceRedirect;
    }

    /**
     * Sets the namespace parameter. The value needs to be string or null.
     *
     * @param  null|string $value                   The namespace parameter value.
     *
     * @return void
     */
    public static function setNamespace($value): void
    {
        self::isOfType(
            'string', $value,
            self::INVALID_NAMESPACE_TYPE
        );
        self::$namespace = $value;
    }

    /**
     * Returns the namespace parameter.
     *
     * @return null|string
     */
    public static function getNamespace(): ?string
    {
        return self::$namespace;
    }

    /**
     * Sets the notFoundRedirect parameter. The value needs to be string or null.
     *
     * @param  null|string $value                   The notFoundRedirect parameter value.
     *
     * @return void
     */
    public static function setNotFoundRedirect($value): void
    {
        self::isOfType(
            'string', $value,
            self::INVALID_NOT_FOUND_REDIRECT_TYPE
        );
        self::$notFoundRedirect = $value;
    }

    /**
     * Returns the notFoundRedirect parameter.
     *
     * @return null|string
     */
    public static function getNotFoundRedirect(): ?string
    {
        return self::$notFoundRedirect;
    }

    /**
     * Sets the output parameter. The value needs to be view, json, file or text. It can be null
     * also.
     *
     * @param  string $value                        The output parameter value.
     *
     * @return void
     */
    public static function setOutput($value): void
    {
        if ($value !== null) {
            if (in_array($value, ['view', 'json', 'file', 'text'])) {
                self::$output = $value;
            } else {
                throw new Exception(
                    self::INVALID_OUTPUT[1],
                    self::INVALID_OUTPUT[0],
                    [
                        var_export($value, true)
                    ]
                );
            }
        }
    }

    /**
     * Returns the output parameter.
     *
     * @return null|string
     */
    public static function getOutput(): ?string
    {
        return self::$output;
    }

    /**
     * Sets the browserCache parameter. This is an array with specific key value types. The first
     * key need to be an integer and not null, while the second key is optional, but if it is set it
     * needs to be an string and cannot be null.
     *
     * @param  array $value                        The browserCache parameter array values.
     *
     * @return void
     */
    public static function setBrowserCache($values): void
    {
        self::isOfType(
            'array', $values,
            self::INVALID_BROWSER_CACHE_TYPE
        );

        if($values !== null) {
            if (array_key_exists(0, $values)) {
                self::isOfTypeNotNull(
                    'integer', $values[0],
                    self::INVALID_BROWSER_CACHE_TIME_TYPE,
                    self::INVALID_BROWSER_CACHE_TIME_TYPE
                );
            }
    
            if (array_key_exists(1, $values)) {
                self::isOfTypeNotNull(
                    'string', $values[1],
                    self::INVALID_BROWSER_CACHE_HEADER_TYPE,
                    self::INVALID_BROWSER_CACHE_HEADER_TYPE
                );
            }
        }

        self::$browserCache = $values;
    }

    /**
     * Returns the browserCache parameter.
     *
     * @return array|null
     */
    public static function getBrowserCache(): ?array
    {
        return self::$browserCache;
    }

    /**
     * Sets the templateFile parameter. The value needs to be string or null.
     *
     * @param  null|string $value                   The templateFile parameter value.
     *
     * @return void
     */
    public static function setTemplateFile($value): void
    {
        self::isOfType(
            'string', $value,
            self::INVALID_TEMPLATE_FILE_TYPE
        );
        self::$templateFile = $value;
    }

    /**
     * Returns the templateFile parameter.
     *
     * @return null|string
     */
    public static function getTemplateFile(): ?string
    {
        return self::$templateFile;
    }

    /**
     * Sets the baseFolder parameter. The value needs to be string or null.
     *
     * @param  null|string $value                   The baseFolder parameter value.
     *
     * @return void
     */
    public static function setBaseFolder($value): void
    {
        self::isOfType(
            'string', $value,
            self::INVALID_BASE_FOLDER_TYPE
        );
        self::$baseFolder = $value;
    }

    /**
     * Returns the baseFolder parameter.
     *
     * @return null|string
     */
    public static function getBaseFolder(): ?string
    {
        return self::$baseFolder;
    }

    /**
     * Sets the offlineMessage parameter. The value needs to be string or null.
     *
     * @param  null|string $value                   The offlineMessage parameter value.
     *
     * @return void
     */
    public static function setOfflineMessage($value): void
    {
        self::isOfType(
            'string', $value,
            self::INVALID_OFFLINE_MESSAGE_TYPE
        );
        self::$offlineMessage = $value ?? self::DEFAULT_OFFLINE_MESSAGE[1];
    }

    /**
     * Returns the offlineMessage parameter.
     *
     * @return string
     */
    public static function getOfflineMessage(): string
    {
        return self::$offlineMessage;
    }

    /**
     * Sets the authFailMessage parameter. The value needs to be string or null.
     *
     * @param  null|string $value                   The authFailMessage parameter value.
     *
     * @return void
     */
    public static function setAuthFailMessage($value): void
    {
        self::isOfType(
            'string', $value,
            self::INVALID_AUTH_FAIL_MESSAGE_TYPE
        );
        self::$authFailMessage = $value ?? self::DEFAULT_AUTH_FAIL_MESSAGE[1];
    }

    /**
     * Returns the authFailMessage parameter.
     *
     * @return string
     */
    public static function getAuthFailMessage(): string
    {
        return self::$authFailMessage;
    }

    /**
     * Sets the permissionFailMessage parameter. The value needs to be string or null.
     *
     * @param  null|string $value                   The permissionFailMessage parameter value.
     *
     * @return void
     */
    public static function setPermissionFailMessage($value): void
    {
        self::isOfType(
            'string', $value,
            self::INVALID_PERMISSION_FAIL_MESSAGE_TYPE
        );
        self::$permissionFailMessage = $value ?? self::DEFAULT_PERMISSION_FAIL_MESSAGE[1];
    }

    /**
     * Returns the permissionFailMessage parameter.
     *
     * @return string
     */
    public static function getPermissionFailMessage(): string
    {
        return self::$permissionFailMessage;
    }

    /**
     * Sets the ignoreMimeType parameter. The value needs to be string or null.
     *
     * @param  bool|null $value                     The ignoreMimeType parameter value.
     *
     * @return void
     */
    public static function setIgnoreMimeType($value): void
    {
        self::isOfType(
            'boolean', $value,
            self::INVALID_VIEW_PATH_TYPE
        );
        self::$ignoreMimeType = $value;
    }

    /**
     * Returns the ignoreMimeType parameter.
     *
     * @return bool|null
     */
    public static function getIgnoreMimeType(): ?bool
    {
        return self::$ignoreMimeType;
    }

    /**
     * Sets the templateEngine parameter. The value needs to be php, twig, blade.
     *
     * @param  string $value                        The output parameter value.
     *
     * @return void
     */
    public static function setTemplateEngine($values): void
    {
        self::isOfTypeNotNull(
            ['array', 'string'], $values,
            self::VIEW_INVALID_TEMPLATE_ENGINE_VALUE_TYPE,
            self::VIEW_UNDEFINED_TEMPLATE_ENGINE_VALUE_TYPE,
        );

        if (gettype($values) !== 'array') {
            $values = [$values];
        }

        if (array_key_exists(0, $values)) {
            $templateEngines = ['php', 'twig', 'blade', 'latte'];

            self::isOfTypeNotNull(
                'string', $values[0],
                self::VIEW_INVALID_TEMPLATE_ENGINE_NAME_TYPE,
                self::VIEW_UNDEFINED_TEMPLATE_ENGINE_NAME,
            );

            if (!in_array($values[0], $templateEngines)) {
                throw new Exception(
                    self::VIEW_INVALID_TEMPLATE_ENGINE[1],
                    self::VIEW_INVALID_TEMPLATE_ENGINE[0],
                    [
                        strtoupper($values[0]),
                        implode(', ', $templateEngines)
                    ]
                );
            }
        }

        if (array_key_exists(1, $values)) {
            self::isOfTypeNotNull(
                'array', $values[1],
                self::VIEW_INVALID_TEMPLATE_ENGINE_OPTIONS_TYPE,
                self::VIEW_UNDEFINED_TEMPLATE_ENGINE_OPTIONS,
                [
                    implode(', ', $templateEngines)
                ]
            );
        }

        self::$templateEngine = $values;
    }

    /**
     * Returns the templateEngine parameter.
     *
     * @return null|array
     */
    public static function getTemplateEngine(): ?array
    {
        return self::$templateEngine;
    }



    //////

    /**
     * Child parameters.
     */

    /**
     * Sets the downloadable parameter. The value needs to be bool or null.
     *
     * @param  bool|null $value                     The downloadable parameter value.
     *
     * @return void
     */
    public static function setDownloadable($value): void
    {
        self::isOfType(
            'boolean', $value,
            self::INVALID_DOWNLOADABLE_TYPE
        );
        self::$downloadable = $value;
    }

    /**
     * Returns the downloadable parameter.
     *
     * @return bool|null
     */
    public static function getDownloadable(): ?bool
    {
        return self::$downloadable;
    }

    /**
     * Sets the downloadable parameter. The value needs to be bool or null.
     *
     * @param  bool|null $value                     The downloadable parameter value.
     *
     * @return void
     */
    public static function setAllowedExtensions($values): void
    {
        self::isOfType(
            'array', $values,
            self::INVALID_ALLOWED_EXTENSIONS_TYPE
        );

        foreach ($values ?? [] as $value) {
            self::isOfTypeNotNull(
                'string', $value,
                self::INVALID_ALLOWED_EXTENSION_VALUE_TYPE,
                self::INVALID_ALLOWED_EXTENSION_VALUE_TYPE
            );
        }

        self::$allowedExtensions = $values;
    }

    /**
     * Returns the allowedExtensions parameter.
     *
     * @return array|null
     */
    public static function getAllowedExtensions(): ?array
    {
        return self::$allowedExtensions;
    }

    /**
     * Sets the viewPath parameter. The value needs to be string or null.
     *
     * @param  null|string $value                   The viewPath parameter value.
     *
     * @return void
     */
    public static function setViewPath($value): void
    {
        self::isOfType(
            'string',
            $value,
            self::INVALID_VIEW_PATH_TYPE
        );
        self::$viewPath = $value;
    }

    /**
     * Returns the viewPath parameter.
     *
     * @return null|string
     */
    public static function getViewPath(): ?string
    {
        return self::$viewPath;
    }

    /**
     * Sets the request parameter. The value needs to be string or null.
     *
     * @param  null|string $value                   The request parameter value.
     *
     * @return void
     */
    public static function setRequest($value): void
    {
        self::$request = $value;
    }

    /**
     * Returns the request parameter.
     *
     * @return null|string
     */
    public static function getRequest(): ?string
    {
        return self::$request;
    }

    /**
     * Sets the parameters parameter. The value needs to be string or null.
     *
     * @param  array $value                         The parameters parameter value.
     *
     * @return void
     */
    public static function setUrlParameters($value): void
    {
        self::$urlParameters = $value;
    }

    /**
     * Returns all the parameters of the URL.
     *
     * @return array|null
     */
    public static function getUrlParameters(): ?array
    {
        return self::$urlParameters;
    }

    /**
     * Returns a specific parameter tag from the parameters of the URL. If the tag doesn't exist, it
     * return null.
     *
     * @return null|string
     */
    public static function getUrlParameter($tag): ?string
    {
        return self::$urlParameters[$tag] ?? null;
    }




    //////

    /**
     * Debug parameters.
     */

    /**
     * Sets the displayErrors parameter. The value needs to be boolean and cannot be null.
     *
     * @param  string $value                        The displayErrors parameter value.
     *
     * @return void
     */
    public static function setDisplayErrors($value): void
    {
        self::isOfTypeNotNull(
            'boolean', $value,
            self::INVALID_DISPLAY_ERRORS_TYPE,
            self::UNDEFINED_DISPLAY_ERRORS
        );
        self::$displayErrors = $value;
    }

    /**
     * Returns the displayErrors parameter.
     *
     * @return bool
     */
    public static function getDisplayErrors(): bool
    {
        return self::$displayErrors;
    }

    /**
     * Sets the showBacklogData parameter. The value needs to be boolean and cannot be null.
     *
     * @param  string $value                        The showBacklogData parameter value.
     *
     * @return void
     */
    public static function setShowBacklogData($value): void
    {
        self::isOfTypeNotNull(
            'boolean', $value,
            self::INVALID_SHOW_BACKLOG_DATA_TYPE,
            self::UNDEFINED_SHOW_BACKLOG_DATA
        );
        self::$showBacklogData = $value;
    }

    /**
     * Returns the showBacklogData parameter.
     *
     * @return bool
     */
    public static function getShowBacklogData(): bool
    {
        return self::$showBacklogData;
    }

    /**
     * Sets the performanceAnalysis parameter. The value needs to be boolean and cannot be null.
     *
     * @param  string $value                        The performanceAnalysis parameter value.
     *
     * @return void
     */
    public static function setPerformanceAnalysis($value): void
    {
        self::isOfTypeNotNull(
            'boolean', $value,
            self::INVALID_PERFORMANCE_ANALYSIS_TYPE,
            self::UNDEFINED_PERFORMANCE_ANALYSIS
        );
        self::$performanceAnalysis = $value;
    }

    /**
     * Returns the performanceAnalysis parameter.
     *
     * @return bool
     */
    public static function getPerformanceAnalysis(): bool
    {
        return self::$performanceAnalysis;
    }

    /**
     * Sets the language parameter. The value needs to be string and cannot be null. If the given
     * language file doesn't exist in the directory, an exception is thrown.
     *
     * @param  string $value                        The language parameter value.
     *
     * @return void
     */
    public static function setLanguage($value): void
    {
        self::isOfTypeNotNull(
            'string', $value,
            self::INVALID_LANGUAGE_TYPE,
            self::UNDEFINED_LANGUAGE
        );

        if (!is_file(self::LANGUAGE_DIRECTORY.'/'.$value.'.php')) {
            throw new Exception(
                self::LANGUAGE_FILE_NOT_FOUND[1],
                self::LANGUAGE_FILE_NOT_FOUND[0],
                [
                    $value
                ]
            );
        }
        self::$language = $value;
    }

    /**
     * Returns the language parameter.
     *
     * @return string
     */
    public static function getLanguage(): string
    {
        return self::$language;
    }

    /**
     * Internal method the checks if the value type of an variable is the one defined in the $type
     * parameter. When invalid, it throws an exception with a message and code set in the
     * $whenInvalid parameter array, where the key 0 is the code and the key 1 is the message. It
     * allows null values.
     *
     * @param  string|array $types                  The types that the variable is expected to be.
     *
     * @param  mixed $value                         The variable that will be tested.
     *
     * @param  array $whenInvalid                   An array, where the key 0 is the code and the
     *                                              key 1 is the message.
     *
     * @return void
     */
    private static function isOfType(/*string|array*/ $types, $value, array $whenInvalid, array $printfValues = []): void
    {
        if (gettype($types) !== 'array') {
            $types = [$types];
        }

        foreach($types as $type) {
            if ($value === null or gettype($value) === $type) {
                return;
            }
        }

        throw new Exception(
            $whenInvalid[1],
            $whenInvalid[0],
            $printfValues
        );
    }

    /**
     * Internal method the checks if the value type of an variable is the one defined in the $type
     * parameter. When invalid, it throws an exception with a message and code set in the
     * $whenInvalid parameter array, where the key 0 is the code and the key 1 is the message. It
     * doesn't allow null values and will return an exception if it is the case following the same
     * format of the $whenInvalid parameter.
     *
     * @param  string|array $types                  The types that the variable is expected to be.
     *
     * @param  mixed $value                         The variable that will be tested.
     *
     * @param  array $whenInvalid                   An array, where the key 0 is the code and the
     *                                              key 1 is the message.
     *
     * @param  array $whenUndefined                 An array, where the key 0 is the code and the
     *                                              key 1 is the message.
     *
     * @return void
     */
    private static function isOfTypeNotNull(/*string|array*/$types, $value, array $whenInvalid, array $whenUndefined, array $printfValues = []): void
    {
        if (gettype($types) !== 'array') {
            $types = [$types];
        }

        foreach($types as $type) {
            if ($value !== null) {
                if (gettype($value) === $type) {
                    return;
                }
            } else {
                throw new Exception(
                    $whenUndefined[1],
                    $whenUndefined[0],
                    $printfValues
                );
            }
        }

        throw new Exception(
            $whenInvalid[1],
            $whenInvalid[0],
            $printfValues
        );
    }
}
