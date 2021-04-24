<?php
declare(strict_types = 1);

namespace galastri\modules\types;

use galastri\modules\types\abstraction\TypeNumeric;

/**
 * This class creates objects that will act as an integer type.
 */
final class TypeInt extends TypeNumeric implements \Language
{
    /**
     * This constant define the type of the data the $value property (defined in \galastri\modules\
     * types\traits\Common) will store. It needs to match the possible returning value given by the
     * gettype() function.
     */
    const VALUE_TYPE = 'integer';

    /**
     * Stores a integer or null value.
     *
     * @var null|int
     */
    protected ?int $value = null;

    /**
     * Stores the first value set to the object that isn't null.
     *
     * @var null|int
     */
    protected ?int $initialValue = null;
}
