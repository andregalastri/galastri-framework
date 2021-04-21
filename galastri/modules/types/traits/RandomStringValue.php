<?php

namespace galastri\modules\types\traits;

trait RandomStringValue
{
    /**
     * Undocumented function
     *
     * @param [type] $length
     * @return void
     */
    public function setRandomValue($length = 15)
    {
        if (function_exists("random_bytes")) {
            $bytes = random_bytes(ceil($length / 2));
        } elseif (function_exists("openssl_random_pseudo_bytes")) {
            $bytes = openssl_random_pseudo_bytes(ceil($length / 2));
        } else {
            throw new Exception("No cryptographically secure random function available");
        }

        $this->execSetValue(bin2hex($bytes));
        return $this;
    }
}
