<?php

namespace galastri\core;

use galastri\extensions\Exception;

final class Parameters implements \galastri\lang\English
{
    const LANGUAGE_DIRECTORY = GALASTRI_PROJECT_DIR.'/galastri/lang';

    private static string $urlRoot;
    private static ?string $timezone = null;
    private static bool $offline;
    private static string $offlineMessage;
    private static ?string $forceRedirect = null;
    private static ?string $output = null;
    private static ?string $notFoundRedirect = null;
    private static ?string $controller = null;
    private static ?string $namespace = null;

    private static ?string $projectTitle = null;
    private static ?string $pageTitle = null;
    private static ?string $authTag = null;
    private static ?string $authFailRedirect = null;
    private static ?string $viewTemplateFile = null;
    private static ?string $viewBaseFolder = null;

    private static ?bool $fileDownloadable = null;
    private static ?string $fileBaseFolder = null;
    private static ?string $viewFilePath = null;

    private static ?string $requestMethod = null;

    private static bool $displayErrors;
    private static bool $showBacklogData;
    private static bool $performanceAnalysis;
    private static string $language = 'English';

    private function __construct()
    {
    }

    public static function setUrlRoot($value): void
    {
        self::isStringNotNull($value, self::INVALID_URL_ROOT_TYPE, self::UNDEFINED_URL_ROOT);
        self::$urlRoot = $value;
    }

    public static function getUrlRoot(): string
    {
        return self::$urlRoot;
    }

    public static function setDisplayErrors($value): void
    {
        self::isBoolNotNull($value, self::INVALID_DISPLAY_ERRORS_TYPE, self::UNDEFINED_DISPLAY_ERRORS);
        self::$displayErrors = $value;
    }

    public static function getDisplayErrors(): bool
    {
        return self::$displayErrors;
    }

    public static function setShowBacklogData($value): void
    {
        self::isBoolNotNull($value, self::INVALID_SHOW_BACKLOG_DATA_TYPE, self::UNDEFINED_SHOW_BACKLOG_DATA);
        self::$showBacklogData = $value;
    }

    public static function getShowBacklogData(): bool
    {
        return self::$showBacklogData;
    }

    public static function setPerformanceAnalysis($value): void
    {
        self::isBoolNotNull($value, self::INVALID_PERFORMANCE_ANALYSIS_TYPE, self::UNDEFINED_PERFORMANCE_ANALYSIS );
        self::$performanceAnalysis = $value;
    }

    public static function getPerformanceAnalysis(): bool
    {
        return self::$performanceAnalysis;
    }

    public static function setLanguage($value): void
    {
        self::isStringNotNull($value, self::INVALID_LANGUAGE_TYPE, self::UNDEFINED_LANGUAGE );

        if (!is_file(self::LANGUAGE_DIRECTORY.'/'.$value.'.php')) {
            throw new Exception("An invalid language '%s' was set in the debug configuration. Language file doesn't exist.", 'G0026', [$value]);
        }
        self::$language = $value;
    }

    public static function getLanguage(): string
    {
        return self::$language;
    }

    public static function setTimezone($value): void
    {
        self::isString($value, self::INVALID_TIMEZONE_TYPE);
        self::$timezone = $value;

        if($value !== null) {
            date_default_timezone_set($value);
        }
    }

    public static function getTimezone(): ?string
    {
        return self::$timezone;
    }

    public static function setOffline($value): void
    {
        self::isBoolNotNull($value, self::INVALID_OFFLINE_TYPE, self::UNDEFINED_OFFLINE);
        self::$offline = $value;
    }

    public static function getOffline(): bool
    {
        return self::$offline;
    }

    public static function setOfflineMessage($value): void
    {
        self::isString($value, self::INVALID_OFFLINE_MESSAGE_TYPE);
        self::$offlineMessage = $value ?? self::DEFAULT_OFFLINE_MESSAGE[1];
    }

    public static function getOfflineMessage(): string
    {
        return self::$offlineMessage;
    }

    public static function setForceRedirect($value): void
    {
        self::isString($value, self::INVALID_FORCE_REDIRECT_TYPE);
        self::$forceRedirect = $value;
    }

    public static function getForceRedirect(): ?string
    {
        return self::$forceRedirect;
    }

    public static function setNotFoundRedirect($value): void
    {
        self::isString($value, self::INVALID_NOT_FOUND_REDIRECT_TYPE);
        self::$notFoundRedirect = $value;
    }

    public static function getNotFoundRedirect(): ?string
    {
        return self::$notFoundRedirect;
    }

    public static function setOutput($value): void
    {
        if ($value !== null) {
            if (in_array($value, ['view', 'json', 'file', 'text'])) {
                self::$output = $value;
            } else {
                throw new Exception(self::INVALID_OUTPUT[1], self::INVALID_OUTPUT[0], [var_export($value, true)]);
            }
        }
    }

    public static function getOutput(): ?string
    {
        return self::$output;
    }

    public static function setController($value): void
    {
        self::isString($value, self::INVALID_CONTROLLER_TYPE);
        self::$controller = $value;
    }

    public static function getController(): ?string
    {
        return self::$controller;
    }

    public static function setNamespace($value): void
    {
        self::isString($value, self::INVALID_NAMESPACE_TYPE);
        self::$namespace = $value;
    }

    public static function getNamespace(): ?string
    {
        return self::$namespace;
    }

    public static function setProjectTitle($value): void
    {
        self::isString($value, self::INVALID_PROJECT_TITLE_TYPE);
        self::$projectTitle = $value;
    }

    public static function getProjectTitle(): ?string
    {
        return self::$projectTitle;
    }

    public static function setPageTitle($value): void
    {
        self::isString($value, self::INVALID_PAGE_TITLE_TYPE);
        self::$pageTitle = $value;
    }

    public static function getPageTitle(): ?string
    {
        return self::$pageTitle;
    }

    public static function setAuthTag($value): void
    {
        self::isString($value, self::INVALID_AUTH_TAGE_TYPE);
        self::$authTag = $value;
    }

    public static function getAuthTag(): ?string
    {
        return self::$authTag;
    }

    public static function setAuthFailRedirect($value): void
    {
        self::isString($value, self::INVALID_AUTH_FAIL_REDIRECT_TYPE);
        self::$authFailRedirect = $value;
    }

    public static function getAuthFailRedirect(): ?string
    {
        return self::$authFailRedirect;
    }

    public static function setViewTemplateFile($value): void
    {
        self::isString($value, self::INVALID_VIEW_TEMPLATE_FILE_TYPE);
        self::$viewTemplateFile = $value;
    }

    public static function getViewTemplateFile(): ?string
    {
        return self::$viewTemplateFile;
    }

    public static function setViewBaseFolder($value): void
    {
        self::isString($value, self::INVALID_VIEW_BASE_FOLDER_TYPE);
        self::$viewBaseFolder = $value;
    }

    public static function getViewBaseFolder(): ?string
    {
        return self::$viewBaseFolder;
    }

    public static function setFileDownloadable($value): void
    {
        self::isBool($value, self::INVALID_FILE_DOWNLOADABLE_TYPE);
        self::$fileDownloadable = $value;
    }

    public static function getFileDownloadable(): ?string
    {
        return self::$fileDownloadable;
    }

    public static function setFileBaseFolder($value): void
    {
        self::isString($value, self::INVALID_FILE_BASE_FOLDER_TYPE);
        self::$fileBaseFolder = $value;
    }

    public static function getFileBaseFolder(): ?string
    {
        return self::$fileBaseFolder;
    }

    public static function setViewFilePath($value): void
    {
        self::isString($value, self::INVALID_VIEW_FILE_PATH_TYPE);
        self::$viewFilePath = $value;
    }

    public static function getViewFilePath(): ?string
    {
        return self::$viewFilePath;
    }

    public static function setRequestMethod($value): void
    {
        self::$requestMethod = $value;
    }

    public static function getRequestMethod(): ?string
    {
        return self::$requestMethod;
    }

    private static function isString($value, $whenInvalid)
    {
        if ($value !== null and gettype($value) !== 'string') {
            throw new Exception($whenInvalid[1], $whenInvalid[0]);
        }
    }

    private static function isStringNotNull($value, $whenInvalid, $whenUndefined)
    {
        if ($value !== null) {
            if (gettype($value) !== 'string') {
                throw new Exception($whenInvalid[1], $whenInvalid[0]);
            }
        } else {
            throw new Exception($whenUndefined[1], $whenUndefined[0]);
        }
    }

    private static function isBool($value, $whenInvalid)
    {
        if ($value !== null and gettype($value) !== 'boolean') {
            throw new Exception($whenInvalid[1], $whenInvalid[0]);
        }
    }

    private static function isBoolNotNull($value, $whenInvalid, $whenUndefined)
    {
        if ($value !== null) {
            if (gettype($value) !== 'boolean') {
                throw new Exception($whenInvalid[1], $whenInvalid[0]);
            }
        } else {
            throw new Exception($whenUndefined[1], $whenUndefined[0]);
        }
    }
}
