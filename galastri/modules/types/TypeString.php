<?php
declare(strict_types = 1);

namespace galastri\modules\types;

use galastri\core\Debug;
use galastri\extensions\Exception;
use galastri\modules\validation\StringValidation;

/**
 * This class creates objects that will act as a string types.
 */
final class TypeString extends StringValidation implements \Language
{
    /**
     * For a better coding and reuse of methods, much of the methods that makes these type classes
     * useful is in trait files, that are imported here.
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
     * This constant defines the type of the data that will be stored. The name of the type is based
     * on the possible results of the PHP function gettype.
     */
    const VALUE_TYPE = 'string';

    /**
     * Stores the real value, after being handled.
     *
     * @var null|string
     */
    protected ?string $storedValue = null;

    /**
     * Stores the value while it is being handled.
     *
     * @var mixed
     */
    protected $handlingValue = null;

    /**
     * The constructor of the class can set an initial value to be stored.
     *
     * @param null|string $value                    An initial value to be stored. It is optional to
     *                                              set it in the constructor.
     *
     * @return void
     */
    public function __construct(/*?string*/ $value = null)
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
     * @param  null|string $value                   The value that will be stored.
     *
     * @return self
     */
    public function set(/*null|string*/ $value = null): self
    {
        Debug::setBacklog();

        $this->forceInitialize = true;

        $this->_set($value);

        return $this;
    }

    /**
     * This method generates a random string based on the given length using one of the available
     * functions of the PHP.
     *
     * @param  int $length                          The length of the random generated string.
     *                                              Default is 15.
     *
     * @return self
     */
    public function random(int $length = 15): self
    {
        /**
         * Checks if there are functions that generates secure random strings. The methods are
         * random_bytes and openssl_random_pseudo_bytes. If these two methods doesn't exists, an
         * exception is thrown.
         */
        if (function_exists("random_bytes")) {
            $bytes = random_bytes((int)ceil($length / 2));
        } elseif (function_exists("openssl_random_pseudo_bytes")) {
            $bytes = openssl_random_pseudo_bytes((int)ceil($length / 2));
        } else {
            throw new Exception(self::SECURE_RANDOM_GENERATOR_NOT_FOUND);
        }

        $this->execHandleValue(bin2hex($bytes));
        return $this;
    }
}
