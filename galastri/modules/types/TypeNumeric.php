<?php
declare(strict_types = 1);

namespace galastri\modules\types;

use galastri\core\Debug;
use galastri\extensions\Exception;
use galastri\modules\validation\NumericValidation;

/**
 * This abstract class defines the base methods that will be used by the TypeInt and TypeFloat classes.
 */
abstract class TypeNumeric extends NumericValidation implements \Language
{
    /**
     * For a better coding and reuse of methods, much of the methods that makes these type classes
     * useful is in trait files, that are imported here.
     */
    use traits\Common;
    use traits\Length;
    use traits\Math;
    use traits\Mask;
    use traits\NumericFormat;

    /**
     * The constructor of the class can set an initial value to be stored.
     *
     * @param int|null|float $value                 An initial value to be stored. It is optional to
     *                                              set it in the constructor.
     *
     * @return void
     */
    public function __construct(/*?int|float*/ $value = null)
    {
        Debug::setBacklog();

        $this->_set($this->convertToRightNumericType($value));
    }

    /**
     * This method executes the methd _set from the Common trait, that stores the value in the
     * $storedValue property.
     *
     * The value will have its type checked and if the value doesn't match the given type, an
     * exception will be thrown. If there are validation methods defined before this method, they
     * will be executed to check if the value matches the defined validation processes.
     *
     * Why not call the _set method directly? Because this method is used internally too, in the
     * constructor, which doesn't allow to make the Debug backlog to be set properly. Methods that
     * are executed directly by the user needs to have its backlog stored right after the call, but
     * if it is called internally too, it messes up the backlog, leading to wrong information if an
     * error occurs.
     *
     * Because of this, this method here works like a bridge between the Debug backlog and the
     * method that executes the code.
     *
     * It can, however, execute modifications in the value before send it to the _set method, which
     * is the case of the TypeNumeric and TypeArray classes.
     *
     * @param  int|null|float $value                The value that will be stored.
     *
     * @return self
     */
    public function set(/*?int|float*/ $value = null): self
    {
        Debug::setBacklog();

        $this->forceInitialize = true;

        $this->_set($this->convertToRightNumericType($value));

        return $this;
    }

    /**
     * This method is exclusive for the TypeNumeric class. It creates a random number between the
     * minimun and the maximum set in the parameters.
     *
     * @param  int $min                             Minimum value that the generated value can have.
     *                                              When undefined, it is set as zero.
     *
     * @param  int|null $max                        Maximum value that the generated value can have.
     *                                              When undefined or null, it will use the maximum
     *                                              integer value that is possible.
     *
     * @return self
     */
    public function random(int $min = 0, ?int $max = null): self
    {
        if ($max === null) {
            $max = mt_getrandmax();
        }

        $randomNumber = mt_rand($min, $max);

        $this->execHandleValue($randomNumber);
        return $this;
    }
}
