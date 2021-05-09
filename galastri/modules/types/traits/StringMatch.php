<?php

namespace galastri\modules\types\traits;

/**
 * This trait has the methods related to manipulate substrings.
 */
trait StringMatch
{
    public function regexMatch(string $regex, ?array &$crudeMatch = null): self
    {
        preg_match_all($regex, $this->getValue(), $crudeMatch);

        $this->execHandleValue($crudeMatch);

        return $this;
    }
}
