<?php

namespace galastri\modules\types\traits;

use galastri\modules\types\TypeArray;

/**
 * This trait has the methods related to manipulate substrings.
 */
trait StringMatch
{
        
    public function regexMatch(string $regex, ?array &$crudeMatch = null): self
    {
        preg_match_all($regex, $this->getValue(), $crudeMatch);

        $this->execHandleValue(new TypeArray($crudeMatch));

        return $this;
    }
}
