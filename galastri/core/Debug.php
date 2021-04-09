<?php
namespace galastri\core;

use \galastri\modules\Functions as F;

/**
 * This class is part of the core of the framework. It helps to handle the
 * exceptions and shows error messages when some configuration is wrong or a
 * framework function is used incorrectly.
 */
final class Debug
{
    const GENERIC_MESSAGE = "An error occurred. Please, contact the administrator.";
    
    /**
     * The backlog array, provided by PHP debug_backtrace() function.
     *
     * @var array
     */
    private static $backlogData = [];
    
    /**
     * The message that will be shown when an exception is thrown.
     *
     * @var string
     */
    private static $message = '';

    /**
     * The code that identifies the exception.
     *
     * @var int|string
     */
    private static $code = '';
    
    /**
     * When true, bypass the displayErrors = false configuration in
     * \app\config\debug.php and shows the error message anyway.
     *
     * @var bool
     */
    private static $bypassGenericMessage = false;
    
    /**
     * This is a singleton class, so, the __construct() method is private to
     * avoid user to instanciate this class.
     *
     * @return void
     */
    private function __construct()
    {
    }
        
    /**
     * Stores the backlog array in the $backlog attribute.
     *
     * @return \galastri\core\Debug
     */
    public static function setBacklog()
    {
        self::$bypassGenericMessage = false;
        $backlogData = debug_backtrace()[1];

        if (GALASTRI_DEBUG['showBacklogData']) {
            self::$backlogData[] = $backlogData;
        } else {
            self::$backlogData[0] = $backlogData;
        }

        return __CLASS__;
    }
    
    /**
     * Returns every data of the backlog array.
     *
     * @return array
     */
    private static function getBacklog()
    {
        return self::$backlogData;
    }

    /**
     * Returns the most recent data of the backlog array.
     *
     * @param  string|int|bool $index   Return specific key of the most recent
     *                                  backlog data.
     *
     * @return array
     */
    private static function getLastBacklog(mixed $index = false)
    {
        return $index ? self::$backlogData[0][$index] : self::$backlogData[0];
    }
    
    /**
     * Sets the exception message and code. Can return additional data to be
     * converted if the message have %s flags in it.
     *
     * This method can be chained with self::print() method.
     *
     * @param  string $message          Message that will be displayed. Can have
     *                                  %s flags that will be replaced by values
     *                                  of $printfData parameter.
     *
     * @param  string|int $code         Code that identifies the exception.
     *
     * @param  array $printfData        Values that will replace %s flags in the
     *                                  message, in the same order of appearance
     *                                  of the flags.
     *
     *                                  Exemple:
     *                                  - message:    'This is %s and %s'
     *                                  - printfData: ['John', 'Paul']
     *
     *                                  Result: 'This is John and Paul'
     * @return \galastri\core\Debug
     */
    public static function setError(string $message, mixed $code, ...$printfData)
    {
        $printfData = F::flattenArray($printfData);
        
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
    public static function print()
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
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        exit();
    }
    
    /**
     * Sets the $bypassGenericMessage attribute to true temporarily to show the
     * error message even if the displayErrors debug configuration is false.
     *
     * @return void
     */
    public static function bypassGenericMessage()
    {
        self::$bypassGenericMessage = true;
    }
}
