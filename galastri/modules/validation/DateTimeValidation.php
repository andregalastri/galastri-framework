<?php

namespace galastri\modules\validation;

use \DateTime;
use galastri\extensions\Exception;
use galastri\modules\types\TypeDateTime;

/**
 * This class put together traits that are related to validation of date-time.
 *
 * The class can be used standalone, but its methods are also available with instances of the
 * TypeString class. It doesn't check the type of the value to be validated in the standalone usage.
 *
 * Standalone usage example:
 *
 *      $validation = new DateTimeValidation('Y-m-d')
 *      $validation->value('2021-04-23')->minDateTime('2021-04-22')->onError('Invalid date')->validate();
 */
class DateTimeValidation implements \Language
{
    use traits\Common {
        /**
         * The value method from the Common trait needs a change before the execution. That is why
         * it is set as private _value, because it can be called after being override by the current
         * value method defined here.
         */
        value as private _value;
    }
    use traits\EmptyValues;
    use traits\DateTimeValues;

    /**
     * Stores the format of the date-time, which will be used when create an object of a DateTime
     * class.
     *
     * @var string
     */
    protected string $format;

    /**
     * The constructor of the class stores the format of the date-time that will be used when setting
     * the value that will be validated.
     *
     * @param  string $format                       The format of the date-time.
     *
     * @return void
     */
    public function __construct(string $format = 'Y-m-d H:i:s')
    {
        $this->setFormat($format);
    }

    /**
     * This method sets the value that will be tested. Its is an override of the value method of the
     * Common trait, which has the purpose to allow the standalone usage. It converts the informed
     * value to a DateTime instance, if it isn't already an instance of this class.
     *
     * @param  mixed $value                         The value that will be validated.
     *
     * @return self
     */
    public function value(/*mixed*/ $value): self
    {
        $value = $this->createDateTime($value);

        return $this->_value($value);
    }

    /**
     * This method sets the format of the date-time.
     *
     * @param  string $format                       The format of the date-time.
     *
     * @return void
     */
    public function setFormat(string $format): void
    {
        $this->format = $format;
    }


    /**
     * This method checks if the given $value parameter is an instance of the TypeDateTime class. If
     * it is, then it gets its value (which is an DateTime instance). If not, it checks if it isn't
     * an instance of the DateTime class. If it isn't, then it creates an instance based on the
     * given format.
     *
     * @param  DateTime|string|TypeDateTime $value  The date-time that will be compared
     *
     * @return DateTime
     */
    protected function createDateTime(/*DateTime|string|TypeDateTime*/ $value): DateTime
    {
        if ($value instanceof TypeDateTime) {
            $dateTime = $value->getValue();
        } else if ($value instanceof DateTime) {
            $dateTime = $value;
        } else {
            $dateTime = DateTime::createFromFormat($this->format, $value);
        }


        if (in_array($dateTime, [false, null])) {
            $this->defaultMessageSet(
                self::VALIDATION_INVALID_DATETIME[1],
                self::VALIDATION_INVALID_DATETIME[0],
                var_export(gettype($value) === 'string' ? $value : $value->format($this->format), true),
                $this->format
            );

            $this->throwFail();
        }

        return $dateTime;
    }
}
