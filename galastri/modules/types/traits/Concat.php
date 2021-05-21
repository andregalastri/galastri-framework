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
     * This method concatenates the given values at the end of the current value. It passes the
     * value to the execConcatenation method with the 0 parameter, indicating that the values need
     * to be placed at the end of the current value.
     *
     * @param string ...$values                     Values that will be concatenated in the current
     *                                              value. If an spacer is set, then all values will
     *                                              be placed between the defined spacer.
     *
     * @return self
     */
    public function concat(string ...$values): self
    {
        $this->execConcatenation($values, 0);

        return $this;
    }

    /**
     * This method concatenates the given parameters at the start of the current value. It passes
     * the value to the execConcatenation method with the 1 parameter, indicating that the values
     * need to be placed at the start of the current value.
     *
     * @param string ...$values                     Values that will be concatenated in the current
     *                                              value. If an spacer is set, then all values will
     *                                              be placed between the defined spacer.
     *
     * @return self
     */
    public function concatStart(string ...$values): self
    {
        $this->execConcatenation($values, 1);

        return $this;
    }

    /**
     * This method defines a string that will work as a spacer between the concatenated values.
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
     * This method executes the concatenation.
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
        /**
         * It sets an $spacer variable if the current value is not empty.
         */
        $spacer = $this->isEmpty() ? '' : $this->concatSpacer;

        /**
         * If the direction is 0, the concatenation will occur at the end of the current value.
         */
        if ($direction === 0) {
            $concat = $spacer . implode($this->concatSpacer, $values);
            $this->execHandleValue($this->getValue() . $concat);

        /**
         * If the direction is 1, the concatenation will occur at the start of the current value.
         */
        } else {
            $concat = implode($this->concatSpacer, $values) . $spacer;
            $this->execHandleValue($concat . $this->getValue());
        }
    }
}
