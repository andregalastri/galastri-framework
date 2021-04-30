<?php

namespace galastri\extensions\output;

use \galastri\core\Debug;

/**
 * This is the Text output script that is used by \galastri\core\Galastri class to return a text
 * output to the request. It gets the returning array from the route controller, format and print
 * each of its keys.
 */
trait Text
{    
    /**
     * Main method. It gets the returning array from the route controller, format and print each of
     * its keys.
     *
     * @return void
     */
    private static function text(): void
    {
        Debug::setBacklog();

        header('Content-Type: text/plain');
        self::textConvertArray(self::$routeController->getResultData());
    }
    
    /**
     * textConvertArray
     *
     * @param  mixed $array
     * @param  mixed $tab
     * @return void
     */
    private static function textConvertArray(array $array, &$tab = ''): void
    {
        foreach ($array as $key => $value) {
            $key = is_int($key) ? '' : $key.': ';

            if (gettype($value) === 'array') {
                echo $tab.$key.PHP_EOL;
                $tab .= '    ';
                self::textConvertArray($value, $tab);
            } else {
                $value = is_string($value) ? $value : var_export($value, true);
                $value = str_replace(['\n', '\t', '\s'], [PHP_EOL, chr(9), ' '], $value);
                echo $tab.$key.$value.PHP_EOL;
            }
        }
        $tab = substr($tab, 4);
    }
}
