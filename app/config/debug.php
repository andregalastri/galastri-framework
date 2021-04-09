<?php
/**
 * This file configures the debug status of the framework. Useful while
 * developing, but turn all them to false when in production because any error
 * can show sensitive data do public users.
 */

return [
    /**
     * Enables the debug tool that will show detailed errors in configuration
     * files, or mistakes with the framework tools. Also enables the PHP
     * display_errors status, which means that internal PHP errors will be
     * shown.
     * 
     * @key displayErrors bool
     */
    'displayErrors' => true,

    /**
     * Enables to display the backlog data with the debug exception information.
     * 
     * @key showBacklogData bool
     */
    'showBacklogData' => false,

    /**
     * Executes the Performance Analysis in entire framework and creates a log
     * file with measures of each method executed by the framework.
     *
     * This is executed to EVERY request made, so, be careful to enable this
     * because this could create a large log file or, if the execution is too
     * big, it can crash the execution.
     *
     * - IMPORTANT: NEVER enable this in production. Every request will generate
     *   a log. If you have many access, a large log data will be created, which
     *   will consume large resources of the server. Use it ONLY for analysis in
     *   a short time period in a test server.
     * - If this crashes the executions of the requests, it is recommended to
     *   execute the PerformanceAnalysis class in specific code parts.
     *
     *   More information in the file: /galastri/modules/PerformanceAnalysis.php
     * 
     * @key performanceAnalysis bool
     */
    'performanceAnalysis' => false,
];
