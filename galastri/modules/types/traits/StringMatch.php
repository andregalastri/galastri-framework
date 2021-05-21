<?php

namespace galastri\modules\types\traits;
/**
 * This trait has the methods related to string matches.
 */
trait StringMatch
{

    /**
     * This method searches for a regex pattern in the current value and stores the matches.
     *
     * @param  mixed $regex                         The regex pattern.
     *
     * @param  mixed $matches                       Returns the matches to the parameter via
     *                                              reference, which allows to store its value
     *                                              directly to a variable.
     *
     * @return self
     */
    public function regexMatch(string $regex, ?array &$matches = null): self
    {
        preg_match_all($regex, $this->getValue(), $matches);

        $this->execHandleValue($matches);

        return $this;
    }
}
