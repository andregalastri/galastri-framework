<?php
declare(strict_types = 1);

namespace galastri\modules\types;

/**
 * This class creates objects that will act as a float type.
 */
final class TypeFloat extends TypeNumeric implements \Language
{
    /**
     * This constant define the type of the data the $value property (defined in \galastri\modules\
     * types\traits\Common) will store. It needs to match the possible returning value given by the
     * gettype() function.
     */
    const VALUE_TYPE = 'double';

    /**
     * Stores the value after handling.
     *
     * @var null|float
     */
    protected ?float $storedValue = null;

    /**
     * Stores the temporary value while handling.
     *
     * @var mixed
     */
    protected $handlingValue = null;
}
