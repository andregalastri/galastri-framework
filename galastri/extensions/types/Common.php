<?php

namespace galastri\extensions\types;

use galastri\extensions\Exception;

trait Common
{
    private ?string $value = null;
    private ?array $chain = null;
    private array $errorMessage = self::VALIDATION_DEFAULT_INVALID_MESSAGE;

    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    public function validate($allowNull = true)
    {
        // if (empty($this->value) and $allowNull === false or !empty($this->value)) {
            // vardump(!empty($this->chain), $this->value);
            if (!empty($this->chain)) {
                krsort($this->chain);
                foreach ($this->chain as $chain) {
                    $chain();
                }
            }
        // }
    }

    public function onError($message)
    {
        $this->chain[] = function () use ($message){
            $this->errorMessage[1] = $message;
        };
    }

    public function throwErrorMessage(...$data)
    {
        throw new Exception($this->errorMessage[1], $this->errorMessage[0], [var_export($this->value, true), implode($data)]);
    }
}
