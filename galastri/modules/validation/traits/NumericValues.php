<?php

namespace galastri\modules\validation\traits;

use galastri\extensions\Exception;

/**
 * This trait has the methods that validates numeric values (integers and floats).
 */
trait NumericValues
{
    /**
     * This method creates a link in the validating chain that checks if the number is lesser than
     * the minimum required. If it is, an exception is thrown.
     *
     * @param  float $minValue                      The minimum value of the number.
     *
     * @return void
     */
    public function minValue(float $minValue): self
    {
        $this->validatingChain[] = function () use ($minValue) {
            if ($this->validatingValue < $minValue) {
                $this->defaultMessageSet(
                    self::VALIDATION_NUMERIC_MIN_VALUE[1],
                    self::VALIDATION_NUMERIC_MIN_VALUE[0],
                    $minValue,
                    $this->validatingValue
                );
                $this->throwFail();
            }
        };

        return $this;
    }

    /**
     * This method creates a link in the validating chain that checks if the number is greater than
     * the maximum allowed. If it is, an exception is thrown.
     *
     * @param  float $maxValue                      The maximum value of the number.
     *
     * @return void
     */
    public function maxValue(float $maxValue): self
    {
        $this->validatingChain[] = function () use ($maxValue) {
            if ($this->validatingValue > $maxValue) {
                $this->defaultMessageSet(
                    self::VALIDATION_NUMERIC_MAX_VALUE[1],
                    self::VALIDATION_NUMERIC_MAX_VALUE[0],
                    $maxValue,
                    $this->validatingValue
                );
                $this->throwFail();
            }
        };

        return $this;
    }

    /**
     * This method is a shortcut to set a minimum and maximum value of the number.
     *
     * @param  float $minValue                      The minimum value.
     *
     * @param  float $maxValue                      The maximum value.
     *
     * @return void
     */
    public function valueRange(float $minValue, float $maxValue): self
    {
        $this->minValue($minValue);
        $this->maxValue($maxValue);

        return $this;
    }
}
