<?php

namespace galastri\extensions\output;

use \galastri\core\Debug;

/**
 * This trait is the Json output, used by the Galastri class, to return a JSON to the request.
 *
 * This trait:
 * - Just get the data returned by the route controller, converts it into JSON format and returns it
 *   to the request.
 *
 * Every property and method name start with 'json' to prevent incompatibilities with other output
 * traits.
 */
trait Json
{
    /**
     * This is the main method of the Json output. It just converts the data returned by the route
     * controller to JSON format and prints it to return the JSON to the request.
     *
     * @return void
     */
    private static function json(): void
    {
        Debug::setBacklog();

        header('Content-Type: application/json');
        echo json_encode(self::$routeController->getResultData(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /**
     * This method is exclusively used by the Galastri class to determine if this output requires a
     * controller to work.
     *
     * @return bool
     */
    private static function jsonRequiresController(): bool
    {
        return true;
    }
}
