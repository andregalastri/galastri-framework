<?php

namespace galastri\modules\validation;

use galastri\extensions\Exception;

/**
 * This class put together traits that are related to validation of arrays.
 *
 * The class can be used standalone, but its methods are also available with instances of the
 * TypeArray class. It doesn't check the type of the value to be validated in the standalone usage.
 *
 * Standalone usage example:
 *
 *      $validation = new ArrayValidation()
 *      $validation->value(['a', 'b'])->denyEmpty()->onError('Value cannot be empty')->validate();
 */
class ArrayValidation implements \Language
{
    use traits\Common;
    use traits\EmptyValues;
}
