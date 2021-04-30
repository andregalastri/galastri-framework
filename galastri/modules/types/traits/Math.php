<?php

namespace galastri\modules\types\traits;

/**
 * This trait has the methods related to math.
 */
trait Math
{
    /**
     * Sums the current value by the given number.
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
     * Subtracts the current value by the given number.
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
     * Subtracts the given number by the current value.
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
     * Multiplies the current value by the given number.
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
     * Divides the current value by the given number.
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
     * Divides the given number by the current value.
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
     * Set the remainder of the division between the current value by the given number as the value.
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
     * Raises the current value to power of the given exponent.
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
     * Raises the given base to power with the current value as exponent.
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
     * Increments the current value by 1.
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
     * Decrement the current value by 1.
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
     * Round the current value up. It also can round up based on a given multiple.
     *
     * @param  float $nearestMultiple               The nearest multiple to round up. For example,
     *                                              if it is wanted to round in multiples of 5, 10
     *                                              or even fractions like 0.5 or 0.1.
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
     * Round the current value down. It also can round down based on a given multiple.
     *
     * @param  float $nearestMultiple               The nearest multiple to round down. For example,
     *                                              if it is wanted to round in multiples of 5, 10
     *                                              or even fractions like 0.5 or 0.1.
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
     * Round the current value up or down automatically, based on the nearest multiple or integer.
     * It also can round down based on a given multiple.
     *
     * @param  float $nearestMultiple               The nearest multiple to round up or down. For
     *                                              example, if it is wanted to round in multiples
     *                                              of 5, 10 or even fractions like 0.5 or 0.1.
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
     * Set the current value to its absolute value.
     *
     * @return self
     */
    public function abs(): self
    {
        $this->execHandleValue(abs($this->getValue()));

        return $this;
    }

    /**
     * Set the current value to its root (square root, cube root, etc).
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
     * Set the current value to its square root. It is a shortcut to the root() method with $degree
     * value equal to 2
     *
     * @return self
     */
    public function sqrt(): self
    {
        $this->root(2);

        return $this;
    }

    /**
     * Set the current value to its cube root. It is a shortcut to the root() method with $degree
     * value equal to 3
     *
     * @return self
     */
    public function cbrt(): self
    {
        $this->root(3);

        return $this;
    }
    
    /**
     * Internal method to convert the result of the calculations to the types of the current object,
     * to resolve incompatibility issues.
     *
     * @param  mixed $value
     *
     * @return void
     */
    private function convertToRightType(float $value)// : int|float
    {
        return static::VALUE_TYPE === 'integer' ? (int)$value : (float)$value;
    }
}
