<?php
declare(strict_types = 1);

namespace galastri\modules\types;

/**
 * This class creates objects that will act as a float types. It extends the abstract class
 * TypeNumeric, which defines its methods.
 */
final class TypeFloat extends TypeNumeric implements \Language
{
    /**
     * This constant defines the type of the data that will be stored. The name of the type is based
     * on the possible results of the PHP function gettype.
     */
    const VALUE_TYPE = 'double';

    /**
     * Stores the real value, after being handled.
     *
     * @var null|float
     */
    protected ?float $storedValue = null;

    /**
     * Stores the value while it is being handled.
     *
     * @var mixed
     */
    protected $handlingValue = null;
}
