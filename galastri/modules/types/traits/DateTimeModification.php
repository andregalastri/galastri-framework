<?php

namespace galastri\modules\types\traits;

/**
 * This trait has the methods related to modify the current date-time.
 */
trait DateTimeModification
{
    /**
     * This method sets the current date-time as the yesterday's date. The time is always set as
     * 00:00:00.00000. The timezone is based on the defined timezone in the route configuration.
     *
     * @return self
     */
    public function yesterday(): self
    {
        $this->execHandleValue('yesterday');
        return $this;
    }

    /**
     * This method sets the current date-time as the today's date. The time is always set as
     * 00:00:00.00000. The timezone is based on the defined timezone in the route configuration.
     *
     * @return self
     */
    public function today(): self
    {
        $this->execHandleValue('today');
        return $this;
    }

    /**
     * This method sets the current date-time as the tomorrow's date. The time is always set as
     * 00:00:00.00000. The timezone is based on the defined timezone in the route configuration.
     *
     * @return self
     */
    public function tomorrow(): self
    {
        $this->execHandleValue('tomorrow');
        return $this;
    }

    /**
     * This method sets the current date-time as the today's date and the time is always set as the
     * current one, defined by the server. The timezone is based on the defined timezone in the
     * route configuration.
     *
     * @return self
     */
    public function now(): self
    {
        $this->handling = true;

        $this->handlingValue = new DateTime();

        return $this;
    }

    /**
     * This method gets the current date-time and add or remove days, months and/or years. To add,
     * just inform the parameters as positive numbers. To remove, just use negative numbers.
     *
     * @param  mixed $days                          Number of days that will be added or removed to
     *                                              the current date-time.
     *
     * @param  mixed $months                        Number of months that will be added or removed
     *                                              to the current date-time.
     *
     * @param  mixed $years                         Number of years that will be added or removed to
     *                                              the current date-time.
     * @return self
     */
    public function modifyDate(string $days, string $months = '0', string $years = '0'): self
    {
        $this->execHandleValue($days.' days, '.$months.' months, '.$years.' years');

        return $this;
    }

    /**
     * This method gets the current date-time and add or remove days, months and/or years. To add,
     * just inform the parameters as positive numbers. To remove, just use negative numbers.
     *
     * @param  mixed $hours                         Number of hours that will be added or removed to
     *                                              the current date-time.
     *
     * @param  mixed $minutes                       Number of minutes that will be added or removed
     *                                              to the current date-time.
     *
     * @param  mixed $seconds                       Number of seconds that will be added or removed to
     *                                              the current date-time.
     * @return self
     */
    public function modifyTime(string $hours, string $minutes = '0', string $seconds = '0'): self
    {
        $this->execHandleValue($hours.' hours, '.$minutes.' minutes, '.$seconds.' seconds');

        return $this;
    }
}
