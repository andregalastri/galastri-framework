<?php

namespace galastri\modules;

use galastri\core\Debug;
use galastri\core\Parameters;
use galastri\modules\types\TypeString;

/**
 * This class redirects the request to another URL (internal or external).
 */
final class Redirect implements \Language
{
    /**
     * Defines if the urlRoot parameter will be ignored when redirecting.
     *
     * @var bool
     */
    private static bool $bypassUrlRoot = false;

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
     * This method redirects the request. It uses 2 criteria to determine the path location:
     *
     * 1. If the parameter $location stores a string that matches a key configured in URL alias
     *    configuration, then the value of that key will be used as a redirect location.
     *
     * 2. If the location isn't in the URL alias configuration, then the string itself will be used
     *    as the redirect location.
     *
     * The location value will be tested again. If it starts with a network protocol, like http or
     * ftp, this means that the location is external. However, if it is not, then it will check if
     * $bypassUrlRoot property is true. If false, then the urlRoot parameter configuration will be
     * attached in front of the location, if true, the urlRoot configuration will be ignored.
     *
     * @param  string $location                     Can be an internal or external URL or an URL
     *                                              alias set in the URL alias configuration file.
     *
     * @param  string ...$printfData                The location can have %s tags to be replaced by
     *                                              dynamic values. These array values will replace
     *                                              every %s tag in the location string, in order of
     *                                              appearance.
     *
     * @return void
     */
    public static function to(/*string*/ $location, string ...$printfData): void
    {
        Debug::setBacklog();

        /**
         * Sets the location string as a TypeString, which cannot be empty. The location is filtered
         * to remove special chars from the edges of the string.
         */
        $locationString = new TypeString();
        $locationString
            ->denyEmpty()
            ->onFail([
                self::INVALID_LOCATION_DATA_TYPE[1],
                self::INVALID_LOCATION_DATA_TYPE[0]
            ])
            ->set(GALASTRI_URL_TAGS[$location] ?? $location);

        $location = self::sanitize($locationString->get());

        /**
         * Checks if the location is external by searching if there is a protocol in the string.
         */
        preg_match(REDIRECT_IDENFITY_PROTOCOLS_REGEX, $location, $match);

        /**
         * If there is no protocol, then it will set the URL as internal.
         */
        if (empty($match)) {
            $urlRoot = self::$bypassUrlRoot ? '' : '/' . self::sanitize(Parameters::getUrlRoot());
            $location = '/' . self::sanitize($urlRoot . $location);
        }

        PerformanceAnalysis::flush(PERFORMANCE_ANALYSIS_LABEL)::store(PERFORMANCE_ANALYSIS_LABEL);

        /**
         * Redirect the request.
         */
        exit(header('Location: ' . vsprintf($location, $printfData)));
    }

    /**
     * This method sets the $bypassUrlRoot property as true, to make the redirect ignore the urlRoot
     * parameter, configured in route configuration.
     *
     * @return self
     */
    public static function bypassUrlRoot()// : self
    {
        self::$bypassUrlRoot = true;
        return __CLASS__;
    }

    /**
     * This method removes every special char and spaces from the edges of a string.
     *
     * @param  string $string                       The string to be sanitized.
     *
     * @return string
     */
    private static function sanitize(/*string*/ $string): string
    {
        return (new TypeString($string))->trim('/?:;<>,.[]{}!@#$%&*()_+-=\\|')->get();
    }
}
