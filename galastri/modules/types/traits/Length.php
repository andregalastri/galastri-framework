<?php

namespace galastri\modules\types\traits;

trait Length
{
    /**
     * length
     *
     * @return int
     */
    public function getLength()
    {
        return strlen($this->value);
    }
}
