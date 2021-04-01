<?php
namespace galastri\core;

use \galastri\modules\Functions as F;

/**
 * This class is parte of the core of the framework. It helps to handle the
 * exceptions and shows error messages when some configuration is wrong or a
 * framework function is used incorrectly.
 */
class Debug
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
     * @var array
     */
    private static $message = '';

    /**
     * The code that identifies the exception.
     *
     * @var array
     */
    private static $code = '';
    
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
     * @param  array $backlogData       The array given by the debug_backlog()
     *                                  function
     *
     * @return void
     */
    public static function setBacklog(array $backlogData)
    {
        if (GALASTRI_DEBUG['showBacklogData']) {
            self::$backlogData[] = $backlogData;
        } else {
            self::$backlogData[0] = $backlogData;
        }
    }
    
    /**
     * Returns every data of the backlog array.
     *
     * @return void
     */
    public static function getBacklog()
    {
        return self::$backlogData;
    }

    /**
     * Returns the most recent data of the backlog array.
     *
     * @param  string|int|bool $index   Return specific key of the most recent
     *                                  backlog data.
     *
     * @return void
     */
    public static function getLastBacklog(mixed $index = false)
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
        
        self::$message = GALASTRI_DEBUG['displayErrors'] ? vsprintf($message, $printfData) : self::GENERIC_MESSAGE[0];
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
        $data = [
            'code' => self::$code,
            'origin' => self::getLastBacklog('file'),
            'line' => self::getLastBacklog('line'),
            'message' => self::$message,
            'warning' => true,
            'error' => true,
        ];

        if (GALASTRI_DEBUG['showBacklogData']) {
            $data = array_merge($data, [
                'backlogTrace' => self::getBacklog(),
            ]);
        }

        header('Content-Type: application/json');
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        exit();
    }
}
