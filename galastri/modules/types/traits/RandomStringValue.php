<?php

namespace galastri\modules\types\traits;

/**
 * This trait has the methods related to random generation of strings.
 */
trait RandomStringValue
{    
    /**
     * Generate a random string with hexadecimal chars, which the length can bet set with the length
     * parameter and store as the value.
     * 
     * @param  int $length                          Length of the random generated string.
     * 
     * @return self
     */
    public function setRandomValue(int $length = 15): self
    {
        if (function_exists("random_bytes")) {
            $bytes = random_bytes(ceil($length / 2));
        } elseif (function_exists("openssl_random_pseudo_bytes")) {
            $bytes = openssl_random_pseudo_bytes(ceil($length / 2));
        } else {
            throw new Exception(self::SECURE_RANDOM_GENERATOR_NOT_FOUND[0], self::SECURE_RANDOM_GENERATOR_NOT_FOUND[1]);
        }

        $this->execSetValue(bin2hex($bytes));
        return $this;
    }
}
