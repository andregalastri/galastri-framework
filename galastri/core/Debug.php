<?php

namespace galastri\core;

/**
 * This is the debug class of the framework. It helps to handle the exceptions and shows
 * error messages when some configuration is wrong or a framework function is used incorrectly.
 */
final class Debug implements \Language
{
    /**
     * Stores the backlog array, provided by the PHP debug_backtrace function.
     *
     * @var array
     */
    private static array $backlogData = [];

    /**
     * Stores a message that will be shown when an exception is thrown.
     *
     * @var string
     */
    private static string $message = '';

    /**
     * Stores a code that identifies the exception.
     *
     * @var int|null|string
     */
    private static /*int|null|string*/ $code = null;

    /**
     * When true, display the message of the exception even if the displayErrors debug configuration
     * is set as false.
     *
     * @var bool
     */
    private static bool $bypassGenericMessage = false;

    /**
     * This is a singleton class, the __construct() method is private to avoid users to instanciate
     * it.
     *
     * @return void
     */
    private function __construct()
    {
    }

    /**
     * This method stores a backtrace array in the $backlogData property, providade by the
     * PHP debug_backgrace function. This built-in function return an array which each key stores a
     * trace of the method or function that is being executed.
     *
     * This method always store the previous trace, which is the key number 1, not the current trace
     * (key number 0).
     *
     * @return self
     */
    public static function setBacklog()// : self
    {
        self::$bypassGenericMessage = false;

        self::$backlogData[] = debug_backtrace()[1];

        return __CLASS__;
    }

    /**
     * Gets all backlog array data.
     *
     * @return array
     */
    public static function getBacklog(): array
    {
        return self::$backlogData;
    }

    /**
     * Returns the last data added to the backlog array. When a key is specified, return only that
     * key value. If this the case, when the key doesn't exist, return null.
     *
     * @param  null|string $key                     Specify a key of the most recent backlog data to
     *                                              be returned.
     *
     * @return mixed
     */
    public static function getLastBacklog(?string $key = null)// : mixed
    {
        $lastBacklog = self::$backlogData;
        $lastBacklog = array_pop($lastBacklog);

        return $key === null ? $lastBacklog : ($lastBacklog[$key] ?? null);
    }

    /**
     * This method sets the exception message and its code. If the message have %s flags in it, the
     * values can be replaced by the printfData parameter. If the displayErrors parameter is set as
     * false in the debug configuration, the message will be replaced by a generic one, except if
     * the $bypassGenericMessage is set before return the exception to the request.
     *
     * This method can be chained with self::print() method.
     *
     * @param  string $message                      Message that will be displayed. Can have %s
     *                                              flags that will be replaced by values of
     *                                              $printfData parameter.
     *
     * @param  int|string $code                     Code that identifies the exception.
     *
     * @param  mixed ...$printfData                 Values that will replace the %s flags of the
     *                                              message. Needs to be set in the same order of
     *                                              appearance of the flags of the message.
     *
     *                                              Exemple:
     *                                              - message:    'This is %s and %s'
     *                                              - printfData: ['John', 'Paul']
     *
     *                                              Result: 'This is John and Paul'
     * @return self
     */
    public static function setError(string $message, /*int|string*/ $code, /*mixed*/ ...$printfData)// : self
    {
        self::$message = (function () use ($message, $printfData) {
            if (!Parameters::getDisplayErrors() and !self::$bypassGenericMessage) {
                return self::GENERIC_MESSAGE;
            } else {
                return vsprintf($message, ...$printfData);
            }
        })();

        self::$code = $code;

        return __CLASS__;
    }

    /**
     * Return the exception in JSON or Text format (based on the defined output) and end the
     * execution of the script. If the displayErrors parameter is set as false in the debug
     * configuration, the origin and line data will be null.
     *
     * @return void
     */
    public static function print(): void
    {
        /**
         * If there is an output set in the route configuration and it is the Text output, then the
         * data returned is the error code and the message.
         */
        if (Parameters::getOutput() === 'text') {
            header('Content-Type: text/plain');
            echo 'Error '.self::$code.PHP_EOL;
            echo self::$message.PHP_EOL;

        /**
         * However, if the output is any other, or is undefined, then a JSON is returned.
         */
        } else {
            /**
             * Defines how the data will be returned.
             */
            $displayErrors = Parameters::getDisplayErrors();
            $data = [
                'code' => self::$code,
                'origin' => $displayErrors ? self::getLastBacklog('file') : null,
                'line' => $displayErrors ? self::getLastBacklog('line') : null,
                'message' => self::$message,
                'warning' => true,
                'error' => true,
            ];

            /**
             * If the 'showBacklogData' parameter configured in the debug configuration is true,
             * then all backlog trace will also be shown.
             */
            if (Parameters::getShowBacklogData()) {
                $data = array_merge($data, [
                    'backlogTrace' => self::getBacklog(),
                ]);
            }

            header('Content-Type: application/json');
            echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }

        exit();
    }

    /**
     * This method sets the $bypassGenericMessage property to true, which means that even if the
     * 'displayErrors' parameter is set as false, the message shown won't be the generic one.
     */
    public static function bypassGenericMessage(): void
    {
        self::$bypassGenericMessage = true;
    }
}
