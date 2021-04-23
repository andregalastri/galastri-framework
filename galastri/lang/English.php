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
    const OFFLINE = [
        'G0000', ""
    ];

    const UNDEFINED_OUTPUT = [
        'G0001', "There is no parameter 'output' defined to this route. Configure it in the '\app\config\routes.php'."
    ];

    const INVALID_OUTPUT = [
        'G0002', "Invalid output '%s'. These are the only valid output: view, json, file or text."
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
     * Constants used in \galastri\modules\Toolbox.
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
        'G0023', "Excepting the value to contain only lower case chars."
    ];

    const VALIDATION_STRING_UPPER_CASE_ONLY = [
        'G0023', "Excepting the value to contain only upper case chars."
    ];

    const VALIDATION_STRING_MIN_LENGTH = [
        'G0023', "Excepting the value to contain '%s' minimum char length, but it contains '%s'."
    ];

    const VALIDATION_STRING_MAX_LENGTH = [
        'G0023', "Excepting the value to contain '%s' maximum char length, but it contains '%s'."
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
     * Constants used in \galastri\modules\types\traits\Math
     */
    const MATH_ROOT_CANNOT_BE_ZERO = [
        'G0024', "The root() method cannot have the degree of root equals to zero."
    ];
}
