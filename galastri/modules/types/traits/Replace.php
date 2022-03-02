<?php

namespace galastri\modules\types\traits;

/**
 * This trait has the methods related to replace a substring to another.
 */
trait Replace
{
    /**
     * This method searches for a substring inside the current value and replaces it by another
     * given string.
     *
     * @param  array|string $search                 The substring that will be searched and
     *                                              replaced.
     *
     * @param  array|string $replace                The string that will replace the searched one.
     *
     * @return self
     */
    public function replace(/*array|string*/ $search, /*array|string*/ $replace): self
    {
        $this->execHandleValue(str_replace($search, $replace, $this->getValue()));

        return $this;
    }

    /**
     * This method searches for a substring inside the current value and replaces it by another
     * given string, but do this only once.
     *
     * @param  array|string $search                 The substring that will be searched and
     *                                              replaced.
     *
     * @param  array|string $replace                The string that will replace the searched one.
     *
     * @return self
     */
    public function replaceOnce(/*array|string*/ $search, /*array|string*/ $replace): self
    {
        $value = $this->getValue();

        $pos = strpos($value, $search);
        if ($pos !== false) {
            $value = substr_replace($value, $replace, $pos, strlen($search));
        }

        $this->execHandleValue($value);

        return $this;
    }
}
