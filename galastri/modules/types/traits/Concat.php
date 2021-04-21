<?php

namespace galastri\modules\types\traits;

trait Concat
{
    private string $concatSpacer = '';
    
    /**
     * Undocumented function
     *
     * @param [type] ...$values
     * @return void
     */
    public function concat(...$values)
    {
        $spacer = $this->isEmpty() ? '' : $this->concatSpacer;
        $concat = $spacer . implode($this->concatSpacer, $values);
        $this->concatSpacer = '';

        $this->execSetValue($this->value . $concat);
        
        return $this;
    }

    /**
     * Undocumented function
     *
     * @param [type] ...$values
     * @return void
     */
    public function concatFromStart(...$values)
    {
        $spacer = $this->isEmpty() ? '' : $this->concatSpacer;
        $concat = implode($this->concatSpacer, $values) . $spacer;
        $this->concatSpacer = '';

        $this->execSetValue($concat . $this->value);

        return $this;
    }

    /**
     * Undocumented function
     *
     * @param [type] $value
     * @return void
     */
    public function concatSpacer($value)
    {
        $this->concatSpacer = $value;
        
        return $this;
    }
}
