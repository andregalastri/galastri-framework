<?php

namespace galastri\modules\validation\traits;

use galastri\core\Debug;
use galastri\extensions\Exception;

/**
 * This trait has common methods that are shared within the validation classes.
 */
trait Common
{
    /**
     * Stores the value that will be validated.
     *
     * @var mixed
     */
    private /*mixed*/ $validatingValue;

    /**
     * Stores an array of closures that will be executed right after the method validate is called.
     */
    private ?array $validatingChain = null;

    /**
     * Stores an array with values of fail messages, with the following format:
     *
     * - Value of key 0: Stores the fail message itself.
     * - Value of key 1: Stores the fail code.
     */
    private ?array $failMessage = null;

    /**
     * This method sets the value that will be tested. Its purpose is to allow the standalone usage
     * and isn't meant to be used with type classes because it can lead to wrong results.
     *
     * @param  mixed $value                         The value that will be validated.
     *
     * @return self
     */
    public function value(/*mixed*/ $value): self
    {
        $this->validatingValue = $value;
        return $this;
    }

    /**
     * This method executes the closures stored in the $validatingChain property. The execution
     * occurs in reversed order, from the last added to the first.
     *
     * @return void
     */
    public function validate(): void
    {
        $this->validatingValue = method_exists($this, 'getValue') ? $this->getValue() : $this->validatingValue;

        /**
         * Executes each of the validation closures in reversed order only if the $validatingChain
         * property isn't empty.
         */
        if (!empty($this->validatingChain)) {
            foreach (array_reverse($this->validatingChain) as $function) {
                $function();
            }
        }
    }

    /**
     * This method creates a link in the validating chain that stores a closure that adds a fail
     * code and message for when a validation closure fails.
     *
     * @param  array|string $messageCode            Can have two behaviors.
     *
     *                                              - When string: stores only the message that will
     *                                              be returned if the validation fails.
     *                                              - When array: stores the message in the key 0
     *                                              and a fail code in the key 1.
     *
     * @param  float|int|string $printfData         The fail message can have printf flags (like %s)
     *                                              that can be replaced to the values informed
     *                                              here.
     *
     * @return self
     */
    public function onFail(/*array|string*/ $messageCode, /*float|int|string*/ ...$printfData): self
    {
        Debug::bypassGenericMessage();

        /**
         * Determines the message. If the $messageCode is an array, then the key 0 is get to be
         * filtered with vsprintf function. If not, the message will be the $messageCode parameter
         * itself .
         */
        $message = vsprintf(is_array($messageCode) ? $messageCode[0] : $messageCode, $printfData);

        /**
         * Defines a code for the fail. If the $messageCode is an array, its key 1 is stored, else,
         * the framework's default validation error code is set.
         */
        $code = is_array($messageCode) ? $messageCode[1] : DEFAULT_VALIDATION_ERROR_CODE;

        $this->validatingChain[] = function () use ($message, $code) {
            $this->failMessage[1] = $message;

            if ($code !== null) {
                $this->failMessage[0] = $code;
            }
        };

        return $this;
    }

    /**
     * This method creates a shortcut to throw exceptions easily, by throwing them using a default
     * behavior.
     *
     * @return void
     */
    protected function throwFail(): void
    {
        throw new Exception($this->failMessage[1], $this->failMessage[0], [var_export($this->validatingValue, true)]);
    }

    /**
     * This method returns the failMessage property. It is used by type classes that inhehits the
     * validation classes, allowing them to get the fail message to define if the message to be
     * shown will be the default one or the defined one.
     *
     * @return array
     */
    protected function getFailMessage(): ?array
    {
        return $this->failMessage;
    }

    /**
     * This method defines a default message. It is just used internally, to make the code be easier
     * to understand.
     *
     * @param  mixed $message                       The default message.
     *
     * @param  mixed $code                          The default code.
     *
     * @param  mixed $printfData                    The default message can have printf flags (like
     *                                              %s) that can be replaced to the values informed
     *                                              here.
     *
     * @return void
     */
    protected function defaultMessageSet(string $message, ?string $code, /*mixed*/ ...$printfData): void
    {
        Debug::bypassGenericMessage();

        if ($this->failMessage === null) {
            $this->failMessage[1] = vsprintf($message, $printfData);
            $this->failMessage[0] = $code;
        }
    }
}
