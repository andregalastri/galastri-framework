<?php

namespace galastri\modules\types\traits;

/**
 * This trait has the methods related to math.
 */
trait Math
{

    /**
     * This method sums the current value with the given number.
     *
     * @param  float $number                        The number that will be added to the value.
     *
     * @return self
     */
    public function sum(float $number): self
    {
        $this->execHandleValue($this->getValue() + $number);

        return $this;
    }

    /**
     * This method subtracts the current value by the given number.
     *
     * @param  float $number                        The number that will subtract the value.
     *
     * @return self
     */
    public function subtract(float $number): self
    {
        $this->execHandleValue($this->getValue() - $number);

        return $this;
    }

    /**
     * This method subtracts the given number by the current value.
     *
     * @param  float $number                        The number that will be subtracted by the value.
     *
     * @return self
     */
    public function minuend(float $number): self
    {
        $this->execHandleValue($number - $this->getValue());

        return $this;
    }

    /**
     * This method multiplies the current value by the given number.
     *
     * @param  float $number                        The number that will be multiplied by the value.
     *
     * @return self
     */
    public function multiply(float $number): self
    {
        $this->execHandleValue($this->getValue() * $number);

        return $this;
    }

    /**
     * This method divides the current value by the given number.
     *
     * @param  float $number                        The number that will divide the value.
     *
     * @return self
     */
    public function divide(float $number): self
    {
        $this->execHandleValue($this->getValue() / $number);

        return $this;
    }

    /**
     * This method divides the given number by the current value.
     *
     * @param  float $number                        The number that will be divided by the value.
     *
     * @return self
     */
    public function dividend(float $number): self
    {
        $this->execHandleValue($number / $this->getValue());

        return $this;
    }

    /**
     * This method sets the remainder of the division between the current value by the given number
     * as the value.
     *
     * @param  float $number                        The number that will divide the value.
     *
     * @return self
     */
    public function mod(float $number): self
    {
        $this->execHandleValue($this->getValue() % $number);

        return $this;
    }

    /**
     * This method raises the current value to power of the given exponent.
     *
     * @param  float $exponent                      The number that will raises the current value to
     *                                              power.
     *
     * @return self
     */
    public function pow(float $exponent): self
    {
        $this->execHandleValue(pow($this->getValue(), $exponent));

        return $this;
    }

    /**
     * This method raises the given base to power with the current value as exponent.
     *
     * @param  float $base                          The number that will be raised to power with the
     *                                              current value as exponent.
     *
     * @return self
     */
    public function powBase(float $base): self
    {
        $this->execHandleValue(pow($exponent, $this->getValue()));

        return $this;
    }

    /**
     * This method increments the current value by 1.
     *
     * @return self
     */
    public function increment(): self
    {
        if ($this->handlingValue !== null) {
            $this->handlingValue++;
        } else {
            $this->storedValue++;
        }

        return $this;
    }

    /**
     * This method decrements the current value by 1.
     *
     * @return self
     */
    public function decrement(): self
    {
        if ($this->handlingValue !== null) {
            $this->handlingValue--;
        } else {
            $this->storedValue--;
        }

        return $this;
    }

    /**
     * This method rounds up the current value. It also can round up based on a given multiple.
     *
     * @param  float $nearestMultiple               The nearest multiple to round up.
     *
     *                                              Examples:
     *
     *                                                  $myFloat->setValue(3.5)->ceil(5)
     *                                                  - Result: 5
     *
     *                                                  $myFloat->setValue(6)->ceil(5)
     *                                                  - Result: 10
     *
     *                                                  $myFloat->setValue(7.12)->ceil(0.5)
     *                                                  - Result: 7.5
     *
     * @return self
     */
    public function ceil(float $nearestMultiple = 1): self
    {
        $result = ceil($this->getValue() / $nearestMultiple) * $nearestMultiple;

        if ($result % 2 > 1 and static::VALUE_TYPE === 'integer') {
            $result = ceil($result);
        }

        $this->execHandleValue($result);

        return $this;
    }

    /**
     * This method rounds down the current value. It also can round down based on a given multiple.
     *
     * @param  float $nearestMultiple               The nearest multiple to round down.
     *
     *                                              Examples:
     *
     *                                                  $myFloat->setValue(3.5)->floor(5)
     *                                                  - Result: 0
     *
     *                                                  $myFloat->setValue(6)->floor(5)
     *                                                  - Result: 5
     *
     *                                                  $myFloat->setValue(7.12)->floor(0.5)
     *                                                  - Result: 7.0
     *
     * @return self
     */
    public function floor(float $nearestMultiple = 1): self
    {
        $result = floor($this->getValue() / $nearestMultiple) * $nearestMultiple;

        if ($result % 2 > 1 and static::VALUE_TYPE === 'integer') {
            $result = floor($result);
        }

        $this->execHandleValue($result);

        return $this;
    }

    /**
     * This method rounds the current value up or down automatically, based on the nearest multiple.
     *
     * @param  float $nearestMultiple               The nearest multiple to round up or down.
     *
     *                                              Examples:
     *
     *                                                  $myFloat->setValue(3.5)->round(5)
     *                                                  - Result: 5
     *
     *                                                  $myFloat->setValue(6.3)->round(5)
     *                                                  - Result: 5
     *
     *                                                  $myFloat->setValue(7.12)->floor(0.5)
     *                                                  - Result: 7.0
     *
     * @return self
     */
    public function round(float $nearestMultiple = 1): self
    {
        $result = round($this->getValue() / $nearestMultiple) * $nearestMultiple;

        if ($result % 2 > 1 and static::VALUE_TYPE === 'integer') {
            $result = round($result);
        }

        $this->execHandleValue($result);

        return $this;
    }

    /**
     * This method sets the current value to its absolute value.
     *
     * @return self
     */
    public function abs(): self
    {
        $this->execHandleValue(abs($this->getValue()));

        return $this;
    }

    /**
     * This method sets the current value to a root (square root, cube root, etc).
     *
     * @param  int $degree                          The degree of the root. Square root is 2, cube
     *                                              root is 3, and so on.
     *
     * @return self
     */
    public function root(int $degree): self
    {
        if ($degree === 0) {
            throw new Exception(
                self::MATH_ROOT_CANNOT_BE_ZERO[1],
                self::MATH_ROOT_CANNOT_BE_ZERO[0]
            );
        }

        $this->execHandleValue(pow($this->getValue(), 1/$degree));

        return $this;
    }

    /**
     * This method sets the current value to its square root. It is a shortcut to the root(2).
     *
     * @return self
     */
    public function sqrt(): self
    {
        $this->root(2);

        return $this;
    }

    /**
     * This method sets the current value to its cube root. It is a shortcut to the root(3).
     *
     * @return self
     */
    public function cbrt(): self
    {
        $this->root(3);

        return $this;
    }
}
