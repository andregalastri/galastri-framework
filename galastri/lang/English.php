<?php

namespace galastri\lang;

/**
 * This interface stores the various messages in English language. It is dynamically implemented in
 * many classes based on the debug configuration 'language' parameter.
 */
interface English
{
    /**
     * Constants used in \galastri\core\Debug.
     */
    const GENERIC_MESSAGE = "An error occurred. Please, contact the administrator.";

    /**
     * Constants used in \galastri\core\Galastri.
     */
    const DEFAULT_OFFLINE_MESSAGE = [
        'G0000', "This area is currently offline. Please, try again later."
    ];

    const DEFAULT_AUTH_FAIL_MESSAGE = [
        'G0025', "You aren't authorized to access this area."
    ];

    const DEFAULT_PERMISSION_FAIL_MESSAGE = [
        'G0025', "You don't have permission to execute this action."
    ];


    const ERROR_404 = [
        'G0003', "Error 404: The requested route was not found."
    ];

    const CONTROLLER_NOT_FOUND = [
        'G0004', "Requested controller '%s' doesn't exist. Check if the file '%s.php' exists in directory '%s' or if its namespace was correctly set."
    ];

    const CONTROLLER_DOESNT_EXTENDS_CORE = [
        'G0005', "Controller '%s' is not extending the core class \galastri\core\Controller. Add the core class to your controller class."
    ];

    const CONTROLLER_METHOD_NOT_FOUND = [
        'G0006', "Controller '%s' doesn't have the requested method '@%s'."
    ];

    const VALIDATION_ERROR = [
        'G0007', "The validation '%s' was returned as invalid. The execution cannot proceed."
    ];

    /**
     * Constants used in \galastri\core\Route.
     */
    const INVALID_PARAM_TYPE = [
        'G0008', "Invalid parameter configuration. Parameter '%s' needs to be '%s'. '%s' given."
    ];

    const REQUEST_METHOD_STARTS_WITH_AT = [
        'G0009', "Request method '%s' need to start with @ as the first character"
    ];

    const INVALID_REQUEST_METHOD_NAME = [
        'G0010', "Request method '%s' has an invalid name."
    ];

    /**
     * Constants used in \galastri\extensions\ViewOutputData.
     */
    const VIEW_INVALID_DATA_KEY = [
        'G0011', "Key '%s' doesn't exist in the data returned by controller."
    ];

    /**
     * Constants used in \galastri\modules\types\traits\FilePath.
     */
    const EMPTY_FILE_PATH = [
        'G0012', "The path parameter is empty in method '%s'"
    ];

    /**
     * Constants used in \galastri\types\ Type* files.
     */
    const TYPE_DEFAULT_INVALID_MESSAGE = [
        'G0014', "Wrong data type. Expecting '%s', but '%s' was given."
    ];

    /**
     * Constants used in \galastri\extensions\output\View
     */
    const UNDEFINED_TEMPLATE_FILE = [
        'G0015', "No template file set to this route. Set a default template in project or route configuration."
    ];

    const TEMPLATE_FILE_NOT_FOUND = [
        'G0016', "Template file '%s' not found."
    ];

    const VIEW_FILE_NOT_FOUND = [
        'G0017', "View file '%s' not found."
    ];

    /**
     * Constants used in \galastri\extensions\typeValidation\StringValidation
     */
    const UNDEFINED_VALIDATION_ALLOWED_CHARSET = [
        'G0018', "Method 'allowedCharset()' requires one or more charsets defined to work. None was given."
    ];

    const UNDEFINED_VALIDATION_REQUIRED_CHARSET = [
        'G0019', "Method 'requiredChars()' needs one or more charsets defined to work. None was given."
    ];

    const VALIDATION_STRING_LOWER_CASE_ONLY = [
        'G0023', "Expecting only lower case chars."
    ];

    const VALIDATION_STRING_UPPER_CASE_ONLY = [
        'G0023', "Expecting only upper case chars."
    ];

    const VALIDATION_STRING_MIN_LENGTH = [
        'G0023', "Expecting '%s' minimum char length, but it contains '%s'."
    ];

    const VALIDATION_STRING_MAX_LENGTH = [
        'G0023', "Expecting '%s' maximum char length, but it contains '%s'."
    ];

    const VALIDATION_STRING_INVALID_CHARS = [
        'G0023', "The value cannot contain '%s' chars."
    ];

    const VALIDATION_STRING_REQUIRED_CHARS = [
        'G0023', "The value needs to contain '%s' of these chars '%s' but '%s' were informed."
    ];

    /**
     * Constants used in \galastri\modules\types\traits\Common
     */
    const TYPE_HISTORY_KEY_NOT_FOUND = [
        'G0020', "There is no key '%s' in the history of the type object."
    ];

    const TYPE_HISTORY_DISABLED = [
        'G0021', "Save history is disabled, there is no data to be reverted. If you want enable this, set to 'true' the second constructor parameter in the definition of this object of types."
    ];

    /**
     * Constants used in \galastri\modules\types\traits\RandomStringValue
     */
    const SECURE_RANDOM_GENERATOR_NOT_FOUND = [
        'G0022', "No cryptographically secure random string generation function available. You need to check your PHP configuration to make the 'random_bytes()' or 'openssl_random_pseudo_bytes()' functions available."
    ];

    /**
     * Constants used in \galastri\extensions\typeValidation\NumericValidation
     */
    const VALIDATION_NUMERIC_MIN_VALUE = [
        'G0023', "Expecting minimum value '%s' but '%s' were given."
    ];

    const VALIDATION_NUMERIC_MAX_VALUE = [
        'G0023', "Expecting maximum value '%s' but '%s' were given."
    ];

    /**
     * Constants used in \galastri\extensions\typeValidation\traits\AllowedValueList
     */
    const VALIDATION_UNDEFINED_VALUES_ALLOWED_LIST = [
        'G0023', "It is required to define at least one value in 'allowedValues' method."
    ];

    const VALIDATION_NO_VALUE_IN_ALLOWED_LIST = [
        'G0023', "The value (%s) is not an allowed value. The allowed values are [%s]."
    ];

    const VALIDATION_UNDEFINED_VALUES_DENIED_LIST = [
        'G0023', "It is required to define at least one value in 'deniedValues' method."
    ];

    const VALIDATION_VALUE_IN_DENIED_LIST = [
        'G0023', "The value (%s) is not an allowed value. The values that aren't allowed are [%s]."
    ];

    /**
     * Constants used in \galastri\core\Config
     */
    const INVALID_KEY_PARAMETER_TYPE = [
        'G0026', "Wrong key type. There is a node in this route whose key was set as %s. Check the route configuration file and define any non-string keys to string type."
    ];

    const INVALID_TIMEZONE_TYPE = [
        'G0026', "Wrong value type. Type of route parameter 'timezone' need to be 'string' or 'null'."
    ];

    const INVALID_OFFLINE_TYPE = [
        'G0026', "Wrong value type. Type of route parameter 'offline' need to be 'bool'."
    ];

    const UNDEFINED_OFFLINE = [
        'G0026', "Undefined route parameter 'offline'. Set it in the project or route configuration files."
    ];

    const INVALID_OFFLINE_MESSAGE_TYPE = [
        'G0026', "Wrong value type. Type of route parameter 'offlineMessage' need to be 'string'."
    ];

    const INVALID_FORCE_REDIRECT_TYPE = [
        'G0026', "Wrong value type. Type of route parameter 'forceRedirect' need to be 'string' or 'null'."
    ];

    const INVALID_OUTPUT = [
        'G0026', "Output %s doesn't exist. The existing outputs are 'view', 'json', 'file' and 'text'."
    ];

    const UNDEFINED_OUTPUT = [
        'G0026', "Undefined route parameter 'output' to this route. Set it in the route configuration file."
    ];

    const INVALID_NOT_FOUND_REDIRECT_TYPE = [
        'G0026', "Wrong value type. Type of route parameter 'notFoundRedirect' need to be 'string' or 'null'."
    ];

    const INVALID_CONTROLLER_TYPE = [
        'G0026', "Wrong value type. Type of parent node parameter 'controller' need to be 'string' or 'null'."
    ];

    const INVALID_NAMESPACE_TYPE = [
        'G0026', "Wrong value type. Type of route parameter 'namespace' need to be 'string' or 'null'."
    ];

    const INVALID_PROJECT_TITLE_TYPE = [
        'G0026', "Wrong value type. Type of route parameter 'projectTitle' need to be 'string' or 'null'."
    ];

    const INVALID_PAGE_TITLE_TYPE = [
        'G0026', "Wrong value type. Type of route parameter 'pageTitle' need to be 'string' or 'null'."
    ];

    const INVALID_AUTH_TAGE_TYPE = [
        'G0026', "Wrong value type. Type of route parameter 'authTag' need to be 'string' or 'null'."
    ];

    const INVALID_AUTH_FAIL_REDIRECT_TYPE = [
        'G0026', "Wrong value type. Type of route parameter 'authFailRedirect' need to be 'string' or 'null'."
    ];

    const INVALID_VIEW_TEMPLATE_FILE_TYPE = [
        'G0026', "Wrong value type. Type of route parameter 'viewTemplateFile' need to be 'string' or 'null'."
    ];

    const INVALID_VIEW_BASE_FOLDER_TYPE = [
        'G0026', "Wrong value type. Type of route parameter 'viewBaseFolder' need to be 'string' or 'null'."
    ];

    const INVALID_FILE_DOWNLOADABLE_TYPE = [
        'G0026', "Wrong value type. Type of route parameter 'fileDownloadable' need to be 'bool' or 'null'."
    ];

    const INVALID_FILE_BASE_FOLDER_TYPE = [
        'G0026', "Wrong value type. Type of route parameter 'fileBaseFolder' need to be 'string' or 'null'."
    ];

    const INVALID_VIEW_FILE_PATH_TYPE = [
        'G0026', "Wrong value type. Type of route parameter 'viewFilePath' need to be 'string' or 'null'."
    ];

    const INVALID_URL_ROOT_TYPE = [
        'G0026', "Wrong value type. Type of project parameter 'urlRoot' need to be 'string'."
    ];

    const UNDEFINED_URL_ROOT = [
        'G0026', "Undefined project parameter 'urlRoot' to this route. Set it in the route configuration file."
    ];

    const INVALID_DISPLAY_ERRORS_TYPE = [
        'G0026', "Wrong value type. Type of debug parameter 'displayErrors' need to be 'bool'."
    ];

    const UNDEFINED_DISPLAY_ERRORS = [
        'G0026', "Undefined debug parameter 'displayErrors' to this route. Set it in the route configuration file."
    ];

    const INVALID_SHOW_BACKLOG_DATA_TYPE = [
        'G0026', "Wrong value type. Type of debug parameter 'showBacklogData' need to be 'bool'."
    ];

    const UNDEFINED_SHOW_BACKLOG_DATA = [
        'G0026', "Undefined debug parameter 'showBacklogData' to this route. Set it in the route configuration file."
    ];

    const INVALID_PERFORMANCE_ANALYSIS_TYPE = [
        'G0026', "Wrong value type. Type of debug parameter 'performanceAnalysis' need to be 'bool'."
    ];

    const UNDEFINED_PERFORMANCE_ANALYSIS = [
        'G0026', "Undefined route debug 'performanceAnalysis' to this route. Set it in the route configuration file."
    ];

    const INVALID_LANGUAGE_TYPE = [
        'G0026', "Wrong value type. Type of debug parameter 'language' need to be 'string'."
    ];

    const UNDEFINED_LANGUAGE = [
        'G0026', "Undefined debug parameter 'language' to this route. Set it in the route configuration file."
    ];

    const INVALID_LOCATION_DATA_TYPE = [
        'G0027', "Wrong value type. Location URL or URL Tag value need to be 'string' and cannot be empty."
    ];

    /**
     * Constants used in \galastri\modules\types\traits\Math
     */
    const MATH_ROOT_CANNOT_BE_ZERO = [
        'G0024', "The root() method cannot have the degree of root equals to zero."
    ];
}
