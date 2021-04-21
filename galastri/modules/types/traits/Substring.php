<?php

namespace galastri\modules\types\traits;

trait Substring
{
    /**
     * Undocumented function
     *
     * @param [type] $start
     * @param [type] $length
     * @return void
     */
    public function setSubstring($start, $length = null)
    {
        if ($length === null) {
            $this->execSetValue(substr($this->value, $start));
        } else {
            $this->execSetValue(substr($this->value, $start, $length));
        }

        return $this;
    }

    /**
     * Undocumented function
     *
     * @param [type] $start
     * @param [type] $length
     * @return void
     */
    public function getSubstring($start, $length = null)
    {
        if ($length === null) {
            return substr($this->value, $start);
        } else {
            return substr($this->value, $start, $length);
        }
    }
}
