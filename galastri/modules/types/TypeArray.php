<?php
declare(strict_types = 1);

namespace galastri\modules\types;

use galastri\core\Debug;
use galastri\extensions\Exception;
use galastri\modules\validation\ArrayValidation;

/**
 * NOTE: THIS TYPE CLASS IS NOT FINISHED.
 * This class creates objects that will act as a array types.
 */
final class TypeArray extends ArrayValidation implements \Language
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
    const VALUE_TYPE = 'array';

    /**
     * List of the type classes. This constant is used when the get method is called.
     */
    const TYPE_CLASS_LIST = [
        'galastri\modules\types\TypeBool',
        'galastri\modules\types\TypeDate',
        'galastri\modules\types\TypeDateTime',
        'galastri\modules\types\TypeFloat',
        'galastri\modules\types\TypeInt',
        'galastri\modules\types\TypeString',
    ];

    /**
     * Stores the real value, after being handled.
     *
     * @var array
     */
    protected array $storedValue = [];

    /**
     * Stores the value while it is being handled.
     *
     * @var mixed
     */
    protected $handlingValue = null;

    /**
     * The constructor of the class can set an initial value to be stored.
     *
     * @param array $value                          An initial value to be stored. It is optional to
     *                                              set it in the constructor.
     *
     * @return void
     */
    public function __construct(/*array*/ $value = [])
    {
        Debug::setBacklog();

        $this->_set($value ?? []);
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
    public function set(/*?array*/ $value = null): self
    {
        Debug::setBacklog();

        $this->forceInitialize = true;

        $this->_set($value ?? []);

        return $this;
    }

    /**
     * This method put the key value in the handling value property to be handled by the class. It
     * can access multidimensional arrays in two ways: by passing the keys as parameter (in order)
     * or by calling this method multiple times.
     *
     * Example: $array = new TypeArray(<array...>);
     *
     *      $array->key(0, 0, 1);
     *      Or
     *      $array->key(0)->key(0)->key(1);
     *
     * @param  int|string ...$keys
     *
     * @return void
     */
    public function key(/*int|string*/ ...$keys)
    {
        $value = $this->getValue();

        foreach ($keys as $key) {
            if (is_array($value) and array_key_exists($key, $value)) {
                $value = $value[$key];
            } else {
                $value = null;
            }
        }

        $this->execHandleValue($value);

        return $this;
    }

    /**
     * This method overrides the get method from the Common trait because it needs to return the
     * value differently. If the value is an instance of the type classes, the value to be get is
     * from get method of the type classes. If not, then it will return the value itself.
     *
     * @return mixed
     */
    public function get()// : mixed
    {
        $value = $this->getValue();

        $this->handling = false;
        $this->handlingValue = null;

        if (is_object($value) and in_array(get_class($value), static::TYPE_CLASS_LIST)) {
            return $value->get();
        } else {
            return $value;
        }
    }

    /**
     * This class recursively rebuild the multidimensional array to a simple array. All the index
     * names are lost in the process, replaced by serialized numbers.
     *
     * Example:
     *      [
     *          'key' => 'value',
     *          'other' => ['value2', 'value3'],
     *          0 => false
     *      ];
     *
     * The above array will be turned into the follow array:
     *
     *      [
     *          0 => 'value',
     *          1 => 'value2',
     *          2 => 'value3',
     *          3 => false
     *      ];
     *
     * @param  mixed $keyMatch                      Returns values only from the specified key.
     *
     * @param  mixed $unique                        Returns only unique values, removing duplicates.
     *
     * @return self
     */
    public function flatten(/*bool|int|string*/ $keyMatch = false, bool $unique = false): self
    {
        $array = $this->getValue();

        $recursive = function ($array, $keyMatch, $result, $recursive) {
            foreach ($array as $key => $value) {
                if (gettype($value) === 'array') {
                    $result = $recursive($value, $keyMatch, $result, $recursive);
                } else {
                    if ($keyMatch === false) {
                        $result[] = $value;
                    } else {
                        if ($key == $keyMatch) {
                            $result[] = $value;
                        }
                    }
                }
            }

            return $result;
        };

        $result = $recursive($array, $keyMatch, [], $recursive);

        $this->execHandleValue($unique ? array_unique($result) : $result);

        return $this;
    }
}
