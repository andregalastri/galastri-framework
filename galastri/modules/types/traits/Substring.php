<?php

namespace galastri\modules\types\traits;

/**
 * This trait has the methods related to manipulate substrings.
 */
trait Substring
{
    /**
     * This method extracts a substring from the current value.
     *
     * @param  int $start                           Starting position for the extraction.
     *
     * @param  int|null $length                     Number os chars to be extracted based on the the
     *                                              starting position. When null, goes until the
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
