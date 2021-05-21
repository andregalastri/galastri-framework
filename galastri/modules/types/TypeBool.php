<?php
declare(strict_types = 1);

namespace galastri\modules\types;

use galastri\core\Debug;
use galastri\extensions\Exception;
use galastri\modules\validation\BoolValidation;

/**
 * This class creates objects that will act as a boolean types.
 */
final class TypeBool extends BoolValidation implements \Language
{
    /**
     * For a better coding and reuse of methods, much of the methods that makes these type classes
     * useful is in trait files, that are imported here.
     */
    use traits\Common;

    /**
     * This constant defines the type of the data that will be stored. The name of the type is based
     * on the possible results of the PHP function gettype.
     */
    const VALUE_TYPE = 'boolean';

    /**
     * Stores the real value, after being handled.
     *
     * @var null|bool
     */
    private ?bool $storedValue = null;

    /**
     * Stores the value while it is being handled.
     *
     * @var mixed
     */
    protected $handlingValue = null;

    /**
     * The constructor of the class can set an initial value to be stored.
     *
     * @param null|bool $value                      An initial value to be stored. It is optional to
     *                                              set it in the constructor.
     *
     * @return void
     */
    public function __construct(/*?bool*/ $value = null)
    {
        Debug::setBacklog();

        $this->_set($value);
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
     * @param  bool|null $value                     The value that will be stored.
     *
     * @return self
     */
    public function set(/*?bool*/ $value = null): self
    {
        Debug::setBacklog();

        $this->forceInitialize = true;

        $this->_set($value);

        return $this;
    }

    /**
     * This method inverts the current value. If it is true, it becomes false. If it is false, it
     * becomes true.
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

    /**
     * This method is a shortcut to set(true). It just stores the value true.
     *
     * @return self
     */
    public function true(): self
    {
        $this->execHandleValue(true);
        return $this;
    }

    /**
     * This method is a shortcut to set(false). It just stores the value false.
     *
     * @return self
     */
    public function false(): self
    {
        $this->execHandleValue(false);
        return $this;
    }

    /**
     * This method checks if the current value is true.
     *
     * @return bool
     */
    public function isTrue(): ?bool
    {
        return $this->getValue();
    }

    /**
     * This method checks if the current value is false.
     *
     * @return bool
     */
    public function isFalse(): ?bool
    {
        return !$this->getValue();
    }
}
