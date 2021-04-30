<?php

namespace galastri\extensions\typeValidation\traits;

use galastri\core\Debug;
use galastri\extensions\Exception;
use galastri\modules\types\TypeArray;

/**
 * This trait has the methods related to the allowed value list.
 */
trait RestrictLists
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
    public function restrictList(array $allowedValues): self
    {
        $allowedValues = new TypeArray($allowedValues);
        $allowedValues->flatten()->set();

        if ($allowedValues->isEmpty()) {
            throw new Exception(
                self::VALIDATION_UNDEFINED_VALUES_ALLOWED_LIST[1],
                self::VALIDATION_UNDEFINED_VALUES_ALLOWED_LIST[0],
            );
        }

        $this->chain[] = function () use ($allowedValues) {
            $resolveValues = function () use ($allowedValues) {
                foreach ($allowedValues->get() as $allowedValue) {
                    $data[] = $allowedValue.'('.str_replace(['double'], ['float'], $allowedValue).')';
                }
                return implode(', ', $data);
            };

            foreach ($allowedValues->get() as $allowedValue) {
                if ($allowedValue === $this->value) {
                    return;
                }
            }

            $this->defaultMessageSet(
                self::VALIDATION_NO_VALUE_IN_ALLOWED_LIST[1],
                self::VALIDATION_NO_VALUE_IN_ALLOWED_LIST[0],
                var_export($this->value, true),
                $resolveValues()
            );
            $this->throwErrorMessage();
        };

        return $this;
    }

    /**
     * This method sets an list of denied values which the value can be. It compares the value to
     * each value set in the list in strict mode, which means that it will compare the value and the
     * type.
     *
     * If the value does match one of the denied values, an exception is thrown.
     *
     * NOTE: This method do not check if the values of the list are of the same type of the type
     * object. It just compares the current value to the list.
     *
     * @param  array $deniedValues                  List with the denied values. Cannot be empty.
     *
     * @return self
     */
    public function denyValues(array $deniedValues): self
    {
        $deniedValues = new TypeArray($deniedValues);
        $deniedValues->flatten()->set();

        if ($deniedValues->isEmpty()) {
            throw new Exception(
                self::VALIDATION_UNDEFINED_VALUES_DENIED_LIST[1],
                self::VALIDATION_UNDEFINED_VALUES_DENIED_LIST[0],
            );
        }

        $this->chain[] = function () use ($deniedValues) {
            $resolveValues = function () use ($deniedValues) {
                foreach ($deniedValues->get() as $deniedValue) {
                    $data[] = $deniedValue.'('.str_replace(['double'], ['float'], $deniedValue).')';
                }
                return implode(', ', $data);
            };

            foreach ($deniedValues->get() as $deniedValue) {
                if ($deniedValue === $this->value) {
                    $this->defaultMessageSet(
                        self::VALIDATION_VALUE_IN_DENIED_LIST[1],
                        self::VALIDATION_VALUE_IN_DENIED_LIST[0],
                        var_export($this->value, true),
                        $resolveValues()
                    );
                    $this->throwErrorMessage();
                }
            }
        };

        return $this;
    }
}
