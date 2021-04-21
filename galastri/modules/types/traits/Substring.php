<?php

namespace galastri\modules\types\traits;

/**
 * This trait has the methods related to manipulate substrings.
 */
trait Substring
{
        
    /**
     * This method extract a substring of the current value and store it in the value, replacing the
     * previous string.
     *
     * @param  int $start                           Start char position to extraction.
     * 
     * @param  int|null $length                     Number os chars to be extracted based on the the
     *                                              starting position. When null, it goes until the
     *                                              end of the string.
     * 
     * @return self
     */
    public function setSubstring(int $start, ?int $length = null): self
    {
        if ($length === null) {
            $this->execSetValue(substr($this->value, $start));
        } else {
            $this->execSetValue(substr($this->value, $start, $length));
        }

        return $this;
    }

        
    /**
     * This method extract a substring of the current value and just return it, without changing it.
     *
     * @param  int $start                           Start char position to extraction.
     * 
     * @param  int|null $length                     Number os chars to be extracted based on the the
     *                                              starting position. When null, it goes until the
     *                                              end of the string.
     * 
     * @return null|string
     */
    public function getSubstring(int $start, ?int $length = null): ?string
    {
        if ($length === null) {
            return substr($this->value, $start);
        } else {
            return substr($this->value, $start, $length);
        }
    }
}
