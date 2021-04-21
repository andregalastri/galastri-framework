<?php

namespace galastri\core;

use galastri\modules\Toolbox;

/**
 * This class is part of the core of the framework. It helps to handle the exceptions and shows
 * error messages when some configuration is wrong or a framework function is used incorrectly.
 */
final class Debug implements \Language
{
    /**
     * The backlog array, provided by PHP debug_backtrace() function.
     *
     * @var array
     */
    private static array $backlogData = [];

    /**
     * The message that will be shown when an exception is thrown.
     *
     * @var string
     */
    private static string $message = '';

    /**
     * The code that identifies the exception.
     *
     * @var int|null|string
     */
    private static /*int|null|string*/ $code = null;

    /**
     * When true, bypass the displayErrors = false configuration in \app\config\debug.php and shows
     * the error message anyway.
     *
     * @var bool
     */
    private static bool $bypassGenericMessage = false;

    /**
     * This is a singleton class, so, the __construct() method is private to avoid user to
     * instanciate this class.
     *
     * @return void
     */
    private function __construct()
    {
    }

    /**
     * Stores the backlog array in the $backlog property.
     *
     * @param array|null $customBacklog             A custom array backlog instead of the
     *                                              debug_backtrace() one.
     * @return galastri\core\Debug
     */
    public static function setBacklog(?array $customBacklog = null): string /*self*/
    {
        self::$bypassGenericMessage = false;

        self::$backlogData[] = $customBacklog ? $customBacklog[0] : debug_backtrace()[1];

        return __CLASS__;
    }

    /**
     * Returns every data of the backlog array.
     *
     * @param  int|null $index                      Return a specific group of keys of the stored
     *                                              backlog.
     *
     * @param  null|string $key                     Return specific key of the most recent backlog
     *                                              data.
     *
     *
     * @return mixed
     */
    public static function getBacklog(?int $index = null, ?string $key = null) // : mixed
    {
        if ($index === null and $key === null) {
            return self::$backlogData;
        } elseif ($index !== null and $key === null) {
            return self::$backlogData[$index];
        } else {
            return self::$backlogData[$index][$key];
        }
    }

    /**
     * Returns the most recent data of the backlog array.
     *
     * @param  null|string $key              Return specific key of the most recent
     *                                              backlog data.
     *
     * @return mixed
     */
    public static function getLastBacklog(?string $key = null) // : mixed
    {
        $lastBacklog = self::$backlogData;
        $lastBacklog = array_pop($lastBacklog);

        return $key === null ? $lastBacklog : $lastBacklog[$key];
    }

    /**
     * Sets the exception message and code. Can return additional data to be converted if the
     * message have %s flags in it.
     *
     * This method can be chained with self::print() method.
     *
     * @param  string $message                      Message that will be displayed. Can have %s
     *                                              flags that will be replaced by values of
     *                                              $printfData parameter.
     *
     * @param  int|string $code                     Code that identifies the exception.
     *
     * @param  mixed d...$printfData                Values that will replace %s flags in the
     *                                              message, in the same order of appearance of the
     *                                              flags.
     *
     *                                              Exemple:
     *                                              - message:    'This is %s and %s'
     *                                              - printfData: ['John', 'Paul']
     *
     *                                              Result: 'This is John and Paul'
     * @return \galastri\core\Debug
     */
    public static function setError(string $message, /*int|string*/ $code, /*mixed*/ ...$printfData): string /*self*/
    {
        $printfData = Toolbox::flattenArray($printfData);
        
        self::$message = (function ($message, $printfData) {
            if (!GALASTRI_DEBUG['displayErrors'] and !self::$bypassGenericMessage) {
                return self::GENERIC_MESSAGE;
            } else {
                return vsprintf($message, $printfData);
            }
        })($message, $printfData);

        self::$code = $code;

        return __CLASS__;
    }

    /**
     * Prints the exception message as a JSON and stops executing the script.
     *
     * @return void
     */
    public static function print(): void
    {
        if (GALASTRI_DEBUG['displayErrors']) {
            $data = [
                'code' => self::$code,
                'origin' => self::getLastBacklog('file'),
                'line' => self::getLastBacklog('line'),
                'message' => self::$message,
                'warning' => true,
                'error' => true,
            ];
        } else {
            $data = [
                'code' => self::$code,
                'origin' => null,
                'line' => null,
                'message' => self::$message,
                'warning' => true,
                'error' => true,
            ];
        }

        if (GALASTRI_DEBUG['showBacklogData']) {
            $data = array_merge($data, [
                'backlogTrace' => self::getBacklog(),
            ]);
        }

        header('Content-Type: application/json');
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        exit();
    }

    /**
     * Sets the $bypassGenericMessage property to true temporarily to show the error message even
     * if the displayErrors debug configuration is false.
     *
     * @return void
     */
    public static function bypassGenericMessage(): void
    {
        self::$bypassGenericMessage = true;
    }
}
