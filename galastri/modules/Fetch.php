<?php

namespace galastri\modules;

use \galastri\core\Debug;

class Fetch
{
    public static function key(string $key)// : mixed
    {
        Debug::setBacklog();

        $requestMethod = mb_strtolower($_SERVER['REQUEST_METHOD']);

        switch ($requestMethod) {
            case 'post': $data = $_POST; break;
            case 'get': $data = $_GET; break;
        }

        $data = $data ?? json_decode(file_get_contents('php://input'), true);

        return $data[$key] ?? null;
    }
}
