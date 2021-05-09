<?php

namespace galastri\modules;

use galastri\core\Debug;
use galastri\core\Parameters;
use galastri\modules\types\TypeString;

/**
 * This module class helps to redirect the request to another path or URL, even external.
 */
final class Redirect implements \Language
{
    /**
     * Defines if the urlRoot parameter will be ignored (true) or not (false).
     *
     * @var bool
     */
    private static bool $bypassUrlRoot = false;

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
     * Method that redirects the request. It uses 2 criteria to determine the path location:
     *
     * 1. If the parameter $location stores a string that matches a key configured in
     *    \app\config\url-alias.php file, then the value of that key will be used as redirect
     *    location.
     *
     * 2. If the location isn't in the URL Alias configuration, then the string itself will be used
     *    as redirect location.
     *
     * The location value will be tested again. If the start of the string matches a network
     * protocol, like http or ftp, this means that the location is external and will be used as is.
     *
     * However, if it is not, then it will check if $bypassUrlRoot is true. It it is NOT, then the
     * urlRoot configuration will be attached in front of the location. If $bypassUrlRoot is false,
     * then the urlRoot configuration will be ignored.
     *
     * @param  string $location                     Internal path, external URL or URL Alias name
     *                                              set in \app\config\url-alias.php file.
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

        $locationString = new TypeString();
        $locationString
            ->denyEmpty()
            ->onFail([
                self::INVALID_LOCATION_DATA_TYPE[1],
                self::INVALID_LOCATION_DATA_TYPE[0]
            ])
            ->set(GALASTRI_URL_TAGS[$location] ?? $location);

        $location = self::sanitize($locationString->get());

        preg_match(REDIRECT_IDENFITY_PROTOCOLS_REGEX, $location, $match);

        if (empty($match)) {
            $urlRoot = self::$bypassUrlRoot ? '' : '/' . self::sanitize(Parameters::getUrlRoot());
            $location = '/' . self::sanitize($urlRoot . $location);
        }

        PerformanceAnalysis::flush(PERFORMANCE_ANALYSIS_LABEL)::store(PERFORMANCE_ANALYSIS_LABEL);
        exit(header('Location: ' . vsprintf($location, $printfData)));
    }

    /**
     * Ignores the urlRoot parameter configured in \app\config\project.php file when the location is
     * internal and uses the exactly location given.
     *
     * @return \galastri\modules\Redirect
     */
    public static function bypassUrlRoot(): string /*self*/
    {
        self::$bypassUrlRoot = true;
        return __CLASS__;
    }

    /**
     * Remove every special char and spaces that can be in the beginning and ending of a string.
     *
     * @param  string $string                       The string to be sanitized.
     *
     * @return string
     */
    private static function sanitize(/*string*/ $string): string
    {
        Debug::setBacklog();

        $string = new TypeString($string);

        return $string->trim('/?:;<>,.[]{}!@#$%&*()_+-=\\|')->get();
    }
}
