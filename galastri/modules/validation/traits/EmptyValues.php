<?php

namespace galastri\modules\validation\traits;

use galastri\extensions\Exception;

/**
 * This trait has the methods that validates empty or empty-like values.
 */
trait EmptyValues
{
    /**
     * This method creates a link in the validating chain that stores a closure that compares the
     * validating value with null. If it is, an exception is thrown.
     *
     * @return self
     */
    public function denyNull(): self
    {
        $this->validatingChain[] = function () {
            if ($this->validatingValue === null) {
                $this->defaultMessageSet(
                    self::VALIDATION_CANNOT_BE_NULL[1],
                    self::VALIDATION_CANNOT_BE_NULL[0]
                );
                $this->throwFail();
            }
        };

        return $this;
    }

    /**
     * This method creates a link in the validating chain that stores a closure that checks if the
     * validating value is empty. If it is, then an exception is thrown.
     *
     * PHP empty() function considers as empty the following values:
     *
     *   - An empty string             : ""
     *   - 0 as a string               : "0"
     *   - 0 as an integer             : 0
     *   - 0 as a float                : 0.0
     *   - An empty array              : array(), []
     *   - Unintialized class property : public|private $var;
     *   - NULL
     *   - FALSE
     *
     * @return self
     */
    public function denyEmpty(): self
    {
        $this->validatingChain[] = function () {
            if (empty($this->validatingValue)) {
                $this->defaultMessageSet(
                    self::VALIDATION_CANNOT_BE_EMPTY[1],
                    self::VALIDATION_CANNOT_BE_EMPTY[0]
                );
                $this->throwFail();
            }
        };

        return $this;
    }
}
