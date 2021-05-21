<?php

namespace galastri\modules\validation;

use galastri\extensions\Exception;

/**
 * This class put together traits that are related to validation of strings.
 *
 * The class can be used standalone, but its methods are also available with instances of the
 * TypeString class. It doesn't check the type of the value to be validated in the standalone usage.
 *
 * Standalone usage example:
 *
 *      $validation = new StringValidation()
 *      $validation->value('My String')->maxLength(5)->onError('Max length is 5')->validate();
 */
class StringValidation implements interfaces\StringConstants, \Language
{
    use traits\Common;
    use traits\RestrictLists;
    use traits\EmptyValues;
    use traits\StringValues;
}
