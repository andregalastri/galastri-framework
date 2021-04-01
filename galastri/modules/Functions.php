<?php
namespace galastri\modules;

class Functions
{
    private static $debugTrack = false;

    public static function getRealPath(string $path)
    {
        self::checkDebugTrack();
        return realpath(GALASTRI_PROJECT_DIR.$path);
    }

    public static function importFile(string $path)
    {
        self::checkDebugTrack();
        return require(self::getRealPath($path));
    }

    public static function getFileContents(string $path)
    {
        self::checkDebugTrack();
        return file_get_contents(self::getRealPath($path));
    }

    public static function arrayKeySearch(string $search, array $array, int $matchType = MATCH_ANY)
    {

        $arrayKeys = array_keys($array);
        $found = false;

        $regex = (function($a, $b){
            switch ($b) {
                case MATCH_ANY: return "/$a/";
                case MATCH_START: return "/^$a/";
                case MATCH_END: return "/$a$/";
            }
        })(preg_quote($search, '/'), $matchType);
        
        foreach ($arrayKeys as $key) {
            preg_match($regex, $key, $match);
            if (!empty($match)) {
                $found[$key] = $array[$key];
            }
        }

        return $found;
    }

    public static function trim(string $value, ...$chars)
    {
        if (empty($chars)) {
            $chars = [' '];
        }

        foreach ($chars as $char) {
            $value = ltrim(rtrim($value, $char), $char);
        }

        return $value;
    }

    public static function capitalize(string $string, bool $asArticle = false, bool $keepChars = false) {
        if ($asArticle) {
            $string = preg_split('/(\.|\!|\?)/', $string, -1, PREG_SPLIT_DELIM_CAPTURE);
            $string = array_map('trim', $string);
        } else {
            $string = [$string];
        }

        foreach ($string as $key => $value) {
            $value = $asArticle ? self::upperCaseFirst($value, !$keepChars) : mb_convert_case($value, MB_CASE_TITLE, 'UTF-8');;

            preg_match('/(\.|\!|\?)/', $value, $match);
            if (!array_key_exists(1, $match)) $match[1] = false;

            $space = $value != $match[1] ? ' ' : '';
            $string[$key] = $space.$value;
        }

        $string = trim(implode('', $string));
        return $string;
    }

    public static function upperCase(string $string)
    {
        return mb_convert_case($string, MB_CASE_UPPER, 'UTF-8');
    }

    public static function lowerCase(string $string)
    {
        return mb_convert_case($string, MB_CASE_LOWER, 'UTF-8');
    }

    public static function convertCase(string $string, int $type, $regex = '/(-|_)/')
    {
        $string = preg_split($regex, $string);
        $string = array_map(function($a){
            return self::trim($a, ' ');
        }, $string);

        foreach($string as $key => &$value){
            if(empty($value))
                continue;

            switch($type){
                case CAMEL_CASE:
                    if($key == 0)
                        continue 2;
                    break;
                case PASCAL_CASE:
                    break;
            }

            $value = self::capitalize($value, true, true);
        } unset($value);

        return implode($string);
    }

    private static function upperCaseFirst(string $string, bool $lowerStringEnd = false, string $encoding = 'UTF-8') {
        $firstLetter = mb_strtoupper(mb_substr($string, 0, 1, $encoding), $encoding);
        $stringingEnd = '';
        if ($lowerStringEnd) {
            $stringingEnd = mb_strtolower(mb_substr($string, 1, mb_strlen($string, $encoding), $encoding), $encoding);
        } else {
            $stringingEnd = mb_substr($string, 1, mb_strlen($string, $encoding), $encoding);
        }
        $string = $firstLetter . $stringingEnd;
        return $string;
    }













    public static function debugTrack()
    {
        self::$debugTrack = true;
        return __CLASS__;
    }

    private static function checkDebugTrack()
    {
        if(self::$debugTrack){
            // Debug::setTrace(debug_backtrace()[0]);
            self::$debugTrack = false;
        }
    }
}
