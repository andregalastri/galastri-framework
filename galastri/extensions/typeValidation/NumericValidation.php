<?php

namespace galastri\extensions\typeValidation;

use galastri\extensions\Exception;

/**
 * This validation class has methods that allows to check if the informed data has certain
 * characters, or force the data to have some of them. It also strict the length of the data, and
 * many other verifications.
 */
final class NumericValidation implements \Language
{
    /**
     * Importing traits to the class.
     */
    use traits\Common;
    use traits\AllowedValueList;

    /**
     * This method adds a chain link with a function that checks if the number is lesser than the
     * minimum required. If it is, an exception is thrown.
     *
     * @param  float $minValue                      The minimum value of the number.
     *
     * @return void
     */
    public function minValue(float $minValue): void
    {
        $this->chain[] = function () use ($minValue) {
            if ($this->value < $minValue) {
                $this->defaultMessageSet(
                    self::VALIDATION_NUMERIC_MIN_VALUE[1],
                    self::VALIDATION_NUMERIC_MIN_VALUE[0],
                    $minValue,
                    $this->value
                );
                $this->throwErrorMessage();
            }
        };
    }

    /**
     * This method adds a chain link with a function that checks if the number is greater than the
     * maximum allowed. If it is, an exception is thrown.
     *
     * @param  float $maxValue                      The maximum value of the number.
     *
     * @return void
     */
    public function maxValue(float $maxValue): void
    {
        $this->chain[] = function () use ($maxValue) {
            if ($this->value > $maxValue) {
                $this->defaultMessageSet(
                    self::VALIDATION_NUMERIC_MAX_VALUE[1],
                    self::VALIDATION_NUMERIC_MAX_VALUE[0],
                    $maxValue,
                    $this->value
                );
                $this->throwErrorMessage();
            }
        };
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
    public function valueRange(float $minValue, float $maxValue): void
    {
        $this->minValue($minValue);
        $this->maxValue($maxValue);
    }
}
