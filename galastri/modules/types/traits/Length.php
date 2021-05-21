<?php

namespace galastri\modules\types\traits;

use galastri\modules\types\TypeInt;

/**
 * This trait has the methods related to length of strings.
 */
trait Length
{
    /**
     * This method returns the number of characters of the string.
     *
     * @return TypeInt
     */
    public function length(): TypeInt
    {
        return new TypeInt(strlen($this->getValue()));
    }
}
