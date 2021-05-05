<?php
namespace galastri\modules;

use \galastri\core\Parameters;
use \galastri\extensions\Exception;
use \galastri\modules\types\TypeArray;

class Permission implements \Language
{
    private static array $allowedList = [];
    private static bool $required = false;

    private function __construct()
    {
    }

    public static function allow(...$allowedList)// : self
    {
        $allowedList = (new TypeArray($allowedList))->flatten()->get();

        foreach ($allowedList as $allowed) {
            self::$allowedList[] = $allowed;
        }

        return __CLASS__;
    }


    public static function require(...$requiredList)// : self
    {
        self::$required = true;

        self::allow(...$requiredList);

        return __CLASS__;
    }

    public static function remove(...$removeList): void
    {
        $removeList = (new TypeArray($removeList))->flatten()->get();

        foreach ($removeList as $removeValue) {
            $keyList = array_keys(self::$allowedList, $removeValue);
            foreach ($keyList as $removeKey) {
                unset(self::$allowedList[$removeKey]);
            }
        }
    }

    public static function onFail(string $message)// : self
    {
        Parameters::setPermissionFailMessage($message);

        return __CLASS__;
    }

    public static function validate(...$permissions): void
    {
        $allowedList = self::$allowedList;
        $permissions = (new TypeArray($permissions))->flatten()->get();

        $validated = false;

        foreach ($allowedList as $allowed) {
            if (array_search($allowed, $permissions) === false) {
                if (self::$required) {
                    $validated = false;
                    break;
                }
            } else {
                $validated = true;
            }
        }

        if (!$validated) {
            throw new Exception(
                Parameters::getPermissionFailMessage(),
                self::DEFAULT_PERMISSION_FAIL_MESSAGE[0]
            );
        }
    }

    public static function clear()// : self
    {
        self::$allowed = [];

        return __CLASS__;
    }
}
