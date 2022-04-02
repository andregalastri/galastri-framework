<?php

namespace galastri\cli\traits;

trait Language
{
    private static string $language;
    
    private static function message($code, $index)
    {
        return constant('self::'.$code)[self::$language][$index];
    }

    public static function setLanguage($language = 'en')
    {
        if(array_search($language, self::AVAILABLE_LANGUAGES) === false) {
            $language = 'en';
        }
        self::$language = $language;
    }
}
