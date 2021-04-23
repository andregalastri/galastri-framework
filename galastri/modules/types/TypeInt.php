<?php
declare(strict_types = 1);

namespace galastri\modules\types;

use galastri\core\Debug;
use galastri\extensions\typeValidation\NumericValidation;
use galastri\extensions\typeValidation\EmptyValidation;
use galastri\modules\types\traits\Common;
use galastri\modules\types\traits\Length;
use galastri\modules\types\traits\Math;
use galastri\modules\types\traits\Mask;
use galastri\modules\types\traits\NumericFormat;
use galastri\modules\types\traits\RandomIntValue;

/**
 * TypeInt
 */
final class TypeInt implements \Language
{
    /**
     * Importing traits to the class.
     */

    /**
     * The TypeInt class need to override the getValue method, which is part of the galastri\modules
     * \types\traits\Common trait. It isn't possible to just redeclare the getValue() method here to
     * override it because traits do not accept polimorphism like inheranced classes do. The way to
     * do this is to rename internally the getValue() method to another name and set it as private,
     * so it isn't accessible from outside calls.
     */
    use Common { getValue as private _getValue; }
    use Length;
    use Math;
    use Mask;
    use NumericFormat;
    use RandomIntValue;

    /**
     * This constant define the type of the data the $value property (defined in \galastri\modules\
     * types\traits\Common) will store. It needs to match the possible returning value given by the
     * gettype() function.
     */
    const VALUE_TYPE = 'integer';

    /**
     * Stores the string or null.
     *
     * @var null|int
     */
    private ?int $value = null;

    /**
     * Stores the first value set to the object that isn't null.
     *
     * @var null|int
     */
    private ?int $initialValue = null;
    
    /**
     * Stores an instance of the NumericValidation class, to be used in the validation methods that
     * uses numeric validation. This is uses composition because it gives better control to the
     * visibility of validation methods and properties.
     *
     * @var \galastri\extensions\typeValidation\NumericValidation
     */
    private NumericValidation $numericValidation;

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
     * @param null|int $value                       The value that will be stored. It is optional to
     *                                              set it in the construct.
     *
     * @param bool $saveHistory                     Defines if the changes of the value will be
     *                                              saved in a history, allowing to revert the value
     *                                              to previous values. Default is false.
     *
     * @return void
     */
    public function __construct(/*?int*/ $value = null, bool $saveHistory = false)
    {
        Debug::setBacklog();

        $this->saveHistory = $saveHistory;
        $this->numericValidation = new NumericValidation();
        $this->emptyValidation = new EmptyValidation();

        $this->execSetValue($value);
    }

    /**
     * Returns the stored value.
     *
     * NOTE: This method is part of the \galastri\modules\types\traits\Common trait, but it was
     * overlapped here because we need make shure that the returning value is being converted to an
     * integer. Many of the numeric methods allows and return floats, so it breaks the int type only
     * concept in this class.
     *
     * @return int
     */
    public function getValue(): int
    {
        return (int)$this->value;
    }

    /**
     * Sets the minimum value to the number.
     *
     * The method calls the minValue() method from the numeric validation class. More information in
     * \galastri\extensions\typeValidation\NumericValidation class file.
     *
     * @param  int $minValue                        The minimum value required.
     *
     * @return self
     */
    public function minValue(int $minValue): self
    {
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
     * @param  int $maxValue                        The maximum value required.
     *
     * @return self
     */
    public function maxValue(int $maxValue): self
    {
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
     * @param  int $minValue                        The minimum value required.
     *
     * @param  int $maxValue                        The maximum value required.
     *
     * @return self
     */
    public function valueRange(int $minValue, int $maxValue): self
    {
        $this->numericValidation
            ->valueRange($minValue, $maxValue);

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
        $this->emptyValidation
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
    public function onError(string $message, string $code = 'G0023'): self
    {
        $this->errorMessage[0] = $code;
        $this->errorMessage[1] = $message;

        $this->numericValidation->onError($message, $code);
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
        $this->numericValidation->setValue($value ?? $this->value)->validate();
        $this->emptyValidation->setValue($value ?? $this->value)->validate();

        return $this;
    }
}
