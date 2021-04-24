<?php
declare(strict_types = 1);

namespace galastri\modules\types;

use galastri\core\Debug;
use galastri\extensions\typeValidation\StringValidation;
use galastri\extensions\typeValidation\EmptyValidation;

/**
 * This class creates objects that will act as a string type.
 */
final class TypeString implements \Language
{
    /**
     * Importing traits to the class.
     */
    use traits\Common;
    use traits\Concat;
    use traits\ConvertCase;
    use traits\Length;
    use traits\Mask;
    use traits\Substring;
    use traits\Trim;
    use traits\Split;
    use traits\Replace;

    /**
     * This constant define the type of the data the $value property (defined in galastri\modules\
     * types\traits\Common) will store. It needs to match the possible returning value given by the
     * gettype() function.
     */
    const VALUE_TYPE = 'string';

    /**
     * Stores a string or null value.
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
     * @var galastri\extensions\typeValidation\StringValidation
     */
    private StringValidation $stringValidation;

    /**
     * Stores an instance of the EmptyValidation class, to be used in the validation methods that
     * uses empty validation. This is uses composition because it gives better control to the
     * visibility of validation methods and properties.
     *
     * @var galastri\extensions\typeValidation\EmptyValidation
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
    public function __construct(/*?string*/ $value = null, bool $saveHistory = false)
    {
        Debug::setBacklog();

        $this->saveHistory = $saveHistory;
        $this->stringValidation = new StringValidation();
        $this->emptyValidation = new EmptyValidation();

        $this->execSetValue($value);
    }

    /**
     * Generate a random string with hexadecimal chars, which the length can bet set with the length
     * parameter and store as the value.
     * 
     * @param  int $length                          Length of the random generated string.
     * 
     * @return self
     */
    public function setRandomValue(int $length = 15): self
    {
        if (function_exists("random_bytes")) {
            $bytes = random_bytes(ceil($length / 2));
        } elseif (function_exists("openssl_random_pseudo_bytes")) {
            $bytes = openssl_random_pseudo_bytes(ceil($length / 2));
        } else {
            throw new Exception(self::SECURE_RANDOM_GENERATOR_NOT_FOUND[0], self::SECURE_RANDOM_GENERATOR_NOT_FOUND[1]);
        }

        $this->execSetValue(bin2hex($bytes));
        return $this;
    }

    /**
     * Validation methods via composition. The names of the methods follow the validation classes
     * methods.
     */

    /**
     * Allow only lower case strings. Calls the lowerCase() method from the string validation class.
     * More information in galastri\extensions\typeValidation\StringValidation class file.
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
     * More information in galastri\extensions\typeValidation\StringValidation class file.
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
     * information in galastri\extensions\typeValidation\StringValidation class file.
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
     * in galastri\extensions\typeValidation\StringValidation class file.

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
     * galastri\extensions\typeValidation\StringValidation class file.
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
     * galastri\extensions\typeValidation\StringValidation class file.
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
     * Sets a restrict list for the allowed values.
     *
     * The method calls the allowedValueList() method from the string validation class. This class
     * imports the trait galastri\extensions\typeValidation\traits\AllowedValueList. More
     * information in the file of the trait.
     *
     * @param  mixed $allowedValues                 List of the allowed values.
     * 
     * @return self
     */
    public function allowedValueList(/*mixed*/ ...$allowedValues): self
    {
        $this->stringValidation
            ->allowedValueList($allowedValues);

        return $this;
    }
    
    /**
     * Sets a minimum and maximum length to the string.
     *
     * The method calls the lengthRange() method from the string validation class. More information
     * in galastri\extensions\typeValidation\StringValidation class file.
     *
     * @param  int $minLength                          The minimum length required.
     *
     * @param  int $maxLength                          The maximum length allowed.
     *
     * @return self
     */
    public function lengthRange(int $minLength, int $maxLength): self
    {
        $this->stringValidation
            ->lengthRange($minLength, $maxLength);

        return $this;
    }
    
    /**
     * Defines that the value of the string cannot be null.
     *
     * The method calls the denyNull() method from the string validation class. More information in
     * galastri\extensions\typeValidation\StringValidation class file.
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
     * galastri\extensions\typeValidation\StringValidation class file.
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
