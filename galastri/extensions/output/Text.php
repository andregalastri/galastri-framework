<?php

namespace galastri\extensions\output;

use galastri\core\Debug;

/**
 * This trait is the Text output, used by the Galastri class, to return a plain text to the request.
 *
 * This trait:
 * - Just get the data returned by the route controller, converts it into plain text format and
 *   returns it to the request.
 *
 * Every property and method name start with 'text' to prevent incompatibilities with other output
 * traits.
 */
trait Text
{
    /**
     * This is the main method of the Text output. It just converts the data returned by the route
     * controller to plain text format and prints it to return the text to the request.
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
     * This method is executed recursively to print each array key from the route controller as a
     * plain text.
     *
     * @param  mixed $array                         The array from the route controller that will be
     *                                              untangled. Each time it is called recursively,
     *                                              this array will be an inner array from the data
     *                                              from the route controller.
     *
     * @param  mixed $tab                           It adds a tab for each level of the
     *                                              multidimensional array from the route
     *                                              controller.
     *
     * @return void
     */
    private static function textConvertArray(array $array, &$tab = ''): void
    {
        foreach ($array as $key => $value) {
            /**
             * The key of the array will only be printed if it is a string.
             */
            $key = is_int($key) ? '' : $key.': ';

            /**
             * If the value is an array, it will add a tab to the $tab parameter and will call this
             * own method recursively, passing the sub array value and the current tab as
             * parameters.
             */
            if (gettype($value) === 'array') {
                echo $tab.$key.PHP_EOL;
                $tab .= '    ';
                self::textConvertArray($value, $tab);

            /**
             * If the value is an object, it will be ignored.
             */
            } else if (gettype($value) === 'object') {
                continue;

            /**
             * However, if it is a primitive value, it will be printed.
             */
            } else {
                $value = is_string($value) ? $value : var_export($value, true);
                $value = str_replace(['\n', '\t', '\s'], [PHP_EOL, chr(9), ' '], $value);
                echo $tab.$key.$value.PHP_EOL;
            }
        }

        /**
         * Resets one level of the tab for each recursive call of this method.
         */
        $tab = substr($tab, 4);
    }

    /**
     * This method is exclusively used by the Galastri class to determine if this output requires a
     * controller to work.
     *
     * @return bool
     */
    private static function textRequiresController(): bool
    {
        return true;
    }
}
