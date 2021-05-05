<?php

namespace galastri\modules\types\traits;

use galastri\modules\types\TypeString;

/**
 * This trait has the methods related to split the string in to parts.
 */
trait Split
{
    /**
     * This method splits the current value in to pieces and return an array with the parts. The
     * split is based on a delimiter in the string.
     *
     * Example
     * - String    : 'my/string'
     * - Delimiter : '/'
     * - Result    : [0] => 'my', [1] => 'string'
     *
     * @param  string $delimiter                    A delimiter that will define where the split
     *                                              will occur.
     *
     * @param  int $limit                           Maximum number of pieces returned.
     *
     * @return array
     */
    public function split(string $delimiter, int $limit = PHP_INT_MAX): array
    {
        $value = $this->getValue();
        $splittedObjects = [];

        foreach (explode($delimiter, $value, $limit) as $value) {
            $splittedObjects[] = new TypeString($value);
        }

        return $splittedObjects;
    }
}
