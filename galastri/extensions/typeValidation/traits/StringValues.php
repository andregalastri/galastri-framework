<?php

namespace galastri\extensions\typeValidation\traits;

use galastri\extensions\Exception;

/**
 * This validation class has methods that allows to check if the informed data has certain
 * characters, or force the data to have some of them. It also strict the length of the data, and
 * many other verifications.
 */
trait StringValues
{
    /**
     * This method adds a chain link with a function that checks if the data has only lower case
     * chars. If there are other chars instead of lower case, an exception is thrown.
     *
     * @return void
     */
    public function denyUpperCase(): void
    {
        $this->chain[] = function () {
            $this->defaultMessageSet(
                self::VALIDATION_STRING_LOWER_CASE_ONLY[1],
                self::VALIDATION_STRING_LOWER_CASE_ONLY[0]
            );
            self::validateCase('/[^\p{Ll}]/u');
        };
    }

    /**
     * This method adds a chain link with a function that checks if the data has only upper case
     * chars. If there are other chars instead of upper case, an exception is thrown.
     *
     * @return void
     */
    public function denyLowerCase(): void
    {
        $this->chain[] = function () {
            $this->defaultMessageSet(
                self::VALIDATION_STRING_UPPER_CASE_ONLY[1],
                self::VALIDATION_STRING_UPPER_CASE_ONLY[0]
            );
            self::validateCase('/[^\p{Lu}]/u');
        };
    }


    /**
     * This method adds a chain link with a function that checks if the data has more length than
     * the number informed. If there is, an exception is thrown.
     *
     * @param  int $length                          The maximum length of the data.
     *
     * @return void
     */
    public function maxLength(int $length): void
    {
        $this->chain[] = function () use ($length) {
            if (strlen($this->value) > $length) {
                $this->defaultMessageSet(
                    self::VALIDATION_STRING_MAX_LENGTH[1],
                    self::VALIDATION_STRING_MAX_LENGTH[0],
                    $length,
                    strlen($this->value)
                );
                $this->throwErrorMessage();
            }
        };
    }

    /**
     * This method adds a chain link with a function that checks if the data has less length than
     * the number informed. If there is, an exception is thrown.
     *
     * @param  int $length                          The minimum length of the data.
     *
     * @return void
     */
    public function minLength(int $length): void
    {
        $this->chain[] = function () use ($length) {
            if (strlen($this->value) < $length) {
                $this->defaultMessageSet(
                    self::VALIDATION_STRING_MIN_LENGTH[1],
                    self::VALIDATION_STRING_MIN_LENGTH[0],
                    $length,
                    strlen($this->value)
                );
                $this->throwErrorMessage();
            }
        };
    }

    /**
     * This method is a shortcut to set a minimum and maximum length to the string.
     *
     * @param  int $minLength                          The minimum length of the data.
     *
     * @param  int $maxLength                          The maximum length of the data.
     *
     * @return void
     */
    public function lengthRange(int $minLength, int $maxLength): void
    {
        $this->minLength($minLength);
        $this->maxLength($maxLength);
    }

    /**
     * This method adds a chain link with a function that checks if the data has only the allowed
     * groups of chars defined.
     *
     * It first converts possible flags to regex chars, based on the CHAR_FLAGS constant. Then, the
     * charset is tested if there are unmaches. If there are, an exception is thrown.
     *
     * @param  array $charSets                      The chars that are allowed. Any char can be
     *                                              informed here. There are also some flag names
     *                                              with pre defined groups of chars.
     *
     *                                              - Example: instead of use '0-9' to set that
     *                                              numbers are allowed, you can use '--numbers'. It
     *                                              is more readable and any change of the behaviour
     *                                              of the charset will affect all methods that use
     *                                              the flag.
     *
     * @return void
     */
    public function allowCharset(array $charSets): void
    {
        $this->chain[] = function () use ($charSets) {
            if (empty($charSets)) {
                throw new Exception(self::UNDEFINED_VALIDATION_ALLOWED_CHARSET[1], self::UNDEFINED_VALIDATION_ALLOWED_CHARSET[0]);
            }

            foreach ($charSets as &$charGroup) {
                $charGroup = self::CHAR_FLAGS[$charGroup] ?? preg_quote($charGroup);
            }
            unset($charGroup);

            preg_match_all('/[^' . implode($charSets) . ']/u', $this->value, $unmatches);

            if (!empty($unmatches[0])) {
                $this->defaultMessageSet(
                    self::VALIDATION_STRING_INVALID_CHARS[1],
                    self::VALIDATION_STRING_INVALID_CHARS[0],
                    implode(', ', array_unique($unmatches[0]))
                );
                $this->throwErrorMessage();
            }
        };
    }

    /**
     * This method adds a chain link with a function that checks if the data has some chars that it
     * is required to have. In any case, it is necessary to define how many of that charset is
     * needed to make the data valid. To set diffent quantity of various charsets, you can call this
     * method as many times it is needed.
     *
     * @param  int $minQty                          Minimun quantity of the given charsets.
     *
     * @param  array $charSets                      The chars that are allowed. Any char can be
     *                                              informed here. There are also some flag names
     *                                              with pre defined groups of chars. Works the same
     *                                              way that allowCharset() method works.
     *
     * @return void
     */
    public function requiredChars(int $minQty, array $charSets)
    {
        $this->chain[] = function () use ($minQty, $charSets) {
            if (empty($charSets)) {
                throw new Exception(
                    self::UNDEFINED_VALIDATION_REQUIRED_CHARSET[1],
                    self::UNDEFINED_VALIDATION_REQUIRED_CHARSET[0]
                );
            }

            foreach ($charSets as $charGroup) {
                $regex = self::CHAR_FLAGS[$charGroup] ?? preg_quote($charGroup);

                preg_match_all('/[' . $regex . ']/u', $this->value, $matches);

                if (count($matches[0]) < $minQty) {
                    $this->defaultMessageSet(
                        self::VALIDATION_STRING_REQUIRED_CHARS[1],
                        self::VALIDATION_STRING_REQUIRED_CHARS[0],
                        $minQty,
                        implode(', ', $matches[0]),
                        count($matches[0])
                    );
                    $this->throwErrorMessage();
                }
            }
        };
    }

    /**
     * This is an internal method, used by the lowerCase() and upperCase() methods.
     *
     * If first filters the value extracting all letters. Then, it matches all the upper or lower
     * case chars, defined by the $casedUtf8CharsRegex parameter. If there are unmaches, then an
     * exceptions is thrown.
     *
     * @param  mixed $casedUtf8CharsRegex           The regex string with the commands that sets the
     *                                              case it will look for.
     *
     * @return void
     */
    private function validateCase($casedUtf8CharsRegex)
    {
        preg_match_all('/[\p{L}]/u', $this->value, $allLetters);
        preg_match_all($casedUtf8CharsRegex, implode($allLetters[0]), $unmatch);

        if (!empty($unmatch[0])) {
            $this->throwErrorMessage();
        }
    }
}
