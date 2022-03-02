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
     * @param  array|int|string $replace            The string that will replace the searched one.
     *
     * @return self
     */
    public function replace(/*array|string*/ $search, /*array|int|string*/ $replace): self
    {
        if (empty($search)) {
            $this->execHandleValue($this->getValue());
        } else {
            $this->execHandleValue(str_replace($search, $replace, $this->getValue()));
        }

        return $this;
    }

    /**
     * This method searches for a substring inside the current value and replaces it by another
     * given string, but do this only once.
     *
     * @param  array|string $search                 The substring that will be searched and
     *                                              replaced.
     *
     * @param int|string $replace                   The string that will replace the searched one.
     *
     * @return self
     */
    public function replaceOnce(/*array|string*/ $search, /*int|string*/ $replace): self
    {
        $value = $this->getValue();

        $search = gettype($search) !== 'array' ? [$search] : $search;

        foreach($search as $searchKey => $searchValue) {
            $pos = empty($searchValue) ? false : strpos($value, $searchValue);
            if ($pos !== false) {
                $value = substr_replace($value, $replace, $pos, strlen($searchValue));
            }
        }

        $this->execHandleValue($value);

        return $this;
    }
}
