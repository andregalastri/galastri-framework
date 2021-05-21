<?php
/**
 * This is the debug configuration file. This file stores an array with debug parameters that are
 * useful while developing. It is good practice to turn them to false when in production because
 * many error messages can show sensitive data do public users.
 */
return [
    /**
     * Enables the debug information that will show detailed error messages during the development.
     * Also enables the PHP display_errors status, which means that internal PHP errors will be
     * shown.
     *
     * bool
     */
    'displayErrors' => true,

    /**
     * Enables to display the backlog data in the the debug information.
     *
     * bool
     */
    'showBacklogData' => false,

    /**
     * Executes the Performance Analysis in entire framework and creates a log file with measures of
     * each method executed by the framework.
     *
     * Be careful to enable this because it can create a large log file or, if the execution is too
     * big, it can crash the execution.
     *
     * - IMPORTANT: NEVER enable this in production. Every request will access the log file and
     *   store its data there. If you have many access it will consume too much resources of the
     *   server (memory, write/read of file, etc.). Use this ONLY in test server.
     *
     * - If this crashes the executions of the requests, it is recommended to execute the
     *   PerformanceAnalysis class in specific parts of the code.
     *
     *   More information in the file: /galastri/modules/PerformanceAnalysis.php
     *
     * bool
     */
    'performanceAnalysis' => false,

    /**
     * Sets the default language of the exception messages. This only refers to the framework
     * exceptions. PHP will still show its errors in english.
     */
    'language' => 'English',
];
