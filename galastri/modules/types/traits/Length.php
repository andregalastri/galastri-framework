<?php

namespace galastri\modules\types\traits;

/**
 * This trait has the methods related to length of strings.
 *
 * IMPORTANT NOTE:
 * - The maxLength() and minLength() methods are part of the validation class \galastri
 *   \extensions\typeValidation\StringValidation. Everything that concerns about validation of data
 *   is responsibility of the validation classes.
 */
trait Length
{
    /**
     * Returns the number of chars of the string.
     *
     * @return int
     */
    public function getLength(): int
    {
        return strlen($this->value);
    }
}
