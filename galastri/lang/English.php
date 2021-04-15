<?php

namespace galastri\lang;

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
     * Constants used in \galastri\extensions\types\TraitCommon.
     */
    const VALIDATION_DEFAULT_INVALID_MESSAGE = [
        'G0013', "The value '%s' is not valid."
    ];
}
