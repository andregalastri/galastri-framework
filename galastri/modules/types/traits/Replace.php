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
     * @param  string $search                       The substring that will be searched and
     *                                              replaced.
     *
     * @param  string $replace                      The string that will replace the searched one.
     *
     * @return self
     */
    public function setReplace(string $search, string $replace): self
    {
        $this->execSetValue(str_replace($search, $replace, $this->value));

        return $this;
    }
}
