<?php
declare(strict_types = 1);

namespace galastri\modules\types;

use galastri\modules\types\abstraction\TypeNumeric;

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
     * Stores a float or null value.
     *
     * @var null|float
     */
    protected ?float $value = null;

    /**
     * Stores the first value set to the object that isn't null.
     *
     * @var null|float
     */
    protected ?float $initialValue = null;
}
