<?php

namespace galastri\modules\validation;

use galastri\extensions\Exception;

/**
 * This class put together traits that are related to validation of integers and floats.
 *
 * The class can be used standalone, but its methods are also available with instances of the
 * TypeInt and TypeFloat classes. It doesn't check the type of the value to be validated in the
 * standalone usage.
 *
 * Standalone usage example:
 *
 *      $validation = new NumericValidation()
 *      $validation->value(15)->minValue(5)->onError('Min value is 5')->validate();
 */
class NumericValidation implements \Language
{
    use traits\Common;
    use traits\RestrictLists;
    use traits\EmptyValues;
    use traits\NumericValues;
}
