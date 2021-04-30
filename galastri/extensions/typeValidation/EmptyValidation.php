<?php

namespace galastri\extensions\typeValidation;

use galastri\extensions\Exception;

/**
 * This validation class has methods that allows to check if the informed data is empty or null. If
 * it is, then it will return an error message.
 */
final class EmptyValidation implements \Language
{
    /**
     * Importing traits to the class.
     */
    use traits\Common;

    /**
     * This method adds a chain link with a function that checks if the data is strictly equal to
     * null. If it is, then an exception is thrown.
     *
     * @return void
     */
    public function denyNull(): void
    {
        $this->chain[] = function () {
            if ($this->value === null) {
                $this->throwErrorMessage();
            }
        };
    }

    /**
     * This method adds a chain link with a function that checks if the data is empty. If it is,
     * then an exception is thrown.
     *
     * PHP empty() function considers empty the follow:
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
     * @return void
     */
    public function denyEmpty(): void
    {
        $this->chain[] = function () {
            if (empty($this->value)) {
                $this->throwErrorMessage();
            }
        };
    }
}
