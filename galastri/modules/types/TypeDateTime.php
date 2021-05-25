<?php
declare(strict_types = 1);

namespace galastri\modules\types;

use \DateTime;
use galastri\core\Debug;
use galastri\extensions\Exception;
use galastri\modules\validation\DateTimeValidation;

/**
 * This class creates objects that will act as a date-time types. This type class works completely
 * different from the primitive type classes because it uses the built-in PHP DateTime class. It,
 * however, uses similar methods to keep the usage in conformity with the other type classes.
 */
final class TypeDateTime extends DateTimeValidation implements \Language
{
    /**
     * For a better coding and reuse of methods, much of the methods that makes these type classes
     * useful is in trait files, that are imported here.
     */
    use traits\Common;
    use traits\DateTimeModification;
    use traits\DateTimeComparison;

    /**
     * Stores an default date-time format used internally.
     */
    const DEFAULT_DATETIME_FORMAT = 'Y-m-d H:i:s';

    /**
     * Stores the real value, after being handled.
     *
     * @var DateTime|null
     */
    protected ?DateTime $storedValue = null;

    /**
     * Stores the value while it is being handled.
     *
     * @var mixed
     */
    protected $handlingValue = null;

    /**
     * The constructor of the class stores the format of the date-time that will be used when setting
     * the value that will be validated.
     *
     * @param  string $format                       The format of the date-time.
     *
     * @return void
     */
    public function __construct($format = 'Y-m-d H:i:s')
    {
        $this->setFormat($format);
    }

    /**
     * This method stores the value in the $storedValue property. It has two behaviors:
     *
     * When the parameter $value is not defined or is null, it will store the value that is being
     * handle (if there is any). If there is no handling value, then 'null' will be stored.
     *
     * However, if the $value parameter is defined, then this value will be converted to an instance
     * of the DateTime class to be stored.
     *
     * If there are validation methods defined before this method, they will be executed to check if
     * the value matches the defined validation processes.
     *
     * The parameter $value also can be a closure function, that is executed before the storage
     * process.
     *
     * @param  mixed $value                         The value that will be stored. If undefined or
     *                                              null, the handling value will be stored. If
     *                                              there is no handling value, then the value
     *                                              stored will be 'null'.
     *
     * @return self
     */
    public function set($value = null): self
    {
        /**
         * Initialize the date-time properties if they are not.
         */
        $this->initializeDateTime();

        /**
         * Force the initialization, even if the value is null.
         */
        $this->forceInitialize = true;

        /**
         * When the value is null, checks if there is a value being handle. If it is, that is the
         * value that will be stored.
         */
        if ($value === null and $this->handling) {
            $this->execHandleValue($this->getValue()->format(static::DEFAULT_DATETIME_FORMAT));

        /**
         * If not, the value declared will be stored. If the value is null, it will stored as null.
         */
        } else {
            /**
             * The $value parameter can be a closure function that do any kind of process to return
             * a final value.
             */
            if ($value instanceof \Closure) {
                $value = $value($this);
            }

            /**
             * Converts the value into an DateTime instance or just clones it if the $value is
             * already an instance of the TypeDateTime class.
             */
            if ($value instanceof TypeDateTime) {
                $value = unserialize(serialize($value->getValue()));
            } else {
                $value = $this->createDateTime($value);
            }

            $this->execHandleValue($value->format(static::DEFAULT_DATETIME_FORMAT));
        }

        $this->execStoreValue();

        return $this;
    }

    /**
     * This method return the handling date-time, if there is any, and clears it, resetting the
     * $handling property. If there is no handling value, then it gets the stored date-time. It can
     * also return the date-time in different format when the $format parameter is set.
     *
     * @param  null|string $format                  A custom format for the date-time that will be
     *                                              returned.
     *
     * @return string
     */
    public function get(?string $format = null): string
    {
        $format = $format ?? $this->format;

        if ($this->handling) {
            $this->handling = false;

            $result = $this->handlingValue->format($format);
        } else {
            $result = $this->storedValue->format($format);
        }

        return str_replace('!', '', $result);
    }

    /**
     * This method creates a DateTime object to be stores in the $storedValue property if its value
     * is not defined yet. It also clones it to be stored in the $handlingValue property if its
     * value is not defined.
     *
     * This is needed because this class uses the built-in PHP DateTime class to manage its methods.
     *
     * @return void
     */
    private function initializeDateTime(): void
    {
        $this->storedValue = $this->storedValue ?? new DateTime();
        $this->handlingValue = $this->handlingValue ?? unserialize(serialize($this->storedValue));
    }

    /**
     * This method overrides the execHandleValue method from the Common trait. It is needed because
     * the method works differently here in this class. Instead of just store the value, the
     * $handlingValue property needs to be modified, because it will always (or most of the time)
     * store an instance of the DateTime class.
     *
     * @param  mixed $value                         The value that willmodify the handling value
     *                                              DateTime instance.
     *
     * @return void
     */
    private function execHandleValue($value): void
    {
        $this->initializeDateTime();

        $this->handling = true;
        $this->handlingValue->modify($value);
    }

    /**
     * This method overrides the execStoreValue method from the Common trait. In is needed because
     * the method works differently here in this class. Instead of checking the value type, it will
     * just execute the validation methods and, if it is all right, it will be stored in the
     * $storedValue property.
     *
     * The use of the serialize and unserialize functions when setting the $storedValue is because
     * it creates an independent clone of the $handlingValue instance.
     *
     * Once the value is stored, the handling is set as false and the instance is initialized, if it
     * isn't yet.
     *
     * @return void
     */
    private function execStoreValue(): void
    {
        /**
         * Execute the validation process, if there is any validation method defined before the
         * execution of this method.
         */
        $this->validate();

        /**
         * The current handling value is stored in an variable. This value has its type validated.
         * If it is equal to null or the expected value type, then it is stored.
         */
        $value = $this->getValue();

        /**
         * Clones the $handlingValue property to be stored in the $storedValue property.
         */
        $this->storedValue = unserialize(serialize($this->handlingValue));

        $this->handling = false;

        if ((!$this->initialized and $value !== null) or (!$this->initialized and $this->forceInitialize)) {
            $this->initialized = true;
        }
    }
}
