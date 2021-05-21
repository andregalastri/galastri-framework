<?php

namespace galastri\lang;

interface English
{
    const GENERIC_MESSAGE = "An error occurred. Please, contact the administrator.";

    const INVALID_OFFLINE_MESSAGE_TYPE = [
        'G0026', "Wrong value type. Type of route parameter 'offlineMessage' need to be 'string'."
    ];

    const DEFAULT_OFFLINE_MESSAGE = [
        'G0000', "This area is currently offline. Please, try again later."
    ];

    const INVALID_AUTH_FAIL_MESSAGE_TYPE = [
        'G0026', "Wrong value type. Type of route parameter 'authFailMessage' need to be 'string'."
    ];

    const DEFAULT_AUTH_FAIL_MESSAGE = [
        'G0025', "You aren't authorized to access this area."
    ];

    const INVALID_PERMISSION_FAIL_MESSAGE_TYPE = [
        'G0026', "Wrong value type. Type of route parameter 'permissionFailMessage' need to be 'string'."
    ];

    const DEFAULT_PERMISSION_FAIL_MESSAGE = [
        'G0025', "You don't have permission to execute this action."
    ];

    const ERROR_404 = [
        'G0003', "Error 404: The requested route or file was not found."
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

    const INVALID_PARAM_TYPE = [
        'G0008', "Invalid parameter configuration. Parameter '%s' needs to be '%s'. '%s' given."
    ];

    const REQUEST_METHOD_STARTS_WITH_AT = [
        'G0009', "Request method '%s' need to start with @ as the first character"
    ];

    const INVALID_REQUEST_METHOD_NAME = [
        'G0010', "Request method '%s' has an invalid name."
    ];

    const VIEW_UNDEFINED_DATA_KEY = [
        'G0011', "Undefined data key to print."
    ];

    const VIEW_INVALID_PRINT_DATA = [
        'G0011', "Data cannot be of type 'array' of 'object'. Use 'data' method for these types of values."
    ];

    const EMPTY_FILE_PATH = [
        'G0012', "The path parameter is empty in method '%s'"
    ];

    const EMPTY_DIRECTORY_PATH = [
        'G0012', "The path parameter is empty in method '%s'"
    ];

    const TYPE_DEFAULT_INVALID_MESSAGE = [
        'G0014', "Wrong data type. Expecting '%s', but '%s' was given."
    ];

    const UNDEFINED_TEMPLATE_FILE = [
        'G0015', "No template file set to this route. Set a default template in project or route configuration."
    ];

    const UNDEFINED_BASE_FOLDER = [
        'G0015', "No base folder set to this file output. Set a 'baseFolder' parameter that points to the directory where the files are stored."
    ];

    const TEMPLATE_FILE_NOT_FOUND = [
        'G0016', "Template file '%s' not found."
    ];

    const UNDEFINED_EXTENSION_MIME_TYPE = [
        'G0016', "Undefined '%s' extension. Define it in the MIME type configuration file, setting the extension and its MIME type."
    ];

    const INVALID_MIME_TYPE_FOR_EXTENSION = [
        'G0016', "Invalid MIME type for file extension. Expecting MIME type to be '%s' for the '%s' extension, but '%s' was given."
    ];

    const UNDEFINED_FILE_PATH = [
        'G0016', "Undefined file path."
    ];

    const VIEW_FILE_NOT_FOUND = [
        'G0017', "View file '%s' not found."
    ];

    const UNDEFINED_AUTH_TAG = [
        'G0026', "No authentication tag defined. Define it as parameter of the method."
    ];

    const UNCONFIGURED_AUTH_TAG = [
        'G0026', "There is no authTag '%s' configured. Configure it using the 'configure' method before use the 'create' method."
    ];

    const UNDEFINED_VALIDATION_ALLOWED_CHARSET = [
        'G0018', "Method 'allowedCharset()' requires one or more charsets defined to work. None was given."
    ];

    const UNDEFINED_VALIDATION_REQUIRED_CHARSET = [
        'G0019', "Method 'requiredChars()' needs one or more charsets defined to work. None was given."
    ];

    const VALIDATION_CANNOT_BE_NULL = [
        'G0023', "The value cannot be null."
    ];

    const VALIDATION_CANNOT_BE_EMPTY = [
        'G0023', "The value cannot be empty."
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

    const TYPE_HISTORY_KEY_NOT_FOUND = [
        'G0020', "There is no key '%s' in the history of the type object."
    ];

    const TYPE_HISTORY_DISABLED = [
        'G0021', "Save history is disabled, there is no data to be reverted. If you want enable this, set to 'true' the second constructor parameter in the definition of this object of types."
    ];

    const SECURE_RANDOM_GENERATOR_NOT_FOUND = [
        'G0022', "No cryptographically secure random string generation function available. You need to check your PHP configuration to make the 'random_bytes()' or 'openssl_random_pseudo_bytes()' functions available."
    ];

    const VALIDATION_NUMERIC_MIN_VALUE = [
        'G0023', "Expecting minimum value '%s' but '%s' were given."
    ];

    const VALIDATION_NUMERIC_MAX_VALUE = [
        'G0023', "Expecting maximum value '%s' but '%s' were given."
    ];

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

    const INVALID_BROWSER_CACHE_TYPE = [
        'G0026', "Wrong value type. Type of route parameter 'browserCache' need to be 'array' or 'null'."
    ];

    const INVALID_BROWSER_CACHE_TIME_TYPE = [
        'G0026', "Wrong value type. The first key of route parameter 'browserCache' represents the cache time and need to be 'integer'."
    ];

    const INVALID_BROWSER_CACHE_HEADER_TYPE = [
        'G0026', "Wrong value type. The second key of route parameter 'browserCache' represents the Cache-Control headers and need to be 'string'."
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

    const INVALID_AUTH_TAG_TYPE = [
        'G0026', "Wrong value type. Type of route parameter 'authTag' need to be 'string' or 'null'."
    ];

    const INVALID_AUTH_FAIL_REDIRECT_TYPE = [
        'G0026', "Wrong value type. Type of route parameter 'authFailRedirect' need to be 'string' or 'null'."
    ];

    const INVALID_TEMPLATE_FILE_TYPE = [
        'G0026', "Wrong value type. Type of route parameter 'templateFile' need to be 'string' or 'null'."
    ];

    const INVALID_BASE_FOLDER_TYPE = [
        'G0026', "Wrong value type. Type of route parameter 'baseFolder' need to be 'string' or 'null'."
    ];

    const INVALID_DOWNLOADABLE_TYPE = [
        'G0026', "Wrong value type. Type of route parameter 'downloadable' need to be 'bool' or 'null'."
    ];

    const INVALID_ALLOWED_EXTENSIONS_TYPE = [
        'G0026', "Wrong value type. Type of route parameter 'allowedExtensions' need to be 'array' or 'null'."
    ];

    const INVALID_ALLOWED_EXTENSION_VALUE_TYPE = [
        'G0026', "Wrong value type. The values of route parameter 'allowedExtensions' need to be 'string'."
    ];

    const INVALID_VIEW_PATH_TYPE = [
        'G0026', "Wrong value type. Type of route parameter 'viewPath' need to be 'string' or 'null'."
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

    const LANGUAGE_FILE_NOT_FOUND = [
        'G0026', "The language '%s' is invalid. There is no such language file in the directory."
    ];

    const UNDEFINED_LANGUAGE = [
        'G0026', "Undefined debug parameter 'language' to this route. Set it in the route configuration file."
    ];

    const INVALID_LOCATION_DATA_TYPE = [
        'G0027', "Wrong value type. Location URL or URL Tag value need to be 'string' and cannot be empty."
    ];

    const PDO_QUERY_EXECUTION_FAIL = [
        'PDO0000', "Can't execute the query. PDO has returned the following error: '%s'."
    ];

    const PDO_CONNECTION_FAIL = [
        'PDO0000', "Can't connect to database. PDO has returned the following error: '%s'."
    ];

    const DATABASE_BIND_PARAMETER_TYPE = [
        'G0035', "Bind parameter #1 need to be string, int or an array."
    ];

    const DATABASE_CONNECTION_FAIL_UNDEFINED_PROPERTY = [
        'G0035', "Can't connect to database. Property '%s' was not configured."
    ];

    const DATABASE_UNINITIALIZED_CLASS = [
        'G0035', "Before execute any database method, execute the 'connect()' method."
    ];

    const DATABASE_UNAVAILABLE_EXPORT_METHOD = [
        'G0035', "Method 'export' isn't available to the '%s' class"
    ];

    const MATH_ROOT_CANNOT_BE_ZERO = [
        'G0024', "The root() method cannot have the degree of root equals to zero."
    ];
}
