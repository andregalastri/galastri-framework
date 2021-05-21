<?php

namespace galastri\modules\validation\traits;

use galastri\core\Debug;
use galastri\extensions\Exception;
use galastri\modules\types\TypeArray;

/**
 * This trait has the methods that validates allowed or restrict lists.
 */
trait RestrictLists
{
    /**
     * This method creates a link in the validating chain that sets a restrict list of possible
     * values. It compares each value from the list in strict mode, which means that it will compare
     * the value and the type.
     *
     * If the value doesn't match one of the allowed values, an exception is thrown.
     *
     * @param  int|string ...$allowedValues         List of the allowed values. Cannot be empty.
     *
     * @return self
     */
    public function restrictList(/*int|string*/ ...$allowedValues): self
    {
        $allowedValues = (new TypeArray($allowedValues))->flatten()->get();

        /**
         * Throws an exception if the $allowedValues is empty.
         */
        if (empty($allowedValues)) {
            throw new Exception(
                self::VALIDATION_UNDEFINED_VALUES_ALLOWED_LIST[1],
                self::VALIDATION_UNDEFINED_VALUES_ALLOWED_LIST[0],
            );
        }

        $this->validatingChain[] = function () use ($allowedValues) {
            /**
             * Return if there is an allowed value that matches the validating value.
             */
            foreach ($allowedValues as $allowedValue) {
                if ($allowedValue === $this->validatingValue) {
                    return;
                }
            }

            /**
             * Throws an exception if there is no allowed value that matches the validating value.
             */
            $this->defaultMessageSet(
                self::VALIDATION_NO_VALUE_IN_ALLOWED_LIST[1],
                self::VALIDATION_NO_VALUE_IN_ALLOWED_LIST[0],
                var_export($this->validatingValue, true),
                $this->resolveValues($allowedValues)
            );
            $this->throwFail();
        };

        return $this;
    }

    /**
     * This method creates a link in the validating chain that sets a list of denied values. It
     * compares the value of each value and if the value does match one of the denied values, an
     * exception is thrown.
     *
     * @param  int|string ...$deniedValues          List with the denied values. Cannot be empty.
     *
     * @return self
     */
    public function denyValues(/*int|string*/ ...$deniedValues): self
    {
        $deniedValues = (new TypeArray($deniedValues))->flatten()->get();

        /**
         * Throws an exception if the $deniedValues is empty.
         */
        if (empty($deniedValues)) {
            throw new Exception(
                self::VALIDATION_UNDEFINED_VALUES_DENIED_LIST[1],
                self::VALIDATION_UNDEFINED_VALUES_DENIED_LIST[0],
            );
        }

        $this->validatingChain[] = function () use ($deniedValues) {
            /**
             * Searchs for each denied value and compare if it matches with the validating value. If
             * they match, then an exception is thrown.
             */
            foreach ($deniedValues as $deniedValue) {
                if ($deniedValue === $this->validatingValue) {
                    $this->defaultMessageSet(
                        self::VALIDATION_VALUE_IN_DENIED_LIST[1],
                        self::VALIDATION_VALUE_IN_DENIED_LIST[0],
                        var_export($this->validatingValue, true),
                        $this->resolveValues($deniedValues)
                    );
                    $this->throwFail();
                }
            }
        };

        return $this;
    }

    /**
     * This method is used internally to return a better understanding list values with their types.
     *
     * @param  array $valueList                     The denied or restrict lists.
     *
     * @return void
     */
    private function resolveValues(array $valueList): string
    {
        foreach ($valueList as $value) {
            $data[] = $value.'('.str_replace(['double'], ['float'], gettype($value)).')';
        }
        return implode(', ', $data);
    }
}
