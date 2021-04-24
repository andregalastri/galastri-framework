<?php

namespace galastri\extensions\typeValidation\traits;

use galastri\core\Debug;
use galastri\extensions\Exception;

/**
 * This trait has the methods related to the allowed value list.
 */
trait AllowedValueList
{
    /**
     * This method sets an restrict possible value list which the value can be. It compares the
     * value to each value set in the list in strict mode, which means that it will compare the
     * value and the type.
     *
     * If the value doesn't match one of the allowed values, an exception is thrown.
     *
     * NOTE: This method do not check if the values of the list are of the same type of the type
     * object. It just compares the current value to the list.
     *
     * @param  array $allowedValues                 List with the allowed values. Cannot be empty.
     *
     * @return self
     */
    public function allowedValueList(array $allowedValues): self
    {
        if (empty($allowedValues)) {
            throw new Exception(
                self::VALIDATION_UNDEFINED_VALUES_ALLOWED_LIST[1],
                self::VALIDATION_UNDEFINED_VALUES_ALLOWED_LIST[0],
            );
        }

        $this->chain[] = function () use ($allowedValues) {
            foreach ($allowedValues as $allowedValue) {
                if ($allowedValue === $this->value) {
                    return;
                }
            }

            $this->defaultMessageSet(
                self::VALIDATION_NO_VALUE_IN_ALLOWED_LIST[1],
                self::VALIDATION_NO_VALUE_IN_ALLOWED_LIST[0],
                var_export($this->value, true),
                implode(', ', $allowedValues)
            );
            $this->throwErrorMessage();
        };

        return $this;
    }
}
