<?php

namespace galastri\modules;

use galastri\core\Parameters;

final class Authentication
{
    private static array $reservedFields = ['token', 'ip', 'cookieExpiration'];
    private static array $authentication;
    private static ?string $authTag = null;
    private static int $defaultCookieExpiration = 86400;

    private function __construct()
    {
    }

    public static function configure(string $authTag, ?int $cookieExpiration = null): string // self
    {
        self::$authentication[$authTag] = [
            'token' => base64_encode(random_bytes(48)),
            'ip' => $_SERVER['REMOTE_ADDR'],
            'cookieExpiration' => $cookieExpiration ?? 86400,
        ];

        self::$authTag = $authTag;

        return __CLASS__;
    }

    public static function setField(string $field, string $value): string // self
    {
        Debug::setBacklog();

        if(in_array($field, self::$reservedFields)) {
            throw new Exception('self::VIEW_INVALID_DATA_KEY[1]', 'self::VIEW_INVALID_DATA_KEY[0]');
        } else {
            $authTag = self::$authTag;

            self::$authentication[$authTag][$field] = $value;
        }
        return __CLASS__;
    }

    public static function createSession(string $authTag): void
    {
        session_start();

        foreach (self::$authentication[$authTag] as $field => $value) {
            $_SESSION[$authTag][$field] = $value;
        }

        setcookie(
            $authTag,
            self::$authentication[$authTag]['token'],
            time() + self::$authentication[$authTag]['cookieExpiration'],
            '/'
        );
        session_regenerate_id();
        session_write_close();
    }

    public static function update(string $authTag): void
    {
        if(self::check($authTag)){
            self::storeInSession($authTag);
        }
    }

    public static function unset(string $authTag): bool
    {
        session_start();

        if(self::check($authTag)){
            unset($_SESSION[$authTag]);
            setcookie($authTag, null, time() - 3600, '/');
            unset($_COOKIE[$authTag]);
            return true;
        }

        return false;
    }

    public static function destroy(): void
    {
        session_start();

        foreach($_SESSION as $key => $value){
            setcookie($key, null, time() - 3600, '/');
            unset($_COOKIE[$key]);
        }

        session_unset();
        session_destroy();
    }

    public static function getData(string $authTag): ?array
    {
        session_start();

        if(self::check($authTag)){
            $session =  [];
            foreach($_SESSION[$authTag] as $field => $value) {
                if (in_array($field, self::$reservedFields)) {
                    continue;
                }
                $session[] = $value;
            }

            session_write_close();
            return $session;
        }

        session_write_close();
        return null;
    }

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
