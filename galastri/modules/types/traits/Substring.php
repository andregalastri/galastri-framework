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
    public function substring(int $start, ?int $length = null): self
    {
        if ($length === null) {
            $this->execHandleValue(substr($this->getValue(), $start));
        } else {
            $this->execHandleValue(substr($this->getValue(), $start, $length));
        }

        return $this;
    }
}
