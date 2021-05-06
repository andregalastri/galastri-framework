<?php
declare(strict_types = 1);

namespace galastri\modules\types;

use galastri\core\Debug;
use galastri\extensions\Exception;
use galastri\extensions\typeValidation\StringValidation;

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
    use traits\FilePath;
    use traits\StringMatch;

    /**
     * This constant define the type of the data the $value property (defined in galastri\modules\
     * types\traits\Common) will store. It needs to match the possible returning value given by the
     * gettype() function.
     */
    const VALUE_TYPE = 'string';

    /**
     * Stores the value after handling.
     *
     * @var null|string
     */
    protected ?string $storedValue = null;

    /**
     * Stores the temporary value while handling.
     *
     * @var mixed
     */
    protected $handlingValue = null;

    /**
     * Stores an instance of the StringValidation class, to be used in the validation methods that
     * uses string validation. This is uses composition because it gives better control to the
     * visibility of validation methods and properties.
     *
     * @var galastri\extensions\typeValidation\StringValidation
     */
    private StringValidation $stringValidation;

    /**
     * Sets up the type It also create the object composition of the validation classes.
     *
     * @param null|string $value                    The value that will be stored. It is optional to
     *                                              set it in the construct.
     *
     * @return void
     */
    public function __construct(/*?string*/ $value = null)
    {
        Debug::setBacklog();

        $this->stringValidation = new StringValidation();

        $this->execHandleValue($value);
        $this->execStoreValue(false);
    }

    /**
     * Generate a random string with hexadecimal chars, which the length can bet set with the length
     * parameter and store as the value.
     *
     * @param  int $length                          Length of the random generated string.
     *
     * @return self
     */
    public function random(int $length = 15): self
    {
        if (function_exists("random_bytes")) {
            $bytes = random_bytes((int)ceil($length / 2));
        } elseif (function_exists("openssl_random_pseudo_bytes")) {
            $bytes = openssl_random_pseudo_bytes((int)ceil($length / 2));
        } else {
            throw new Exception(self::SECURE_RANDOM_GENERATOR_NOT_FOUND[0], self::SECURE_RANDOM_GENERATOR_NOT_FOUND[1]);
        }

        $this->execHandleValue(bin2hex($bytes));
        return $this;
    }

    public function setNull(): void
    {
        $this->stringValidation->setValue(null)->validate();

        $this->handlingValue = null;
        $this->storedValue = null;
    }

    /**
     * Validation methods via composition. The names of the methods follow the validation classes
     * methods.
     */

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
     * Restricts the possible values to a restrict list of allowed values.
     *
     * The method calls the restrictList() method from the string validation class. This class
     * imports the trait galastri\extensions\typeValidation\traits\AllowedValueList. More
     * information in the file of the trait.
     *
     * @param  mixed $allowedValues                 List of the allowed values.
     *
     * @return self
     */
    public function restrictList(/*mixed*/ ...$allowedValues): self
    {
        $this->stringValidation
            ->restrictList($allowedValues);

        return $this;
    }

    /**
     * Allow only lower case strings. Calls the lowerCase() method from the string validation class.
     * More information in galastri\extensions\typeValidation\StringValidation class file.
     *
     * @return self
     */
    public function denyUpperCase(): self
    {
        $this->stringValidation
            ->denyUpperCase();
        return $this;
    }

    /**
     * Allow only upper case strings. Calls the upperCase() method from the string validation class.
     * More information in galastri\extensions\typeValidation\StringValidation class file.
     *
     * @return self
     */
    public function denyLowerCase(): self
    {
        $this->stringValidation
            ->denyLowerCase();

        return $this;
    }

    /**
     * Sets a list of denied values.
     *
     * The method calls the denyValues() method from the string validation class. This class
     * imports the trait galastri\extensions\typeValidation\traits\AllowedValueList. More
     * information in the file of the trait.
     *
     * @param  mixed $deniedValues                  List of the denied values.
     *
     * @return self
     */
    public function denyValues(/*mixed*/ ...$deniedValues): self
    {
        $this->stringValidation
            ->denyValues($deniedValues);

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
        $this->stringValidation
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
        $this->stringValidation
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
    public function onFail(/*array|string*/ $messageCode, /*float|int|string*/ ...$printfData): self
    {
        $message = $this->execBuildErrorMessage($messageCode, $printfData);

        $this->stringValidation->onFail($message[1], $message[0]);

        return $this;
    }

    /**
     * This method executes the validation to the given value. The validation is always when the
     * value is changed, testing the value before store it. However, sometimes is necessary to check
     * if the data is valid without using the setValue() method. For example, after pass the value
     * in the construct of the TypeString class and only after that set up the validation
     * configuration.
     *
     * @return string
     */
    public function validate(): ?string
    {
        $this->stringValidation->setValue($this->getValue())->validate();

        return $this->getValue();
    }
}
