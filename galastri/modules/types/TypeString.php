<?php

namespace galastri\modules\types;

use galastri\core\Debug;
use galastri\extensions\typeValidation\StringValidation;
use galastri\extensions\typeValidation\EmptyValidation;
use galastri\modules\types\traits\Common;
use galastri\modules\types\traits\Concat;
use galastri\modules\types\traits\ConvertCase;
use galastri\modules\types\traits\Length;
use galastri\modules\types\traits\Substring;
use galastri\modules\types\traits\Trim;
use galastri\modules\types\traits\Split;
use galastri\modules\types\traits\Replace;
use galastri\modules\types\traits\RandomStringValue;

/**
 * TypeString
 */
final class TypeString implements \Language
{
    /**
     * Importing traits to the class.
     */
    use Common;
    use Concat;
    use ConvertCase;
    use Length;
    use Substring;
    use Trim;
    use Split;
    use Replace;
    use RandomStringValue;

    /**
     * This constant define the type of the data the $value property (defined in \galastri\modules\
     * types\traits\Common) will store. It needs to match the possible returning value given by the
     * gettype() function.
     */
    const VALUE_TYPE = 'string';

    /**
     * Stores the string or null.
     *
     * @var null|string
     */
    private ?string $value = null;

    /**
     * Stores the first value set to the object that isn't null.
     *
     * @var null|string
     */
    private ?string $initialValue = null;
    
    /**
     * Stores an instance of the StringValidation class, to be used in the validation methods that
     * uses string validation. This is uses composition because it gives better control to the
     * visibility of validation methods and properties.
     *
     * @var \galastri\extensions\typeValidation\StringValidation
     */
    private StringValidation $stringValidation;

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
    public function __construct($value = null, $saveHistory = false)
    {
        Debug::setBacklog();

        $this->saveHistory = $saveHistory;
        $this->stringValidation = new StringValidation();
        $this->emptyValidation = new EmptyValidation();

        $this->execSetValue($value);
    }

    /**
     * Validation methods via composition. The names of the methods follow the validation classes
     * methods.
     */

    /**
     * Allow only lower case strings. Calls the lowerCase() method from the string validation class.
     * More information in \galastri\extensions\typeValidation\StringValidation class file.
     *
     * @return self
     */
    public function lowerCase(): self
    {
        $this->stringValidation
            ->lowerCase();
        return $this;
    }
    
    /**
     * Allow only upper case strings. Calls the upperCase() method from the string validation class.
     * More information in \galastri\extensions\typeValidation\StringValidation class file.
     *
     * @return self
     */
    public function upperCase(): self
    {
        $this->stringValidation
            ->upperCase();

        return $this;
    }
    
    /**
     * Set required charsets that is needed to be present in the string and defines the minimum
     * quantity of one of the chars is needed. This method can be called multiple times setting up
     * multiple charsets and/or minimum quantities of them in the string.
     *
     * The method calls the requiredChars() method from the string validation class. More
     * information in \galastri\extensions\typeValidation\StringValidation class file.
     *
     * @param  int $minQty                          Number that defines how many chars of each
     *                                              charset needs to be in the string.
     *
     * @param  string ...$charSet                   Groups of chars that the string need to have.
     *                                              Important: the $minQty parameter sets the
     *                                              minimum quantity of each group. This means that
     *                                              if there is two charsets defined, with 1 minimum
     *                                              quantity, then this means that it is required
     *                                              that the string have one char of the first group
     *                                              and one of the second.
     *
     * @return self
     */
    public function requiredChars(int $minQty, string ...$charSets): self
    {
        $this->stringValidation
            ->requiredChars($minQty, $charSets);

        return $this;
    }
    
    /**
     * Set the allowed chars in the string. When set, blocks every char that isn't defined in the
     * $charset parameter.
     *
     * The method calls the allowCharset() method from the string validation class. More information
     * in \galastri\extensions\typeValidation\StringValidation class file.

     * @param  string ...$charSet                   Charsets that are allowed to be in the string.
     *
     * @return self
     */
    public function allowCharset(string ...$charSet): self
    {
        $this->stringValidation
            ->allowCharset($charSet);

        return $this;
    }
    
    /**
     * Sets the minimum length to the string.
     *
     * The method calls the minLength() method from the string validation class. More information in
     * \galastri\extensions\typeValidation\StringValidation class file.
     *
     * @param  int $length                          The minimum length required.
     *
     * @return self
     */
    public function minLength(int $length): self
    {
        $this->stringValidation
            ->minLength($length);

        return $this;
    }
    
    /**
     * Sets the maximum length to the string.
     * 
     * The method calls the maxLength() method from the string validation class. More information in
     * \galastri\extensions\typeValidation\StringValidation class file.
     * 
     * @param  int $length                          The minimum length required.
     * 
     * @return self
     */
    public function maxLength(int $length): self
    {
        $this->stringValidation
            ->maxLength($length);

        return $this;
    }
    
    /**
     * Defines that the value of the string cannot be null.
     * 
     * The method calls the denyNull() method from the string validation class. More information in
     * \galastri\extensions\typeValidation\StringValidation class file.
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
     * \galastri\extensions\typeValidation\StringValidation class file.
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

        $this->stringValidation->onError($message, $code);
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
        $this->stringValidation->setValue($value ?? $this->value)->validate();
        $this->emptyValidation->setValue($value ?? $this->value)->validate();

        return $this;
    }
}
