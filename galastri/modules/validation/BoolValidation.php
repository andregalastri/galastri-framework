<?php

namespace galastri\modules\validation;

use galastri\extensions\Exception;

/**
 * This class put together traits that are related to validation of booleans.
 *
 * The class can be used standalone, but its methods are also available with instances of the
 * TypeBool class. It doesn't check the type of the value to be validated in the standalone usage.
 *
 * Standalone usage example:
 *
 *      $validation = new BoolValidation()
 *      $validation->value(null)->denyNull()->onError('Value cannot be null')->validate();
 */
class BoolValidation implements \Language
{
    use traits\Common;
    use traits\EmptyValues;
}
