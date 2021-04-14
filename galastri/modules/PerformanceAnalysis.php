<?php

namespace galastri\modules;

use galastri\modules\Toolbox;

/**
 * This class creates a log file with an analysis of the entire request execution, or a other custom
 * parts of the user code.
 *
 * The measures are calculated between the begin() to flush() methods and every flush() to flush()
 * methods.
 *
 * The log file have these measures about the execution:
 *
 *   - Order of the execution;
 *   - Path of the file that executed the method;
 *   - The route requested (URI);
 *   - The request method, like POST, GET, etc;
 *   - Method that is being executed;
 *   - Memory usage in the current portion of the script;
 *   - Peak of memory usage in the entire script;
 *   - Execution time (in milliseconds) of the section measured;
 *   - Cumulative execution time (in milliseconds).
 */

/**
 * Measure example:
 *
 *   1. /galastri/core/Route.php
 *     Requested URI .......... /page1
 *     Request Method ......... POST
 *     Execution .............. galastri\core\Route::prepareUrlArray
 *     Memory Usage (Current).. 455.55 kb
 *     Memory Usage (Peak)..... 455.55 kb
 *     Execution Time ......... 0.1 ms
 *     Cumulative Time ........ 0.1 ms
 * 
 *   2. /galastri/core/Route.php
 *     Requested URI .......... /page1
 *     Request Method ......... POST
 *     Execution .............. galastri\core\Route::resolveParentNodeParams
 *     Memory Usage (Current).. 366.27 kb
 *     Memory Usage (Peak)..... 455.55 kb
 *     Execution Time ......... 0.1 ms
 *     Cumulative Time ........ 0.2 ms
 */
final class PerformanceAnalysis
{
    const LOG_DIRECTORY_PATH = '/logs/performance-analysis/';

    /**
     * Stores the status when begin() method is called. When true, then the measures are active and
     * the data is being collected.
     *
     * @var array
     */
    private static array $status = [];

    /**
     * Stores the start time of the execution that will be compared when the measure stops.
     *
     * @var array
     */
    private static array $microtimeStart = [];

    /**
     * Stores the time when the execution stops and will be compared with the start time.
     *
     * @var array
     */
    private static array $microtimeStop = [];

    /**
     * Stores the cumulative execution time.
     *
     * @var array
     */
    private static array $cumulativeTime = [];

    /**
     * Stores the flushed data, created to be stored in the log file.
     *
     * @var array
     */
    private static array $flushedData = [];

    /**
     * Stores the last label that was created. Helps to avoid informing the label of the analysis to
     * all methods.
     *
     * @var string
     */
    private static string $lastLabel = '';

    /**
     * This is a singleton class, so, the __construct() method is private to avoid user to
     * instanciate it.
     *
     * @return void
     */
    private function __construct()
    {
    }

    /**
     * Sets the start of the measures and also starts the microtimer. The microtime is the current
     * time when the execution starts in milliseconds. When a flush() method is executed, this start
     * time will be subtracted from the stop time and with this we have the measure of how much time
     * was spent in the execution.
     *
     * @param  string $label                        Label that identifies the analysis.
     *
     * @param  bool $status                         Sets if the analysis will be executed (true) or
     *                                              not (false)
     *
     * @return void
     */
    public static function begin(string $label, bool $status = true): void
    {
        self::$status[$label] = $status;

        if ($status) {
            self::$lastLabel = $label;
            self::microtimeStart($label);
        }
    }

    /**
     * Creates a section in the log file with the data measured from the start to this point. This
     * data is stored inside an array that will be processed when it is inserted inside the log
     * file.
     *
     * After get the data, this method also reestarts the microtimer, so when another flush() method
     * is executed, the data measured will be of the last flush() to the next flush() and so on.
     *
     * @param  string $label                        Label that identifies the analysis.
     *
     * @return \galastri\modules\PerformanceAnalysis
     */
    public static function flush(string $label = ''): string
    {
        $label = $label ?: self::$lastLabel;

        if (self::$status[$label]) {
            self::microtimeStop($label);

            $backlogData = debug_backtrace()[1];
            $executionTime = (float) number_format((self::$microtimeStop[$label] - self::$microtimeStart[$label]) * 1000, 1, '.', '');

            self::$cumulativeTime[$label] = (self::$cumulativeTime[$label] ?? 0) + $executionTime;

            self::$flushedData[$label][] = [
                str_replace(GALASTRI_PROJECT_DIR, '', $backlogData['file']),
                '    Requested URI .......... ' . $_SERVER['REQUEST_URI'],
                '    Request Method ......... ' . $_SERVER['REQUEST_METHOD'],
                '    Execution .............. ' . ($backlogData['class'] ?? 'root') . ($backlogData['type'] ?? '..') . ($backlogData['function'] ?? 'root'),
                '    Memory Usage (Current).. ' . Toolbox::convertBytes(memory_get_usage()),
                '    Memory Usage (Peak)..... ' . Toolbox::convertBytes(memory_get_peak_usage()),
                '    Execution Time ......... ' . $executionTime . ' ms',
                '    Cumulative Time ........ ' . self::$cumulativeTime[$label] . ' ms',
            ];

            self::microtimeStart($label);
        }

        return __CLASS__;
    }

    /**
     * Stores the collected data inside the log file. All the data is converted to a string and it
     * is stored in the bottom of the file. The name of the file is the label that identifies the
     * analysis, the date of the analysis and the current hour.
     *
     * @param  string $label                        Label that identifies the analysis.
     *
     * @return void
     */
    public static function store(string $label = ''): void
    {
        $label = $label ?: self::$lastLabel;

        if (self::$status[$label]) {
            foreach (self::$flushedData[$label] as $key => &$data) {
                $key++;
                $data = '  ' . $key . '. ' . implode(PHP_EOL, $data) . PHP_EOL . PHP_EOL;
            }

            $flushedData  = '-------------- GALASTRI PERFORMANCE ANALYSIS [' . date('Y-m-d   H:i:s') . '] --------------';
            $flushedData .= str_repeat(PHP_EOL, 3) . implode(PHP_EOL, self::$flushedData[$label]);
            $flushedData .= 'END';
            $flushedData .= str_repeat(PHP_EOL, 4);

            $filename = $label === PERFORMANCE_ANALYSIS_LABEL ? '' : preg_replace('/[^a-zA-Z0-9]+/', '', $label);
            $filename = $filename . date('Ymd-H') . '.log';

            Toolbox::createFile(self::LOG_DIRECTORY_PATH . $filename);
            Toolbox::fileInsertContent(self::LOG_DIRECTORY_PATH . $filename, $flushedData);

            self::reset($label);
        }
    }

    /**
     * Starts the microtimer to define when the execution started.
     *
     * @param  string $label                        Label that identifies the analysis.
     * 
     * @return void
     */
    private static function microtimeStart(string $label): void
    {
        if (self::$status[$label]) {
            self::$microtimeStart[$label] = microtime(true);
        }
    }

    /**
     * Stops the microtimer to define when the execution stoped.
     *
     * @param  string $label                        Label that identifies the analysis.
     * 
     * @return void
     */
    private static function microtimeStop(string $label): void
    {
        if (self::$status[$label]) {
            self::$microtimeStop[$label] = microtime(true);
        }
    }

    /**
     * Reset all the data of an analysis.
     *
     * @param  string $label                        Label that identifies the analysis.
     * 
     * @return void
     */
    private static function reset(string $label): void
    {
        self::$flushedData[$label] = [];
        self::$cumulativeTime[$label] = 0;
        self::$status[$label] = false;
    }
}
