<?php

namespace galastri\extensions\typeValidation;

use galastri\extensions\Exception;

/**
 * This trait has the common methods that is used by various validation classes. To avoid code
 * repetition, this trait can be easily implemented in the classes that need these codes.
 */
trait Common
{
    /**
     * Stores the value that will be tested.
     *
     * @var mixed
     */
    private $value = null;

    /**
     * Stores closure functions that will create a chain of validation executions. This chain will
     * be inverted, to be executed from back to front.
     *
     * @var array|null
     */
    private ?array $chain = null;

    /**
     * Stores an error message that will be shown if the value is considered invalid. It is an array
     * because the first key stores the error code and the second the message itself.
     *
     * @var array
     */
    private array $errorMessage = self::VALIDATION_DEFAULT_INVALID_MESSAGE;

    
    /**
     * This method stores the given value in the $value property.
     *
     * IMPORTANT NOTES HERE:
     * - This setter doesn't check the data type, it only stores the value to be validated by a
     *   validation class. The class that check the data type are the type classes, not the
     *   validation classes.
     * - The data, also, won't be validated here, the validation classes has the methods responsible
     *   to validate the data.
     *
     * @param  mixed $value                         The value to be stored.
     *
     * @return self
     */
    public function setValue($value): self
    {
        $this->value = $value;
        return $this;
    }
    
    /**
     * This method starts the chain execution. It first checks if the chain isn't empty. If there
     * are functions stored in the array, then it is inverted, because the execution needs to start
     * from the last added function to the first.
     *
     * Finally, all functions are executed one by one in a foreach loop.
     *
     * @return void
     */
    public function validate(): void
    {
        if (!empty($this->chain)) {
            $chain = $this->chain;
            krsort($chain);

            foreach ($this->chain as $function) {
                $function();
            }
        }
    }
    
    /**
     * Adds a chain link that sets the error message that will be returned if the next validation
     * execution returns that the data is invalid. It can also add a optional custom error code.
     *
     * @param  string $message                      The text that will be returned when the data is
     *                                              invalid.
     *
     * @param  null|string $code                    Optional. An custom code that defines the error.
     *
     * @return void
     */
    public function onError(string $message, ?string $code = null): void
    {
        $this->chain[] = function () use ($message, $code) {
            $this->errorMessage[1] = $message;

            if ($code !== null) {
                $this->errorMessage[0] = $code;
            }
        };
    }
    
    /**
     * This method throw an exception with the error message and code.
     *
     * @param  mixed ...$data                       Additional data that can be used as arguments
     *                                              when the message has printf flags.
     *
     * @return void
     */
    public function throwErrorMessage(...$data): void
    {
        throw new Exception($this->errorMessage[1], $this->errorMessage[0], [var_export($this->value, true), implode($data)]);
    }
}
