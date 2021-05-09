<?php

namespace galastri\extensions\typeValidation;

use galastri\extensions\Exception;

/**
 * This validation class has methods that allows to check if the informed data has certain
 * characters, or force the data to have some of them. It also strict the length of the data, and
 * many other verifications.
 */
final class NumericValidation implements \Language
{
    /**
     * Importing traits to the class.
     */
    use traits\Common;
    use traits\RestrictLists;
    use traits\EmptyValues;
    use traits\NumericValues;
}
