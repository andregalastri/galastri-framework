<?php

/**
 * Path that points to your project directory (the same that contains the app and galastri folders).
 */
$projectDirectory = __DIR__.'/../';

/**
 * Importing the bootstrap file that starts the framework.
 */
require_once(rtrim($projectDirectory, '/').'/galastri/bootstrap.php');
unset($projectDirectory);