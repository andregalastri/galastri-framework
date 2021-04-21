<?php

namespace galastri\modules\types\traits;

use galastri\extensions\Exception;
use galastri\core\Debug;

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
     * Defines if the history will be enabled (true) or not (false).
     *
     * @var bool
     */
    private bool $saveHistory = false;

    /**
     * When the $saveHistory property is enabled, stores each value changes creating a history that
     * make possible to revert to previous values.
     *
     * @var array
     */
    private array $history = [];

    /**
     * Set a value to the object. The value will be checked, to make shure it matches the expected
     * type of data or null. If not, throws an exception.
     *
     * @param mixed $value                          The value to be checked and stored
     *
     * @return self
     */
    public function setValue($value): self
    {
        Debug::setBacklog();

        $this->execSetValue($value);
        return $this;
    }

    /**
     * Returns the stored value.
     * NOTE: The type hint is mixed because this trait can be used in any type class.
     *
     * @return mixed
     */
    public function getValue()// : mixed
    {
        return $this->value;
    }


    /**
     * Returns the initial value, but not change it.
     * NOTE: The type hint is mixed because this trait can be used in any type class.
     *
     * @return mixed
     */
    public function getInitialValue()// : mixed
    {
        return $this->initialValue;
    }

    /**
     * Restarts the value to the initial value.
     *
     * @return self
     */
    public function resetValue(): self
    {
        Debug::setBacklog();

        $this->execSetValue($this->initialValue);
        return $this;
    }


    /**
     * History methods
     */

    /**
     * Returns a specific state of the history, when it is enabled. Return all the history data when
     * no key is specified.
     *
     * @param int|null $key                         The state that will be returned. When null,
     *                                              return all the history in array format.
     * 
     * @return array|mixed
     */
    public function getHistory(?int $key = null)// : mixed
    {
        Debug::setBacklog();

        if ($this->saveHistory) {
            if ($key) {
                if (isset($this->history[$key])) {
                    return $this->history[$key];
                } else {
                    throw new Exception(self::TYPE_HISTORY_KEY_NOT_FOUND[1], self::TYPE_HISTORY_KEY_NOT_FOUND[0], [$key]);
                }
            }
        }

        return $this->history;
    }

    /**
     * Reverts the value to some of the previous values stored in the history. It is required to
     * specify which key will be restored to the value.
     * 
     * This method only works if the $saveHistory property is enabled.
     *
     * @param int $key                              The state that will be restored to the value.
     * 
     * @return self
     */
    public function revertToHistory(int $key): self
    {
        Debug::setBacklog();

        if ($this->saveHistory) {
            if (isset($this->history[$key])) {
                $this->execSetValue($this->history[$key]);
            } else {
                throw new Exception(self::TYPE_HISTORY_KEY_NOT_FOUND[1], self::TYPE_HISTORY_KEY_NOT_FOUND[0], [$key]);
            }
        } else {
            throw new Exception(self::TYPE_HISTORY_DISABLED[1], self::TYPE_HISTORY_DISABLED[0]);
        }

        return $this;
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
     * Finally, it calls the save history method execution, that will store the value as a new state
     * inside the data history (only if $saveHistory property is enabled).
     *
     * @param mixed $value                          The value that will be checked and stored.
     * 
     * @return void
     */
    private function execSetValue($value): void
    {
        if (gettype($value) === 'NULL' or gettype($value) === self::VALUE_TYPE) {
            $this->validate($value);

            $this->value = $value;
            
            if (!$this->initialized and $value !== null) {
                $this->initialized = true;
                $this->initialValue = $value;
            }
            
            $this->execSaveHistory($value);
        } else {
            throw new Exception(
                self::TYPE_DEFAULT_INVALID_MESSAGE[1],
                self::TYPE_DEFAULT_INVALID_MESSAGE[0],
                [self::VALUE_TYPE, gettype($value)]
            );
        }
    }

    /**
     * This method checks if the $saveHistory property is enabled. If it is, then any value set will
     * be saved in an array, creating a history with each change of the value.
     *
     * @param string $value                         The value that will be stored.
     *
     * @return void
     */
    private function execSaveHistory($value): void
    {
        if ($this->saveHistory) {
            $this->history[] = $value;
        }
    }
    
    /**
     * This method return if the actual value is empty (true) or not (false).
     *
     * @return bool
     */
    private function isEmpty(): bool
    {
        return empty($this->value);
    }


    /**
     * Execute the varDump function in the value to get its data and properties.
     *
     * @return void
     */
    public function varDump(): void
    {
        varDump($this->value);
    }
}
