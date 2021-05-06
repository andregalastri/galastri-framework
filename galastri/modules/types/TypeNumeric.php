<?php
declare(strict_types = 1);

namespace galastri\modules\types;

use galastri\core\Debug;
use galastri\extensions\Exception;
use galastri\extensions\typeValidation\NumericValidation;

/**
 * This class abstracts numeric types to be inherited by numeric types.
 */
abstract class TypeNumeric implements \Language
{
    /**
     * Importing traits to the class.
     */

    /**
     * The TypeNumeric class need to override the getValue method, which is part of the
     * galastri\modules \types\traits\Common trait. It isn't possible to just redeclare the
     * getValue() method here to override it because traits do not accept polimorphism like
     * inheranced classes do. The way to do this is to rename internally the getValue() method to
     * another name and set it as private, so it isn't accessible from outside calls.
     */
    use traits\Common { set as private _set; }
    use traits\Length;
    use traits\Math;
    use traits\Mask;
    use traits\NumericFormat;

    /**
     * Stores an instance of the NumericValidation class, to be used in the validation methods that
     * uses numeric validation. This is uses composition because it gives better control to the
     * visibility of validation methods and properties.
     *
     * @var \galastri\extensions\typeValidation\NumericValidation
     */
    protected NumericValidation $numericValidation;

    /**
     * Sets up the type It also create the object composition of the validation classes.
     *
     * @param null|int|float $value                 The value that will be stored. It is optional to
     *                                              set it in the construct.
     *
     * @return void
     */
    public function __construct(/*?int|float*/ $value = null)
    {
        Debug::setBacklog();

        $this->numericValidation = new NumericValidation();

        $this->execHandleValue($value);
        $this->execStoreValue(false);
    }

    /**
     * Returns the stored value.
     *
     * NOTE: This method is part of the \galastri\modules\types\traits\Common trait, but it was
     * overlapped here because we need make shure that the returning value is being converted to an
     * integer. Many of the numeric methods allows and return floats, so it breaks the int type only
     * concept in this class.
     *
     * @return int|float
     */
    public function set()// : int|float
    {
        if ($this->handlingValue !== null) {
            $this->convertToRightNumericType($this->handlingValue);
        } else {
            $this->convertToRightNumericType($this->storedValue);
        }

        return $this->_set();
    }

    /**
     * Generate a random number which the minimum and maximum value can bet set with the $min and
     * $max parameters. The number generated is store as the value.
     *
     * @param  int $min                             Minimum value to the generated number.
     *
     * @param  int|null $max                        Maximum value to the generated number.
     *
     * @return self
     */
    public function random(int $min = 0, ?int $max = null): self
    {
        if ($max === null) {
            $max = mt_getrandmax();
        }

        $randomNumber = mt_rand($min, $max);

        $this->convertToRightNumericType($randomNumber);

        $this->execHandleValue($randomNumber);
        return $this;
    }

    /**
     * Validation methods via composition. The names of the methods follow the validation classes
     * methods.
     */

    /**
     * Sets the minimum value to the number.
     *
     * The method calls the minValue() method from the numeric validation class. More information in
     * \galastri\extensions\typeValidation\NumericValidation class file.
     *
     * @param  float $minValue                      The minimum value required.
     *
     * @return self
     */
    public function minValue(float $minValue): self
    {
        $this->convertToRightNumericType($minValue);

        $this->numericValidation
            ->minValue($minValue);

        return $this;
    }

    /**
     * Sets the maximum value to the number.
     *
     * The method calls the maxValue() method from the numeric validation class. More information in
     * \galastri\extensions\typeValidation\NumericValidation class file.
     *
     * @param  float $maxValue                      The maximum value required.
     *
     * @return self
     */
    public function maxValue(float $maxValue): self
    {
        $this->convertToRightNumericType($maxValue);

        $this->numericValidation
            ->maxValue($maxValue);

        return $this;
    }

    /**
     * Sets a minimum and maximum value to the number.
     *
     * The method calls the valueRange() method from the numeric validation class. More information in
     * \galastri\extensions\typeValidation\NumericValidation class file.
     *
     * @param  float $minValue                      The minimum value required.
     *
     * @param  float $maxValue                      The maximum value required.
     *
     * @return self
     */
    public function valueRange(float $minValue, float $maxValue): self
    {
        $this->convertToRightNumericType($minValue, $maxValue);

        $this->numericValidation
            ->valueRange($minValue, $maxValue);

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
        $this->numericValidation
            ->restrictList($allowedValues);

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
        $this->numericValidation
            ->denyValues($deniedValues);

        return $this;
    }

    /**
     * Defines that the value of the string cannot be null.
     *
     * The method calls the denyNull() method from the string validation class. More information in
     * \galastri\extensions\typeValidation\NumericValidation class file.
     *
     * @return self
     */
    public function denyNull(): self
    {
        $this->numericValidation
            ->denyNull();

        return $this;
    }

    /**
     * Defines that the value of the string cannot be empty.
     *
     * The method calls the denyEmpty() method from the string validation class. More information in
     * \galastri\extensions\typeValidation\NumericValidation class file.
     *
     * @return self
     */
    public function denyEmpty(): self
    {
        $this->numericValidation
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

        $this->numericValidation->onFail($message[1], $message[0]);

        return $this;
    }

    /**
     * This method executes the validation to the given value. The validation is always when the
     * value is changed, testing the value before store it. However, sometimes is necessary to check
     * if the data is valid without using the setValue() method. For example, after pass the value
     * in the construct of the TypeString class and only after that set up the validation
     * configuration.
     *
     * @return float|int|null
     */
    public function validate()// : ?float|?int
    {
        $this->numericValidation->setValue($this->getValue())->validate();

        return $this->getValue();
    }
}
