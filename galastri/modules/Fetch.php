<?php

namespace galastri\modules;

use \galastri\core\Debug;

/**
 * This method return data from the request (via POST, GET, etc). This method purpose is to make
 * possible to receive any data from the request, even from JS promises.
 */
class Fetch
{
    /**
     * This method return the value from the request, from any request method type.
     *
     * @param  string $key                          Key name from the request. If the key doesn't
     *                                              exist it return null.
     *
     * @return bool|int|null|string
     */
    public static function key(string $key)// : bool|int|string
    {
        Debug::setBacklog();

        /**
         * The request method is stored.
         */
        $requestMethod = mb_strtolower($_SERVER['REQUEST_METHOD']);

        /**
         * Based on the request method, the data received from the request is stored. If the request
         * method if GET or POST, it gets the data from the $_GET and $_POST globals. If not, the
         * data is get directly from the php://input file.
         */
        switch ($requestMethod) {
            case 'post': $data = $_POST; break;
            case 'get': $data = $_GET; break;
        }

        $data = empty($data) ? json_decode(file_get_contents('php://input'), true) : $data;

        /**
         * Return the data key or null if the key doesn't exist.
         */
        return $data[$key] ?? null;
    }
}
