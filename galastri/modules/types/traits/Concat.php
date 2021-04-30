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
        $this->execConcatenation($values, 0);

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
    public function concatStart(string ...$values): self
    {
        $this->execConcatenation($values, 1);

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
    private function execConcatenation(array $values, int $direction): void
    {
        $spacer = $this->isEmpty() ? '' : $this->concatSpacer;

        if ($direction === 0) {
            $concat = $spacer . implode($this->concatSpacer, $values);
            $this->execHandleValue($this->getValue() . $concat);
        } else {
            $concat = implode($this->concatSpacer, $values) . $spacer;
            $this->execHandleValue($concat . $this->getValue());
        }
    }
}
