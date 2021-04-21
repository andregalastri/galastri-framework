<?php

namespace galastri\modules\types\traits;

/**
 * This trait has the methods related to concatenation of strings.
 */
trait Concat
{
    /**
     * Stores a string that will be used as spacer between the values to concatenated.
     *
     * @var string
     */
    private string $concatSpacer = '';

    /**
     * Defines if the $concatSpacer value will have its value reseted after the first concatenation
     * (true) or not (false).
     *
     * @var bool
     */
    private bool $tmpConcatSpacer = false;
    
    /**
     * Concatenate the given parameters at the end of the current value.
     *
     * @param string ...$values                     Values that will be concatenated in the current
     *                                              object value. If an spacer is set, then all
     *                                              values will be placed between the defined
     *                                              spacer.
     *
     * @return self
     */
    public function concat(string ...$values): self
    {
        $this->execConcatenation(0);
        
        return $this;
    }

    /**
     * Concatenate the given parameters at the start of the current value.
     *
     * @param string ...$values                     Values that will be concatenated in the current
     *                                              object value. If an spacer is set, then all
     *                                              values will be placed between the defined
     *                                              spacer.
     *
     * @return self
     */
    public function concatFromStart(string ...$values): self
    {
        $this->execConcatenation(1);

        return $this;
    }

    /**
     * Defines a string that will work as a spacer between the concatenated values.
     *
     * @param string $value                         The string that will be used as spacer.
     *
     * @return self
     */
    public function concatSpacer(string $value): self
    {
        $this->concatSpacer = $value;
        
        return $this;
    }

    /**
     * Defines a string that will work as a spacer between the concatenated values but its value
     * will be reseted after the first concatenation.
     *
     * @param string $value                         The string that will be used as spacer.
     *
     * @return self
     */
    public function concatTmpSpacer(string $value): self
    {
        $this->tmpConcatSpacer = true;
        $this->concatSpacer = $value;
        
        return $this;
    }
    
    /**
     * Executes the concatenation. It sets an $spacer variable if the current value is not empty.
     * Then, it checks the direction. If it is 0, the concatenation will occur at the end of the
     * current value. If it is 1, the concatenation will occur at the start of the current value.
     * 
     * Finally, it checks if the concatenation spacer is temporary. If it is, then its value will be
     * set as empty string '', because it only lasts one concatenation execution.
     *
     * @param  int $direction                       When 0, means that the concatenation will occur
     *                                              at the end of the current value. When 1, means
     *                                              that the concatenation will occur at the start
     *                                              of the current value.
     * 
     * @return void
     */
    private function execConcatenation(int $direction): void
    {
        $spacer = $this->isEmpty() ? '' : $this->concatSpacer;
        
        if ($direction === 0) {
            $concat = $spacer . implode($this->concatSpacer, $values);
            $this->execSetValue($this->value . $concat);
        } else {
            $concat = implode($this->concatSpacer, $values) . $spacer;
            $this->execSetValue($concat . $this->value);
        }

        if ($this->tmpConcatSpacer) {
            $this->tmpConcatSpacer = false;
            $this->concatSpacer = '';
        }
    }
}
