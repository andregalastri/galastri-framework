<?php
declare(strict_types = 1);

namespace galastri\modules\types;

use galastri\core\Debug;
use galastri\extensions\Exception;
use galastri\extensions\typeValidation\EmptyValidation;

/**
 * This class creates objects that will act as a string type.
 */
final class TypeBool implements \Language
{
    /**
     * Importing traits to the class.
     */
    use traits\Common;

    /**
     * This constant define the type of the data the $value property (defined in \galastri\modules\
     * types\traits\Common) will store. It needs to match the possible returning value given by the
     * gettype() function.
     */
    const VALUE_TYPE = 'boolean';

    /**
     * Stores the value after handling.
     *
     * @var null|bool
     */
    private ?bool $storedValue = null;

    /**
     * Stores the temporary value while handling.
     *
     * @var mixed
     */
    protected $handlingValue = null;

    /**
     * Stores an instance of the EmptyValidation class, to be used in the validation methods that
     * uses empty validation. This is uses composition because it gives better control to the
     * visibility of validation methods and properties.
     *
     * @var \galastri\extensions\typeValidation\EmptyValidation
     */
    private EmptyValidation $emptyValidation;

    /**
     * Sets up the type It also create the object composition of the validation classes.
     *
     * @param null|bool $value                      The value that will be stored. It is optional to
     *                                              set it in the construct.
     *
     * @return void
     */
    public function __construct(/*?bool*/ $value = null,)
    {
        Debug::setBacklog();

        $this->emptyValidation = new EmptyValidation();

        $this->execHandleValue($value);
        $this->execStoreValue(false);
    }

    /**
     * Invert the current value. If it is false, it turns into true, and vice-versa.
     *
     * @return self
     */
    public function invert(): self
    {
        if ($this->getValue() !== null) {
            $this->execHandleValue(!$this->getValue());
        }

        return $this;
    }

    public function true(): self
    {
        $this->execHandleValue(true);
        return $this;
    }

    public function false(): self
    {
        $this->execHandleValue(false);
        return $this;
    }

    public function null(): self
    {
        $this->execHandleValue(null);
        return $this;
    }

    public function isTrue(): ?bool
    {
        return $this->getValue();
    }

    public function isFalse(): ?bool
    {
        return !$this->getValue();
    }

    /**
     * Validation methods via composition. The names of the methods follow the validation classes
     * methods.
     */

    /**
     * Defines that the value of the string cannot be null.
     *
     * The method calls the denyNull() method from the string validation class. More information in
     * \galastri\extensions\typeValidation\EmptyValidation class file.
     *
     * @return self
     */
    public function denyNull(): self
    {
        $this->emptyValidation
            ->denyNull();

        return $this;
    }

    /**
     * Defines that the value of the string cannot be empty.
     *
     * The method calls the denyEmpty() method from the string validation class. More information in
     * \galastri\extensions\typeValidation\EmptyValidation class file.
     *
     * @return self
     */
    public function denyEmpty(): self
    {
        $this->emptyValidation
            ->denyEmpty();

        return $this;
    }

    /**
     * Sets an returning error message when the validation fails. This method needs to be placed in
     * front of the validation method. If the validation fails, then the returning message set will
     * be the one set in this method here.
     *
     * Optionally, an error code can be set.
     *
     * @param  array|string $messageCode            The message when an validation returns error.
     *                                              The message can have printf flags to be replaced
     *                                              by the $printfData parameter. When it is a
     *                                              array, then the first key is the message and the
     *                                              second is a custom code that will replace the
     *                                              custom G0023 code that refers to invalid data.
     *
     * @param  float|int|string ...$printfData      When there are printf flags in the message, they
     *                                              will be replaced by the values set in this
     *                                              ellipsis array.
     *
     * @return self
     */
    public function onError(/*array|string*/ $messageCode, /*float|int|string*/ ...$printfData): self
    {
        $message = $this->execBuildErrorMessage($messageCode, $printfData);

        $this->emptyValidation->onError($message[1], $message[0]);

        return $this;
    }

    /**
     * This method executes the validation to the given value. The validation is always when the
     * value is changed, testing the value before store it. However, sometimes is necessary to check
     * if the data is valid without using the setValue() method. For example, after pass the value
     * in the construct of the TypeString class and only after that set up the validation
     * configuration.
     *
     * @return bool
     */
    public function validate(): ?bool
    {
        $this->emptyValidation->setValue($this->getValue())->validate();

        return $this->getValue();
    }
}
