<?php

namespace galastri\modules\types\traits;

/**
 * This trait has the methods related to compare the current date-time with the informed date-times.
 */
trait DateTimeComparison
{
    /**
     * This method checks if the current date-time is greater than the given value.
     *
     * @param  DateTime|string|TypeDateTime $value  The date-time that will be compared
     *
     * @return bool
     */
    public function isGreater(/*DateTime|string|TypeDateTime*/ $value): bool
    {
        $this->initializeDateTime();

        return $this->getValue() > $this->createDateTime($value);
    }

    /**
     * This method checks if the current date-time is lesser than the given value.
     *
     * @param  DateTime|string|TypeDateTime $value  The date-time that will be compared
     *
     * @return bool
     */
    public function isLesser(/*DateTime|string|TypeDateTime*/ $value): bool
    {
        $this->initializeDateTime();

        return $this->getValue() < $this->createDateTime($value);
    }

    /**
     * This method checks if the current date-time is greater or equal than the given value.
     *
     * @param  DateTime|string|TypeDateTime $value  The date-time that will be compared
     *
     * @return bool
     */
    public function isGreaterEqual(/*DateTime|string|TypeDateTime*/ $value): bool
    {
        $this->initializeDateTime();

        return $this->getValue() >= $this->createDateTime($value);
    }

    /**
     * This method checks if the current date-time is lesser or equal than the given value.
     *
     * @param  DateTime|string|TypeDateTime $value  The date-time that will be compared
     *
     * @return bool
     */
    public function isLesserEqual(/*DateTime|string|TypeDateTime*/ $value): bool
    {
        $this->initializeDateTime();

        return $this->getValue() <= $this->createDateTime($value);
    }

    /**
     * This method checks if the current date-time is equal than the given value.
     *
     * @param  DateTime|string|TypeDateTime $value  The date-time that will be compared
     *
     * @return bool
     */
    public function isEqual(/*DateTime|string|TypeDateTime*/ $value): bool
    {
        $this->initializeDateTime();

        return $this->getValue() == $this->createDateTime($value);
    }

    /**
     * This method compares the current date-time with the given value and returns if it is greater,
     * lesser or equal.
     *
     * The possible returning values are:
     * - When greater: returns '>'
     * - When lesser: returns '<'
     * - When equal: returns '=='
     *
     * @param  DateTime|string|TypeDateTime $value  The date-time that will be compared
     *
     * @return string
     */
    public function compare(/*DateTime|string|TypeDateTime*/ $value): string
    {
        if ($this->isGreater($value)) {
            return '>';
        } else if ($this->isLesser($value)) {
            return '<';
        } else {
            return '==';
        }
    }

    /**
     * This method returns a formatted DateTime diff method that can check the difference between
     * two date-times.
     *
     * @param  DateTime|string|TypeDateTime $value  The date-time that will be compared
     *
     * @param  string $returningFormat              The format to be returned. It uses the
     *                                              DateInterval class format method, not the
     *                                              DateTime class format method.
     *
     * @return string
     */
    public function diff(/*DateTime|string|TypeDateTime*/ $value, string $returningFormat): string
    {
        $this->initializeDateTime();

        return $this->getValue()->diff($this->createDateTime($value))->format($returningFormat);
    }
}
