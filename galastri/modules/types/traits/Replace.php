<?php

namespace galastri\modules\types\traits;

trait Replace
{
    /**
     * Undocumented function
     *
     * @param [type] ...$values
     * @return void
     */
    public function setReplace($search, $replace)
    {
        $this->execSetValue(str_replace($search, $replace, $this->value));

        return $this;
    }
}
