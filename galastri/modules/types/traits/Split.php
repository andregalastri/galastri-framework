<?php

namespace galastri\modules\types\traits;

trait Split
{
    /**
     * split
     *
     * @return array
     */
    public function split($delimiter, $limit = PHP_INT_MAX)
    {
        return explode($delimiter, $this->value, $limit);
    }
}
