<?php
namespace galastri\modules;

use \galastri\core\Debug;
use \galastri\core\PerformanceAnalysis;

/**
 * This class have many methods that execute functions that can help and to make
 * things easy. It is used by the framework, so, be careful if you will change
 * something here.
 *
 * However, if you want to import your own functions, consider create your own
 * file inside /app/config/additional-config. All the .php scripts there are
 * automatically loaded with the framework.
 */
final class Functions
{
    /**
     * Property that stores if the method will be executed with trace bracklog
     * active or not. Useful only for debuggin the framework, not for users.
     *
     * @var bool
     */
    private static $debugTrack = false;

    /**
     * This is a singleton class, so, the __construct() method is private to
     * avoid user to instanciate it.
     *
     * @return void
     */
    private function __construct()
    {
    }

    /**
     * Author: Sven Arduwie
     * https://www.php.net/manual/pt_BR/function.realpath.php#84012

     * Receives a path and converts it to the real path, based on project's
     * root.
     *
     * @param  string $path             The path which will be converted.
     * @return string
     */
    public static function getRealPath(string $path)
    {
        self::checkDebugTrack();

        $path = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, GALASTRI_PROJECT_DIR.'/'.self::trim($path, DIRECTORY_SEPARATOR));
        $parts = array_filter(explode(DIRECTORY_SEPARATOR, $path), 'strlen');
        $absolutes = array();
        foreach ($parts as $part) {
            if ('.' == $part) continue;
            if ('..' == $part) {
                array_pop($absolutes);
            } else {
                $absolutes[] = $part;
            }
        }

        $finalPath = implode(DIRECTORY_SEPARATOR, $absolutes);
        
        return strncasecmp(PHP_OS, 'WIN', 3) == 0 ? $finalPath : DIRECTORY_SEPARATOR.$finalPath;
    }
    
    /**
     * Imports a script file, based on the given path.
     *
     * @param  string $path             The path of the file to be imported.
     * @return mixed
     */
    public static function importFile(string $path)
    {
        self::checkDebugTrack();
        require(self::getRealPath($path));
        
        return require(self::getRealPath($path));
    }
    
    /**
     * Get the contents of a file.
     *
     * @param  string $path             The path of the file to be got.
     * @return mixed
     */
    public static function getFileContents(string $path)
    {
        self::checkDebugTrack();
        return file_get_contents(self::getRealPath($path));
    }
    
    /**
     * A better trim function. Removes many chars from the beginning and end of
     * the given string.
     *
     * @param  string $string            The string that will be trimmed.
     *
     * @param  array $chars             List of chars that will be removed from
     *                                  the string.
     * @return string
     */
    public static function trim(string $string, ...$chars)
    {
        if (empty($chars)) {
            $chars = [' '];
        }


        foreach ($chars as $char) {
            $char = preg_quote($char);
            $string = ltrim(rtrim($string, $char), $char);
        }

        return $string;
    }
    
    /**
     * Capitalizes a the given string.
     *
     * @param  string $string           The string that will be converted.
     *
     * @param  bool $asArticle          Capitalize the letters the are in the
     *                                  beginning of the string and also the
     *                                  ones next to periods, exclamation and
     *                                  question marks.
     *
     * @param  bool $keepChars          If true, will keep capitalized letters
     *                                  that already are in the string.
     * @return string
     */
    public static function capitalize(string $string, bool $asArticle = false, bool $keepChars = false)
    {
        if ($asArticle) {
            $string = preg_split('/(\.|\!|\?)/', $string, -1, PREG_SPLIT_DELIM_CAPTURE);
            $string = array_map('trim', $string);
        } else {
            $string = [$string];
        }

        /**
         * An internal method to capitalize only the first letter of the string.
         */
        $upperCaseFirst = function (string $string, bool $lowerStringEnd = false, string $encoding = 'UTF-8') {
            $firstLetter = mb_strtoupper(mb_substr($string, 0, 1, $encoding), $encoding);
            $stringingEnd = '';
            if ($lowerStringEnd) {
                $stringingEnd = mb_strtolower(mb_substr($string, 1, mb_strlen($string, $encoding), $encoding), $encoding);
            } else {
                $stringingEnd = mb_substr($string, 1, mb_strlen($string, $encoding), $encoding);
            }
            $string = $firstLetter . $stringingEnd;
            return $string;
        };

        foreach ($string as $key => $value) {
            $value = $asArticle ? $upperCaseFirst($value, !$keepChars) : mb_convert_case($value, MB_CASE_TITLE, 'UTF-8');

            preg_match('/(\.|\!|\?)/', $value, $match);
            if (!array_key_exists(1, $match)) {
                $match[1] = false;
            }

            $space = $value != $match[1] ? ' ' : '';
            $string[$key] = $space.$value;
        }

        $string = trim(implode('', $string));
        return $string;
    }

    /**
     * Converts the string to UPPER case.
     *
     * @param  string $string           The string that will be converted.
     * @return string
     */
    public static function upperCase(string $string)
    {
        return mb_convert_case($string, MB_CASE_UPPER, 'UTF-8');
    }

    /**
     * Converts the string to lower case.
     *
     * @param  string $string            The string that will be converted.
     * @return string
     */
    public static function lowerCase(string $string)
    {
        return mb_convert_case($string, MB_CASE_LOWER, 'UTF-8');
    }
    
    /**
     * Converts the string to a specific type.
     *
     * @param  string $string           The string that will be converted.
     *
     * @param  int $type                The type of conversion. Can be
     *                                  CAMEL_CASE or PASCAL_CASE.
     *
     * @param  string $regex            A custom regex to detect chars that need
     *                                  to be removed if it is in the string.
     * @return string
     */
    public static function convertCase(string $string, int $type, string $regex = '/(-|_|\s)/')
    {
        $string = preg_split($regex, $string);
        $string = array_map(function ($a) {
            return self::trim($a, ' ');
        }, $string);

        foreach ($string as $key => &$value) {
            if (empty($value)) {
                continue;
            }

            switch ($type) {
                case CAMEL_CASE:
                    if ($key == 0) {
                        continue 2;
                    }
                    break;
                case PASCAL_CASE:
                    break;
            }

            $value = self::capitalize($value, true, true);
        }
        unset($value);

        return implode($string);
    }

     
    /**
     * This method search for keys in an array that is equal to a string. It has
     * modifiers to match any key that is equal to the string and keys that
     * starts or ends with the given string.
     *
     * @param  string $search           The value to be found.
     *
     * @param  array $array             The array that will be looked.
     *
     * @param  int $matchType           The modifier. Can be MATCH_ANY,
     *                                  MATCH_START or MATCH_END.
     * @return array|bool
     */
    public static function arrayKeySearch(string $search, array $array, int $matchType = MATCH_ANY)
    {
        $arrayKeys = array_keys($array);
        $found = false;

        $regex = (function ($a, $b) {
            switch ($b) {
                case MATCH_EXACT: return "$a";
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

    public static function arrayValueSearch(string $search, array $array, int $matchType = MATCH_ANY)
    {
        $arrayValues = array_values($array);
        $found = false;

        $regex = (function ($a, $b) {
            switch ($b) {
                case MATCH_EXACT: return "/\b$a\b/";
                case MATCH_ANY: return "/$a/";
                case MATCH_START: return "/^$a/";
                case MATCH_END: return "/$a$/";
            }
        })(preg_quote($search, '/'), $matchType);
        
        foreach ($arrayValues as $key => $value) {
            preg_match($regex, $value, $match);
            if (!empty($match)) {
                $found[$key] = $value;
            }
        }

        return $found;
    }

    public static function arrayValueExists(string $search, array $array, int $matchType = MATCH_EXACT)
    {
        if (self::arrayValueSearch($search, $array, $matchType)) {
            return true;
        } else {
            return false;
        }
    }
        
    /**
     * Converts a multidimensional array into a simple array, keeping the values
     * of the keys, but the indexes are lost.
     *
     * @param  array $array             The multidimensional array that will be
     *                                  converted.
     *
     * @param  string|bool|int $onlyKey Specify the index key label that will
     *                                  have its value returned.
     *
     *                                  Example:
     *                                  [0] => ['id' => 1, 'name' => 'John'],
     *                                  [1] => ['id' => 2, 'name' => 'Paul'],
     *
     *                                  If you specify $onlyKey = 'name', the
     *                                  method will only flat and return keys
     *                                  with 'name' label:
     *
     *                                  Result:
     *                                  [0] => 'John',
     *                                  [1] => 'Paul',
     *
     * @param  bool $unique             Returns only unique keys.
     *
     * @return array
     */
    public static function flattenArray(array $array, mixed $onlyKey = false, bool $unique = false)
    {
        $recursive = function ($array, $onlyKey, $result, $recursive) {
            foreach ($array as $key => $value) {
                if (gettype($value) === 'array') {
                    $result = $recursive($value, $onlyKey, $result, $recursive);
                } else {
                    if ($onlyKey === false) {
                        $result[] = $value;
                    } else {
                        if ($key == $onlyKey) {
                            $result[] = $value;
                        }
                    }
                }
            }
            
            return $result;
        };
        
        $result = $recursive($array, $onlyKey, [], $recursive);
        return $unique ? array_unique($result) : $result;
    }

    /**
     * Creates a file and all the directory path, if the file will be stored
     * inside a path that doesn't exist.
     *
     * @param  string $path             The path of the file.
     * 
     * @return void
     */
    public static function createFile(string $path)
    {
        self::checkDebugTrack();

        $parentDir = self::getRealPath($path.'/..');
        $filePath = self::getRealPath($path);

        if (!file_exists($parentDir)) {
            $permissionResolve = umask(0);
            mkdir($parentDir, 0777, true);
            umask($permissionResolve);
        }

        if (!file_exists($filePath)) {
            $fileOpen = fopen($filePath, 'a');
            fclose($fileOpen);
        }
    }

    /**
     * Insert a string inside a file.
     *
     * @param  string $path             The path of the file.
     * 
     * @param  string $string           The text that will be included inside
     *                                  the file.
     *
     * @param  string $method           Read/Write method used by fopen.
     * @return void
     */
    public static function fileInsertContent(string $path, string $string, string $method = 'a')
    {
        $filePath = self::getRealPath($path);

        $fileOpen = fopen($filePath, $method);
        fwrite($fileOpen, $string);
        fclose($fileOpen);
    }

    /**
     * Author: xelozz@gmail.com
     * https://www.php.net/manual/pt_BR/function.memory-get-usage.php#96280
     *
     * Converts a number of bytes in formatted string showing its value in kb,
     * mb, etc.
     *
     * @param  int $bytes               Integer in bytes that will be converted.
     * 
     * @return string
     */    
    public static function convertBytes(int $bytes)
    {
        $unit = ['b','kb','mb','gb','tb','pb'];
        return @round($bytes / pow(1024, ($i = floor(log($bytes, 1024)))), 2).' '.$unit[$i];
    }

    /**
     * Author hackan@gmail.com
     * https://www.php.net/manual/pt_BR/function.uniqid.php#120123
     *
     * Creates a random string using hexadecimal numbers and letters, based on
     * the informed length.
     *
     * - IMPORTANT: If you want to create safe unique string, use length greater
     *   than 15 chars.
     *
     * @param  int $length              Number of chars that the final random
     *                                  string will have.
     * @return string
     */
    public static function randomBytes(int $length) {
        if (function_exists("random_bytes")) {
            $bytes = random_bytes(ceil($length / 2));
        } elseif (function_exists("openssl_random_pseudo_bytes")) {
            $bytes = openssl_random_pseudo_bytes(ceil($length / 2));
        } else {
            throw new Exception("No cryptographically secure random function available");
            exit;
        }

        return bin2hex($bytes);
    }
        
    /**
     * Define the $debugTrack as true which allow the next method to be included
     * in the backlog errors if something wrong occurs.
     *
     * @return void
     */
    public static function debugTrack()
    {
        self::$debugTrack = true;
        return __CLASS__;
    }
    
    /**
     * If $debugTrack is true, store a trace of the backlog for debugging. Right
     * after store the backlog, it turns the $debugTrack off (false).
     *
     * @return void
     */
    private static function checkDebugTrack()
    {
        if (self::$debugTrack) {
            Debug::setBacklog();
            self::$debugTrack = false;
        }
    }
}
