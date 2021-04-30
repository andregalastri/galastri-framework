<?php
declare(strict_types = 1);

namespace galastri\modules\types;

use galastri\core\Debug;
use galastri\extensions\Exception;
use galastri\extensions\typeValidation\ArrayValidation;

final class TypeArray implements \Language
{
    use traits\Common {
        set as private _set;
        get as private _get;
        clear as private _clear;
    }

    use traits\Concat {
        concat as private _concat;
        concatStart as private _concatStart;
        concatSpacer as private _concatSpacer;
    }

    const VALUE_TYPE = 'array';

    protected array $storedValue = [];
    protected $handlingValue = null;

    private ArrayValidation $arrayValidation;

    public function __construct(/*?string*/ $value = [])
    {
        Debug::setBacklog();

        $this->arrayValidation = new ArrayValidation();
        
        $this->execHandleValue($value);
        $this->execStoreValue(false);
    }
    
    public function key(...$keys)//: mixed
    {
        if (empty($keys)) {
            throw new Exception('self::VIEW_INVALID_DATA_KEY[1]', 'self::VIEW_INVALID_DATA_KEY[0]');
        }

        $array = $this->getValue();

        if ($this->handlingValue === null) {
            $storedValue = &$this->storedValue;
        } else {
            $storedValue = &$this->handlingValue;
        }
        
        $result = null;

        foreach ($keys as $key) {
            array_shift($keys);

            if (isset($array[$key]) and gettype($array) === 'array') {
                $array = $array[$key];

                if (!$array instanceof TypeString and
                    !$array instanceof TypeBool and
                    !$array instanceof TypeInt and
                    !$array instanceof TypeFloat and
                    !$array instanceof TypeArray)
                {
                    $class = $this->execTypeClassName($array, 'galastri\modules\types\\');

                    $result = new $class($storedValue[$key]);
                    $storedValue[$key] = $result;
                } else {
                    $result = $array;
                }

                if (count($keys) > 0) {
                    if ($storedValue[$key] instanceof TypeArray) {
                        return $storedValue[$key]->key(...$keys);
                    }
                }
            } else {
                $result = null;
                break;
            }
        }

        if ($result === null) {
            $this->execHandleValue(null);
            return $this;
        }

        return $result;
    }

    public function unset($key): void
    {
        unset($this->storedValue[$key]);
    }

    public function set($value = null): self
    {
        foreach ($this->getValue() as $key => $data) {
            $this->key($key)->set();
        }
        
        $this->_set($value);
        
        return $this;
    }

    public function clear(): self
    {
        $this->handlingValue = [];

        return $this;
    }

    public function join($spacer = ''): self
    {
        $value = $this->getValue();

        if (is_array($value)) {
            $value = implode($spacer, $value);
        }

        $this->execHandleValue(new TypeString($value));

        return $this;
    }

    public function get($return = VALUE)// : mixed
    {
        $get = $this->_get();

        return $return === KEY ? key($get) : $get;
    }

    public function add($value, $key = null): self
    {
        $array = $this->getValue();

        if ($key === null) {
            $array[] = $value;
        } else {
            if (isset($array[$key])) {
                throw new Exception(
                    'Key $KEY already exists in the array',
                    'G0026'
                );
            }
            $array[$key] = $value;
        }

        $this->execHandleValue($array);

        return $this;
    }


    public function keyExists($key)
    {
        return isset($this->getValue()[$key]);
    }

    public function keyIndex()
    {
        return key($this->getValue());
    }

    public function valueExists(string $search, int $matchType = MATCH_EXACT)
    {
        return !empty($this->searchValue($search, $matchType)->get());
    }

    public function shift()
    {
        $this->execHandleValue(array_shift($this->storedValue));

        return $this->get();
    }

    public function pop()
    {
        $this->execHandleValue(array_pop($this->storedValue));

        return $this->get();
    }

    public function searchKey(/*int|string*/ $search, int $matchType = MATCH_ANY): self
    {
        $array = $this->getValue();

        $arrayKeys = array_keys($array);
        $found = [];
        
        $regex = (function ($a, $b) {
            switch ($b) {
                case MATCH_EXACT:
                    return "$a";
                case MATCH_ANY:
                    return "/$a/";
                case MATCH_START:
                    return "/^$a/";
                case MATCH_END:
                    return "/$a$/";
            }
        })(preg_quote((string)$search, '/'), $matchType);

        foreach ($arrayKeys as $key) {
            preg_match($regex, (string)$key, $match);

            if (!empty($match)) {
                $found[$key] = $array[$key];
            }
        }

        $this->execHandleValue($found);

        return $this;
    }

    public function searchValue(/*int|float|string*/ $search, int $matchType = MATCH_ANY)// : mixed
    {
        $array = $this->getValue();

        $arrayValues = array_values($array);
        $found = [];

        $regex = (function ($a, $b) {
            switch ($b) {
                case MATCH_EXACT:
                    return "/\b$a\b/";
                case MATCH_ANY:
                    return "/$a/";
                case MATCH_START:
                    return "/^$a/";
                case MATCH_END:
                    return "/$a$/";
            }
        })(preg_quote($search, '/'), $matchType);

        foreach ($arrayValues as $key => $value) {
            preg_match($regex, $value, $match);
            if (!empty($match)) {
                $found[$key] = $value;
            }
        }

        $this->execHandleValue($found);
        
        return $this;
    }

    public function rearrange()// : mixed
    {
        $this->execHandleValue(array_values($this->getValue()));

        return $this;
    }

    public function keys()// : mixed
    {
        $array = [];

        foreach($this->getValue() as $key => $value) {
            $array[$key] = $value->get() ?? $this->key($key)->get();
        }

        $this->execHandleValue($array);

        return $this;
    }

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

    public function concat(string ...$values): self
    {
        return $this->execArrayModeConcats('concat', ...$values);
    }

    public function concatStart(string ...$values): self
    {
        return $this->execArrayModeConcats('concatStart', ...$values);
    }

    public function concatSpacer(string ...$values): self
    {
        return $this->execArrayModeConcats('concatSpacer', ...$values);
    }

    private function execArrayModeConcats($concatType, ...$values)
    {
        foreach ($this->getValue() as $key => $data) {
            if ($this->key($key) instanceof TypeString) {
                $this->key($key)->$concatType(...$values);
            }
        }

        $this->execHandleValue($this->getValue());

        return $this;
    }

    public function onError(/*array|string*/ $messageCode, /*float|int|string*/ ...$printfData): self
    {
        $message = $this->execBuildErrorMessage($messageCode, $printfData);

        $this->arrayValidation->onError($message[1], $message[0]);

        return $this;
    }

    public function validate(): ?array
    {
        return $this->getValue();
    }
}
