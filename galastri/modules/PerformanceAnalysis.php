<?php

namespace galastri\modules;

use galastri\modules\types\TypeString;
use galastri\modules\types\TypeInt;

/**
 * This class creates a log file with an analysis of the entire request execution, or can be used by
 * the users to measure specific parts of their codes.
 *
 * The measure is calculated starting from the begin method and each flush() method sets the points
 * where the calculation will have to be temporarely stored. The store method finalizes the
 * calculation and create the log file with the measured.
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
    /**
     * Default location where the log files will be stored.
     */
    const LOG_DIRECTORY_PATH = '/logs/performance-analysis/';

    /**
     * Stores an array with the status of a label. It is defined when the begin() method is called.
     * Must follow this format:
     *
     * - Key: The label name that identifies the performance.
     * - Value: Boolean. When true means that the measurement is active and the data is being
     *   collocted.
     *
     * @var array
     */
    private static array $status = [];

    /**
     * Stores an array with the start time of the execution of a label. This time that will be
     * compared when the measure stops.
     *
     * Must follow this format:
     *
     * - Key: The label name that identifies the performance.
     * - Value: Integer. The microtime when the measurement was started.
     *
     * @var array
     */
    private static array $microtimeStart = [];

    /**
     * Stores an array with the stop time of the execution of a label. This time that will be
     * compared with the starting measure.
     *
     * Must follow this format:
     *
     * - Key: The label name that identifies the performance.
     * - Value: Integer. The microtime when the measurement was stopped.
     *
     * @var array
     */
    private static array $microtimeStop = [];

    /**
     * Stores an array with the cumulative time of the execution of a label. This is the rest of the
     * calculation that subtracts the stopping time with the starting time.
     *
     * Must follow this format:
     *
     * - Key: The label name that identifies the performance.
     * - Value: Integer. The difference between the starting time and the stopping time.
     *
     * @var array
     */
    private static array $cumulativeTime = [];

    /**
     * Stores an multidimensional data with the flushed data that will be stored in the log file.
     *
     * Must follow this format:
     *
     * - Key: The label name that identifies the performance.
     * - Value: Array. Data that was measured and stored.
     *
     * @var array
     */
    private static array $flushedData = [];

    /**
     * Stores the last label that was created. Helps to avoid informing the label of the analysis to
     * the methods each time that tjey are used.
     *
     * @var string
     */
    private static string $lastLabel = '';

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
     * This method sets the start of the measurement and also starts the microtimer. The microtime
     * is the current time when the execution starts, in milliseconds. When a flush method is
     * executed, this starting time will be subtracted from the stopping time, which the result is
     * the measure of how much time was spent in the execution.
     *
     * @param  string $label                        Label that identifies the analysis.
     *
     * @param  bool $status                         Sets if the analysis will be executed (true) or
     *                                              not (false).
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
     * This method creates a section in the log file with the data measured from the start to this
     * point. This data is stored inside an array that will be processed when it is inserted inside
     * the log file.
     *
     * After get the data, this method also restarts the microtimer, so when another flush method
     * is executed, the data measured calculated from the previous flush to the current flush.
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
                '    Memory Usage (Current).. ' . (new TypeInt(memory_get_usage()))->formatBytes()->get(),
                '    Memory Usage (Peak)..... ' . (new TypeInt(memory_get_peak_usage()))->formatBytes()->get(),
                '    Execution Time ......... ' . $executionTime . ' ms',
                '    Cumulative Time ........ ' . self::$cumulativeTime[$label] . ' ms',
            ];

            self::microtimeStart($label);
        }

        return __CLASS__;
    }

    /**
     * This method stores the collected data inside the log file. All the data is converted to a
     * string and it is stored at the bottom of the file. The name of the file is the label that
     * identifies the analysis and each time the store method is called, it overwrites the previous
     * file.
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

            $filename = preg_replace('/[^a-zA-Z0-9]+/', '-', ($label === PERFORMANCE_ANALYSIS_LABEL ? '' : $label) . $_SERVER['REQUEST_URI']);
            $filename = rtrim('analysis_' . ltrim(rtrim($filename, '-'), '-'), '_') . '.log';

            $file = new TypeString(self::LOG_DIRECTORY_PATH . $filename);
            $file->createFile()->fileInsertContents($flushedData, 'w+');

            self::reset($label);
        }
    }

    /**
     * This method starts the microtimer to define when the execution started.
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
     * This method stops the microtimer to define when the execution stoped.
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
     * This method resets the data of an analysis.
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
