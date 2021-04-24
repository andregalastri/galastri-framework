<?php
declare(strict_types = 1);

namespace galastri\modules\types;

use galastri\core\Debug;
use galastri\extensions\typeValidation\StringValidation;
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
     * Stores a bool or null value.
     *
     * @var null|bool
     */
    private ?bool $value = null;

    /**
     * Stores the first value set to the object that isn't null.
     *
     * @var null|bool
     */
    private ?bool $initialValue = null;
    
    /**
     * Stores an instance of the EmptyValidation class, to be used in the validation methods that
     * uses empty validation. This is uses composition because it gives better control to the
     * visibility of validation methods and properties.
     *
     * @var \galastri\extensions\typeValidation\EmptyValidation
     */
    private EmptyValidation $emptyValidation;

    /**
     * Sets up the value and if it will save or not a history of the value changes. It also create
     * the object composition of the validation classes.
     *
     * @param null|string $value                    The value that will be stored. It is optional to
     *                                              set it in the construct.
     *
     * @param bool $saveHistory                     Defines if the changes of the value will be
     *                                              saved in a history, allowing to revert the value
     *                                              to previous values. Default is false.
     *
     * @return void
     */
    public function __construct(/*?bool*/ $value = null, bool $saveHistory = false)
    {
        Debug::setBacklog();

        $this->saveHistory = $saveHistory;
        $this->emptyValidation = new EmptyValidation();

        $this->execSetValue($value);
    }
    
    /**
     * Invert the current value. If it is false, it turns into true, and vice-versa.
     *
     * @return self
     */
    public function invert(): self
    {
        $this->execSetValue(!$this->value);
        return $this;
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
     * @param  string $message                      The message when an validation returns error.
     *
     * @param  string $code                         A custom code to the error.
     *
     * @return self
     */
    public function onError(string $message, string $code = 'validationFail'): self
    {
        $this->errorMessage[0] = $code;
        $this->errorMessage[1] = $message;

        $this->emptyValidation->onError($message, $code);

        return $this;
    }
    
    /**
     * This method executes the validation to the given value. The validation is always when the
     * value is changed, testing the value before store it. However, sometimes is necessary to check
     * if the data is valid without using the setValue() method. For example, after pass the value
     * in the construct of the TypeString class and only after that set up the validation
     * configuration.
     *
     * @param  mixed $value                         The value that will be tested.
     *
     * @return self
     */
    public function validate(?string $value = null): self
    {
        $this->emptyValidation->setValue($value ?? $this->value)->validate();

        return $this;
    }
}
