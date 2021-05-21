<?php

namespace galastri\modules;

use galastri\core\Debug;
use galastri\core\Parameters;
use galastri\extensions\Exception;

/**
 * This class checks if the session of the user have an authentication tag defined, which allows it
 * to access protected routes that uses authTag parameters.
 *
 * This class uses session to store the state of the user, which uses cookies on the client side.
 * The cookie stores o
 */
final class Authentication
{
    /**
     * Stores an array with the fields created by the user for a specific authTag. These fields can
     * store any primitive value. It will be stored in the user session and create a cookie to store
     * information in the client side.
     *
     * @var array
     */
    private static array $fields;

    /**
     * Stores an array with an token for a specific authTag. The token is stored in the user session
     * and in the cookie on the client side. This token is always compared to check if the user
     * session is still valid.
     *
     * @var array
     */
    private static array $token;

    /**
     * Stores an array with the IP of the user for a specific authTag. The ip helps the purpose of
     * the validation of the session, but is disabled by default. To validate, the $ipCheck
     * parameter needs to be true when the validate method is called.
     *
     * @var array
     */
    private static array $ip;

    /**
     * Stores an array with the cookie expiration time (in seconds) for a specific authTag. If not specified, the
     * default cookie expiration time will be 86400 seconds (24 hours).
     *
     * @var array
     */
    private static array $cookieExpiration;

    /**
     * Temporarily stores the authentication tag from the configure method to be used in the
     * setField method.
     *
     * @var null|string
     */
    private static ?string $authTag = null;

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
     * This method starts the authentication tag configuration, defining which authTag will be set.
     *
     * @param  string $authTag                      The authTag parameter that will be configured.
     *
     * @param  int|null $cookieExpiration           Cookie expiration time. The cookie stores the
     *                                              session fields (but not PHP session itself, only
     *                                              this framework fields).
     *
     * @return self
     */
    public static function configure(string $authTag, ?int $cookieExpiration = null)// : self
    {
        /**
         * Stores the internal properties that will be used by the this class.
         */
        self::$token[$authTag] = base64_encode(random_bytes(48));
        self::$ip[$authTag] = $_SERVER['REMOTE_ADDR'];
        self::$cookieExpiration[$authTag] = $cookieExpiration ?? 86400;

        self::$authTag = $authTag;

        return __CLASS__;
    }

    /**
     * This method sets fields to store values in the session and its cookie to be used in
     * other requests.
     *
     * @param  string $field                        Name that identifies the value.
     *
     * @param  bool|int|null|string $value          The value that will be stored.
     *
     * @param  null|string $authTag                 Optional: Defines to which authTag the field
     *                                              will be stored. If not defined, it will use the
     *                                              current value of the $authTag property. If the
     *                                              property isn't defined, then an exception will
     *                                              be thrown.
     *
     * @return self
     */
    public static function setField(string $field, /*bool|int|null|string*/ $value, ?string $authTag = null)// : self
    {
        Debug::setBacklog();

        /**
         * If there is an authentication tag, then the field will be stored in its array.
         */
        if ($authTag ?? self::$authTag) {
            self::$fields[$authTag ?? self::$authTag][$field] = $value;

        /**
         * If there is no authentication tag defined, it will throw an exception.
         */
        } else {
            throw new Exception(self::UNDEFINED_AUTH_TAG[1], self::UNDEFINED_AUTH_TAG[0]);
        }

        return __CLASS__;
    }

    /**
     * This method executes the creation of the session and will store the values in the session and
     * in the cookie on the client side.
     *
     * This also updates a started session, but it is better to use the update method for a better
     * understanding code.
     *
     * @param  null|string $authTag                 The authentication tag that will be created.
     *
     * @return void
     */
    public static function create(?string $authTag = null): void
    {
        /**
         * Also checks if there is an authentication tag set before continue.
         */
        if ($authTag ?? self::$authTag) {
            $authTag = $authTag ?? self::$authTag;
        } else {
            throw new Exception(self::UNDEFINED_AUTH_TAG[1], self::UNDEFINED_AUTH_TAG[0]);
        }

        /**
         * If the configure method wasn't called before this method, than an exception is thrown.
         */
        if (!isset(self::$token[$authTag])) {
            throw new Exception(self::UNCONFIGURED_AUTH_TAG[1], self::UNCONFIGURED_AUTH_TAG[0], [$authTag]);
        }

        session_start();

        /**
         * Stores the internal properties in the session.
         */
        $_SESSION[$authTag]['token'] = self::$token[$authTag];
        $_SESSION[$authTag]['ip'] = self::$ip[$authTag];
        $_SESSION[$authTag]['cookieExpiration'] = self::$cookieExpiration[$authTag];

        /**
         * Stores the internal properties in the user cookie.
         */
        setcookie(
            $authTag,
            self::$token[$authTag],
            time() + self::$cookieExpiration[$authTag],
            '/'
        );

        /**
         * Stores the user's fields in the session.
         */
        foreach (self::$fields[$authTag] as $field => $value) {
            $_SESSION[$authTag]['fields'][$field] = $value;
        }

        /**
         * Stores the user's fields in the cookie.
         */
        setcookie(
            $authTag.'Fields',
            serialize($_SESSION[$authTag]['fields']),
            time() + self::$cookieExpiration[$authTag],
            '/'
        );

        session_regenerate_id();
        session_write_close();
    }

    /**
     * This method is just a alias for the create method, which also updates current sessions. It
     * makes the user's code more understandable.
     *
     * @param  string $authTag                      The authentication tag that will be updated.
     *
     * @return void
     */
    public static function update(string $authTag): void
    {
        if(self::check($authTag)){
            self::create($authTag);
        }
    }

    /**
     * This method remove an authTag from the session and cookies.
     *
     * @param  string $authTag                      The authentication tag that will be removed.
     *
     * @return bool
     */
    public static function unset(string $authTag): bool
    {
        session_start();

        if(self::check($authTag)){
            unset($_SESSION[$authTag]);

            setcookie($authTag, null, time() - 3600, '/');
            unset($_COOKIE[$authTag]);

            setcookie($authTag.'Fields', null, time() - 3600, '/');
            unset($_COOKIE[$authTag.'Fields']);
            return true;
        }

        session_write_close();

        return false;
    }

    /**
     * This method destroys all the session and cookies from the user.
     *
     * @return void
     */
    public static function destroy(): void
    {
        session_start();

        foreach($_SESSION as $key => $value){
            setcookie($key, null, time() - 3600, '/');
            unset($_COOKIE[$key]);
            unset($_COOKIE[$key.'Fields']);
        }

        session_unset();
        session_destroy();
    }

    /**
     * This method gets all the fields from an authentication tag from the session.
     *
     * @param  string $authTag                      The authentication tag that will have its data
     *                                              recovered from the session.
     *
     * @return array|null
     */
    public static function getSession(string $authTag): ?array
    {
        session_start();

        if(self::check($authTag)){
            $sessionFields = $_SESSION[$authTag]['fields'];

            session_write_close();
            return $sessionFields;
        }

        session_write_close();
        return null;
    }

    /**
     * This method gets all the fields from an authentication tag from the cookie.
     *
     * @param  string $authTag                      The authentication tag that will have its data
     *                                              recovered from the cookie.
     *
     * @return array|null
     */
    public static function getCookies(string $authTag): ?array
    {
        session_start();

        if(self::check($authTag)){
            $cookie = unserialize($_COOKIE[$authTag.'Fields']);

            session_write_close();
            return $cookie;
        }

        session_write_close();
        return null;
    }

    /**
     * This method checks if an authentication tag is valid for the user. It checks the token and,
     * optionally, the ip of the used, comparing the session data with the cookie data.
     *
     * @param  string $authTag                      The authentication tag that will be checked.
     *
     * @param  mixed $ipCheck                       When true, compared the IP of the cookie with
     *                                              the current user.
     *
     * @return bool
     */
    public static function validate(string $authTag, bool $ipCheck = false): bool
    {
        session_start();

        if(self::check($authTag)){
            if($_SESSION[$authTag]['token'] === $_COOKIE[$authTag]){
                if($ipCheck){
                    if($_SESSION[$authTag]['ip'] === $_SERVER['REMOTE_ADDR']){
                        session_write_close();
                        return true;
                    } else {
                        session_write_close();
                        return false;
                    }
                } else {
                    session_write_close();
                    return true;
                }
            }
        }

        session_write_close();
        return false;
    }

    /**
     * This method compares the token of the current session of the user with the cookie stored in
     * the client side of the user.
     *
     * @param  string $authTag                      The authentication tag that will be checked.
     *
     * @return bool
     */
    public static function tokenCompare($authTag): bool
    {
        session_start();

        if(self::check($authTag)){
            if($_SESSION[$authTag]['token'] === $_COOKIE[$authTag]){
                return true;
            }
        }

        session_write_close();
        return false;
    }

    /**
     * This method is a simple internal verification if there is an session started.
     *
     * @param  string $authTag                      The authentication tag that will be checked.
     *
     * @return bool
     */
    private static function check(string $authTag): bool
    {
        if(session_status() === PHP_SESSION_NONE){
            return false;
        }

        if(!isset($_SESSION[$authTag]['token'])){
            return false;
        }
        return true;
    }
}
