<?php

namespace galastri\modules\types\traits;

use galastri\core\Debug;
use galastri\extensions\Exception;

/**
 * This trait has common methods that are shared within the type classes.
 */
trait Common
{
    /**
     * Defines if the instance is initialized.
     *
     * @var bool
     */
    private bool $initialized = false;

    /**
     * Defines if the value is being handled.
     *
     * @var bool
     */
    private bool $handling = false;

    /**
     * Defines if the instance will be set as initialized even if its initial value is null. It is
     * used when using the set method from the type classes.
     *
     * @var bool
     */
    private bool $forceInitialize = false;

    /**
     * This method stores the value in the $storedValue property. It has two behaviors:
     *
     * When the parameter $value is not defined or is null, it will store the value that is being
     * handle (if there is any). If there is no handling value, then 'null' will be stored.
     *
     * However, if the $value parameter is defined, then this is the value that will be stored.
     *
     * The value will have its type checked and if the value doesn't match the given type, an
     * exception will be thrown. If there are validation methods defined before this method, they
     * will be executed to check if the value matches the defined validation processes.
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
    private function _set($value = null): self
    {
        /**
         * When the value is null, checks if there is a value being handle. If it is, that is the
         * value that will be stored.
         */
        if ($value === null and $this->handling) {
            $this->execHandleValue($this->getValue());

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

            $this->execHandleValue($value);
        }

        $this->execStoreValue();

        return $this;
    }

    /**
     * This method gets the handling value, if there is any, and clears it, resetting the $handling
     * and the $handlingValue properties. If there is no handling value, then it gets the stored
     * value.
     *
     * @return mixed
     */
    public function get()// : mixed
    {
        $handling = $this->handling;
        $handlingValue = $this->handlingValue;

        $this->handling = false;
        $this->handlingValue = null;

        return $handling ? $handlingValue : $this->storedValue;
    }

    /**
     * This method gets the handling value, if there is any, but do not clears it. If there is no
     * handling value, then it gets the stored value.
     *
     * @return mixed
     */
    public function preview()// : mixed
    {
        return $this->getValue();
    }

    /**
     * This method clears the handling value.
     *
     * @return self
     */
    public function clear(): self
    {
        $this->handling = false;
        $this->handlingValue = null;

        return $this;
    }

    /**
     * This method executes the vardump function in the current instance.
     *
     * @param  int $exit                            Stops the execution when declared with STOP
     *                                              constant.
     *
     * @return self
     */
    public function dump(int $exit = DONT_STOP): self
    {
        vardump($this);

        if ($exit === STOP) {
            exit;
        }

        return $this;
    }

    /**
     * This method executes the vardump function in the current instance value.
     *
     * @param  int $exit                            Stops the execution when declared with STOP
     *                                              constant.
     *
     * @return self
     */
    public function valdump(int $exit = DONT_STOP): self
    {
        vardump($this->getValue());

        if ($exit === STOP) {
            exit;
        }

        return $this;
    }

    /**
     * This method checks if the instance is initialized.
     *
     * @return bool
     */
    public function isInitialized(): bool
    {
        return $this->initialized;
    }

    /**
     * This method checks if the value is null.
     *
     * @return bool
     */
    public function isNull(): bool
    {
        return $this->getValue() === null;
    }

    /**
     * This method checks if the value is not null.
     *
     * @return bool
     */
    public function isNotNull(): bool
    {
        return $this->getValue() !== null;
    }

    /**
     * This method checks if the value is empty.
     *
     * PHP empty() function considers as empty the following values:
     *
     *   - An empty string             : ""
     *   - 0 as a string               : "0"
     *   - 0 as an integer             : 0
     *   - 0 as a float                : 0.0
     *   - An empty array              : array(), []
     *   - Unintialized class property : public|private $var;
     *   - NULL
     *   - FALSE
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->getValue());
    }

    /**
     * This method checks if the value is not empty.
     *
     * @return bool
     */
    public function isNotEmpty(): bool
    {
        return !empty($this->getValue());
    }

    /**
     * This method stores the value that is being handled by modifier methods in the $handlingValue.
     *
     * @param  mixed $value                         The value that will be stored as handling value.
     *
     * @return void
     */
    private function execHandleValue($value): void
    {
        // if (
        //     in_array(static::VALUE_TYPE, ['double', 'integer']) and
        //     in_array(gettype($value), ['double', 'integer'])
        // ) {
        //     $value = $this->convertToRightNumericType($value);
        // }

        $this->handling = true;
        $this->handlingValue = $value;
    }

    /**
     * This method do the execution of the storing process into the $storedValue property. The value
     * is validated and then checked if its value matches the expected value. An exception is thrown
     * if it fails.
     *
     * Once passed in the validation process, the value is stored, the handling value is cleared
     * and the instance is initialized, if it isn't yet.
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

        if (in_array(gettype($value), ['NULL', static::VALUE_TYPE])) {
            $this->storedValue = $value;

            $this->handling = false;
            $this->handlingValue = null;

            if ((!$this->initialized and $value !== null) or (!$this->initialized and $this->forceInitialize)) {
                $this->initialized = true;
            }

        /**
         * If the value type doesn't match, then an exception is thrown. The exception can show an
         * default message and code or the ones defined by the user, if there is a onError method
         * defining them.
         */
        } else {
            $errorMessage = $this->getFailMessage();

            throw new Exception(
                $errorMessage[1] ?? self::TYPE_DEFAULT_INVALID_MESSAGE[1],
                $errorMessage[0] ?? self::TYPE_DEFAULT_INVALID_MESSAGE[0],
                [static::VALUE_TYPE, $this->execGetVarType($value)]
            );
        }
    }

    /**
     * This method returns a more concise type names. The gettype function return names of the types
     * differently from the naming convention of the framework. This method replaces the different
     * names replaces it to better ones.
     *
     * @param  mixed $variable                      Variable that will have its value type returned.
     *
     * @return string
     */
    private function execGetVarType($variable): string
    {
        return str_replace([
            'boolean', 'double', 'integer', 'NULL'
        ],
        [
            'bool', 'float', 'int', 'null'
        ],
        gettype($variable));
    }

    /**
     * This method converts the numeric value types to the right ones. The TypeInt and TypeFloat
     * classes share the same parent class TypeNumeric, but the deal with different types. This
     * method helps to convert the value to the type of the final numeric class.
     *
     * @param  int|float $value                     The value that will be converted.
     *
     * @return int|float
     */
    private function convertToRightNumericType(/*int|float*/ $value)// : int|float
    {
        switch (gettype($value)) {
            case 'integer':
            case 'double':
                settype($value, static::VALUE_TYPE);
                break;
        }

        return $value;
    }

    /**
     * This method return the handling value if the $handling property is true, or the stored value
     * if the $handling property is false.
     *
     * @return void
     */
    protected function getValue()
    {
        return $this->handling ? $this->handlingValue : $this->storedValue;
    }
}
