<?php

namespace galastri\modules\validation\traits;

use \DateTime;
use galastri\extensions\Exception;
use galastri\modules\types\TypeDateTime;

/**
 * This trait has the methods that validates date and time.
 */
trait DateTimeValues
{
    /**
     * This method creates a link in the validating chain that checks if the date-time is bigger than
     * the date-time informed. If it is, an exception is thrown.
     *
     * @param  TypeDateTime|string $maxDateTime     The maximum allowed date-time.
     *
     * @return void
     */
    public function maxDateTime(/*TypeDateTime|string*/ $maxDateTime): self
    {
        $this->validatingChain[] = function () use ($maxDateTime) {
            if ($this->validatingValue > $this->createDateTime($maxDateTime)) {
                $this->defaultMessageSet(
                    self::VALIDATION_DATETIME_MAX[1],
                    self::VALIDATION_DATETIME_MAX[0],
                    gettype($maxDateTime) === 'string' ? $maxDateTime : $maxDateTime->get()
                );
                $this->throwFail();
            }
        };

        return $this;
    }

    /**
     * This method creates a link in the validating chain that checks if the date-time is lesser than
     * the date-time informed. If it is, an exception is thrown.
     *
     * @param  TypeDateTime|string $minDateTime     The minimum allowed date-time.
     *
     * @return void
     */
    public function minDateTime(/*TypeDateTime|string*/ $minDateTime): self
    {
        $this->validatingChain[] = function () use ($minDateTime) {
            if ($this->validatingValue < $this->createDateTime($minDateTime)) {
                $this->defaultMessageSet(
                    self::VALIDATION_DATETIME_MIN[1],
                    self::VALIDATION_DATETIME_MIN[0],
                    gettype($minDateTime) === 'string' ? $minDateTime : $minDateTime->get()
                );
                $this->throwFail();
            }
        };

        return $this;
    }

    /**
     * This method is a shortcut to set a minimum and maximum date-time.
     *
     * @param  TypeDateTime|string $minDateTime     The minimum allowed date-time.
     *
     * @param  TypeDateTime|string $maxDateTime     The maximum allowed date-time.
     *
     * @return void
     */
    public function dateTimeRange(/*TypeDateTime|string*/ $minDateTime, /*DateTime|string*/ $maxDateTime): self
    {
        $this->minDateTime($minDateTime);
        $this->maxDateTime($maxDateTime);

        return $this;
    }
}
