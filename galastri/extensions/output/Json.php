<?php

namespace galastri\extensions\output;

use \galastri\core\Debug;

/**
 * This is the Json output script that is used by \galastri\core\Galastri class to return a JSON
 * output to the request. It is a simple script, it gets the returning array from the route
 * controller, encode and return the result.
 */
trait Json
{    
    /**
     * Main method. It gets the returning array from the route controller, encode and return the
     * result.
     *
     * @return void
     */
    private static function json(): void
    {
        Debug::setBacklog();

        header('Content-Type: application/json');
        echo json_encode(self::$routeController->getResultData(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}
