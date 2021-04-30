<?php

namespace galastri\modules\types\traits;

use galastri\core\Debug;
use galastri\extensions\Exception;

/**
 * This trait has the common methods that is used by various type classes. To avoid code repetition,
 * this trait can be easily implemented in the classes that need these codes.
 */
trait Common
{
    /**
     * Defines if the object was initialized or not. It is considered initialized when it stores a
     * valid value that isn't null.
     *
     * @var bool
     */
    private bool $initialized = false;

    /**
     * Stores the error message and code that will be returned when an exception occurs.
     *
     * @var array|null
     */
    private ?array $errorMessage = null;

    /**
     * Stores the debug tracking . This is used for internal framework, change this value while
     * developing your app.
     *
     * @var bool
     */
    private int $debugTrack;

    
    /**
     * Set a value to the object. The value will be checked, to make shure it matches the expected
     * type of data or null. If not, throws an exception.
     *
     * @param mixed $value                          The value to be checked and stored
     *
     * @return self
     */
    public function set($value = null): self
    {
        Debug::setBacklog();

        if ($value === null) {
            $this->execHandleValue($this->getValue());
        } else {
            if ($value instanceof \Closure) {
                $value = $value($this);
            }

            $this->execHandleValue($value);
        }

        $this->execStoreValue();

        return $this;
    }

    /**
     * Returns the stored value.
     * NOTE: The type hint is mixed because this trait can be used in any type class.
     *
     * @return mixed
     */
    public function get()// : mixed
    {
        $handlingValue = $this->handlingValue;
        $this->handlingValue = null;

        return $handlingValue ?? $this->storedValue;
    }

    public function disableDebugTrack(): self
    {
        $this->disableDebug = false;

        return $this;
    }

    public function preview()// : mixed
    {
        return $this->getValue();
    }

    public function clear()// : mixed
    {
        $this->handlingValue = null;

        return $this;
    }


    /**
     * Execute the varDump function in the value to get its data and properties.
     *
     * @return self
     */
    public function dump($exit = DONT_STOP): self
    {
        vardump($this);
        
        if ($exit === STOP) {
            exit;
        }

        return $this;
    }

    public function valdump($exit = DONT_STOP): self
    {
        vardump($this->getValue());
        
        if ($exit === STOP) {
            exit;
        }

        return $this;
    }

    /**
     * This method return if the actual object is initilialized.
     *
     * @return bool
     */
    public function isInitialized(): bool
    {
        return $this->initialized;
    }

    public function isNull(): bool
    {
        return $this->getValue() === null;
    }

    public function isNotNull(): bool
    {
        return $this->getValue() !== null;
    }

    public function isEmpty(): bool
    {
        return empty($this->getValue());
    }

    public function isNotEmpty(): bool
    {
        return !empty($this->getValue());
    }

    /**
     * Internal executions.
     */
    
    /**
     * This method stores the value into the $value property. It first checks its type; if it is
     * null or equal to the expected value type, then it is valid. If not, an exception is thrown.
     *
     * When it has a valid type, the value is validated the validation methods, if they were
     * configured and only after this the value is stored.
     *
     * This method also sets if the value is valid to define the object as initialized or not, by
     * checking if it is already initialized and if the value is not equal to null. The initial
     * value is set only if the value meets this requirements.
     *
     * @param mixed $value                          The value that will be checked and stored.
     *
     * @param bool $forceInitialize                 Force initialize even if the value to be set is
     *                                              null.
     *
     * @return void
     */
    private function execHandleValue($value): void
    {
        if (
            in_array(static::VALUE_TYPE, ['double', 'integer']) and
            in_array(gettype($value), ['double', 'integer'])
        ) {
            $this->convertToRightNumericType($value);
        }

        $this->handlingValue = $value;
    }

    private function execStoreValue(bool $forceInitialize = true): void
    {
        $value = $this->handlingValue;

        if (gettype($value) === 'NULL' or gettype($value) === static::VALUE_TYPE) {
            $this->storedValue = $this->validate();
            $this->handlingValue = null;

            if ((!$this->initialized and $this->getValue() !== null) or (!$this->initialized and $forceInitialize)) {
                $this->initialized = true;
            }
        } else {
            throw new Exception(
                $this->errorMessage[1] ?? self::TYPE_DEFAULT_INVALID_MESSAGE[1],
                $this->errorMessage[0] ?? self::TYPE_DEFAULT_INVALID_MESSAGE[0],
                [static::VALUE_TYPE, $this->execGetVarType($value)]
            );
        }
    }
    
    /**
     * execBuildErrorMessage
     *
     * @param  array|string $messageCode
     * @param  float|int|string $printfData
     * @return array
     */
    private function execBuildErrorMessage(/*array|string*/ $messageCode, /*float|int|string*/$printfData): array
    {
        $message = vsprintf(is_array($messageCode) ? $messageCode[0] : $messageCode, $printfData);
        $code = is_array($messageCode) ? $messageCode[1] : 'G0023';

        if (empty($this->errorMessage)) {
            $this->errorMessage[0] = $code;
            $this->errorMessage[1] = $message;
        }

        return [$code, $message];
    }

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

    private function execSetVarType($type): string
    {
        $type = str_replace([
            'bool', 'float', 'int', 'null'
        ],
        [
            'boolean', 'double', 'integer', 'NULL'
        ],
        $type);

        return settype($type);
    }

    private function execTypeClassName($data, string $location = ''): string
    {
        return $location . str_replace(
            ['string', 'double', 'integer', 'boolean', 'array'],
            ['TypeString', 'TypeFloat', 'TypeInt', 'TypeBool', 'TypeArray'],
            gettype($data)
        );
    }

    /**
     * Internal method that converts numeric values to the right type set in the VALUE_TYPE
     * constant. This way it is always safe that a TypeInt will return an integer and a TypeFloat
     * will return a float.
     *
     * @param  int|float &$value                    The value that will be converted.
     *
     * @return void
     */
    private function convertToRightNumericType(/*int|float*/ &...$values): void
    {
        foreach ($values as &$value) {
            if (static::VALUE_TYPE === 'integer') {
                $value = (int)explode('.', $value)[0];
            }
            
            settype($value, static::VALUE_TYPE);
        }
        unset($value);
    }

    private function getValue()
    {
        return $this->handlingValue ?? $this->storedValue;
    }
}
