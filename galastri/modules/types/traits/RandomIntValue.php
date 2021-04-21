<?php

namespace galastri\modules\types\traits;

trait RandomIntValue
{
    /**
     * Undocumented function
     *
     * @param [type] $length
     * @return void
     */
    public function setRandomValue($min = 0, $max = null)
    {
        if ($max === null) {
            $max = getrandmax();
        }

        $this->execSetValue(rand($min, $max));
        return $this;
    }
}
