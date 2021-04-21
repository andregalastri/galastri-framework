<?php

namespace galastri\modules\types\traits;

use galastri\extensions\Exception;
use galastri\core\Debug;

trait Common
{
    private ?string $value = null;
    private ?string $initialValue = null;

    private ?array $errorMessage = null;

    private bool   $saveHistory;
    private array  $history = [];


    /**********************************************
     * Initialize and setters and getters
     **********************************************/

    /**
     * value
     *
     * @param bool $value
     * @return self|string
     */
    public function setValue($value)
    {
        Debug::setBacklog();

        $this->execSetValue($value);
        return $this;
    }

    /**
     * value
     *
     * @param bool $value
     * @return self|string
     */
    public function getValue()
    {
        return $this->value;
    }


    /**
     * initialValue
     *
     * @return null|string
     */
    public function getInitialValue()
    {
        return $this->initialValue;
    }

    /**
     * resetValue
     *
     * @return self
     */
    public function resetValue()
    {
        Debug::setBacklog();

        $this->execSetValue($this->initialValue);
        return $this;
    }


    /**********************************************
     * History and state
     **********************************************/

    /**
     * history
     *
     * @param int|null $key
     * @return array|string
     */
    public function getHistory($key = null)
    {
        Debug::setBacklog();

        if ($this->storeHistory) {
            if ($key) {
                if (isset($this->history[$key])) {
                    return $this->history[$key];
                } else {
                    throw new Exception('Key not found in stored history');
                }
            }
        }

        return $this->history;
    }

    /**
     * revertToHistory
     *
     * @param int $key
     * @return void
     */
    public function revertToHistory($key)
    {
        Debug::setBacklog();

        if ($this->storeHistory) {
            if (isset($this->history[$key])) {
                $this->execSetValue($this->history[$key]);
            } else {
                throw new Exception('Key not found in stored history');
            }
        } else {
            throw new Exception('Stored history is not active');
        }

        return $this;
    }


    
    /**********************************************
     * Internal executions
     **********************************************/

    /**
     * execSetValue
     *
     * @param null|string $value
     * @param bool $allowNull
     * @return void
     */
    private function execSetValue($value)
    {
        if (gettype($value) === 'NULL' or gettype($value) === self::VALUE_TYPE) {
            $this->validate($value);

            $this->value = $value;
            $this->execSaveHistory($value);
            return true;
        }

        throw new Exception(
            self::TYPE_DEFAULT_INVALID_MESSAGE[1],
            self::TYPE_DEFAULT_INVALID_MESSAGE[0],
            [self::VALUE_TYPE, gettype($value)]
        );
    }

    /**
     * execSaveHistory
     *
     * @param string $value
     * @return void
     */
    private function execSaveHistory($value)
    {
        if ($this->storeHistory) {
            $this->history[] = $value;
        }
    }
    
    /**
     * Undocumented function
     *
     * @return boolean
     */
    private function isEmpty()
    {
        return empty($this->value);
    }



    /**********************************************
     * Debug
     **********************************************/

    /**
     * varDump
     *
     * @return void
     */
    public function varDump()
    {
        echo '<pre>';
        var_dump($this->value);
        echo '</pre>';
    }
}
