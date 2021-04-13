<?php

namespace galastri\extensions;

use \galastri\core\Debug;
use \galastri\extensions\Exception;
use \galastri\modules\Toolbox;

final class ViewOutputData
{
    const EMPTY_DATA_KEY = ['EMPTY_DATA_KEY', "Inform a key of the data you want to get."];
    const INVALID_DATA_KEY = ['INVALID_DATA_KEY', "Key '%s' doesn't exist in the data returned by controller."];

    private array $resultdata;
    private string $viewFilePath;

    public function __construct(array $resultData, string $viewFilePath)
    {
        $this->resultData = $resultData;
        $this->viewFilePath = $viewFilePath;
    }

    public function data(...$keys)// : mixed
    {
        Debug::setBacklog();

        $resultData = $this->resultData;

        if (empty($keys)) {
            return $resultData;
        }

        foreach ($keys as $value) {
            if (isset($resultData[$value])) {
                $resultData = $resultData[$value];
            } else {
                throw new Exception(self::INVALID_DATA_KEY[1], self::INVALID_DATA_KEY[0], [$value]);
            }
        }

        return $resultData;
    }

    public static function trim(string $string, string ...$chars): string
    {
        return Toolbox::trim($string, $chars);
    }
    
    public static function capitalize(string $string, bool $asArticle = false, bool $keepChars = false): string
    {
        return Toolbox::capitalize($string, $asArticle, $keepChars);
    }

    public static function upperCase(string $string): string
    {
        return Toolbox::upperCase($string);
    }

    public static function lowerCase(string $string): string
    {
        return Toolbox::lowerCase($string);
    }

    public function import(string $path)
    {
        $galastri = $this;

        if ($path === 'view') {
            return require_once(Toolbox::getRealPath($this->viewFilePath));
        }
    }
}