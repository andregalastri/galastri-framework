<?php

namespace galastri\extensions;

use galastri\core\Debug;
use galastri\extensions\Exception;
use galastri\modules\Toolbox;

final class ViewOutputData implements \Language
{
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
                throw new Exception(self::VIEW_INVALID_DATA_KEY[1], self::VIEW_INVALID_DATA_KEY[0], [$value]);
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