<?php

namespace galastri\modules\types\traits;

/**
 * This trait has the methods related to replace a substring to another.
 */
trait Replace
{
    /**
     * This method searches for a substring inside the current value and replaces it for another
     * given string.
     *
     * @param  array|string $search                 The substring that will be searched and
     *                                              replaced.
     *
     * @param  array|string $replace                The string that will replace the searched one.
     *
     * @return self
     */
    public function replace($search, $replace): self
    {
        $this->execHandleValue(str_replace($search, $replace, $this->getValue()));

        return $this;
    }
}
