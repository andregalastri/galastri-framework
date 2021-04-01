<?php
namespace galastri\core;

class Debug
{
    const GENERIC_MESSAGE = "An error occurred. Please, contact the administrator.";

    private static $traceData = [];
    private static $message = '';
    private static $code = '';

    private function __construct(){}

    public static function setTrace($data)
    {
        self::$traceData = $data;
    }

    public static function getTrace($index = false)
    {
        return self::$traceData;
    }

    public static function setError($message, $code, ...$data)
    {
        $data = _flattenArray($data);
        
        self::$message = GALASTRI_DEBUG['displayErrors'] ? vsprintf($message, $data) : self::GENERIC_MESSAGE[0];
        self::$code = $code;
        
        return __CLASS__;
    }

    public static function print()
    {
        $data = [
            'code' => self::$code,
            'origin' => self::getTrace('file'),
            'line' => self::getTrace('line'),
            'message' => self::$message,
            'warning' => true,
            'error' => true,
        ];

        if(GALASTRI_DEBUG['showTraceData'])
            $data = array_merge($data, [
                'traceData' => self::getTrace(),
            ]);

        header('Content-Type: application/json');
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        exit();
    }
}
