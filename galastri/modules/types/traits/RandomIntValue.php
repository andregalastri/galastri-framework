<?php

namespace galastri\modules\types\traits;

/**
 * This trait has the methods related to random generation of integers.
 */
trait RandomIntValue
{
        
    /**
     * Generate a random number which the minimum and maximum value can bet set with the $min and
     * $max parameters. The number generated is store as the value.
     *
     * @param  int $min                             Minimum value to the generated number.
     * 
     * @param  int|null $max                        Maximum value to the generated number.
     * 
     * @return self
     */
    public function setRandomValue(int $min = 0, ?int $max = null): self
    {
        if ($max === null) {
            $max = getrandmax();
        }

        $this->execSetValue(rand($min, $max));
        return $this;
    }
}
