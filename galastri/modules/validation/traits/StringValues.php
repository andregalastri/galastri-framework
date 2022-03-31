<?php

namespace galastri\modules\validation\traits;

use galastri\extensions\Exception;

/**
 * This trait has the methods that validates strings.
 */
trait StringValues
{
    /**
     * This method creates a link in the validating chain that checks if the data has only lower
     * case chars. If there are upper case chars, an exception is thrown.
     *
     * @return void
     */
    public function denyUpperCase(): self
    {
        $this->validatingChain[] = function () {
            $this->defaultMessageSet(
                self::VALIDATION_STRING_LOWER_CASE_ONLY[1],
                self::VALIDATION_STRING_LOWER_CASE_ONLY[0]
            );
            self::validateCase('/[^\p{Ll}]/u');
        };

        return $this;
    }

    /**
     * This method creates a link in the validating chain that checks if the data has only upper
     * case chars. If there are lower case chars, an exception is thrown.
     *
     * @return void
     */
    public function denyLowerCase(): self
    {
        $this->validatingChain[] = function () {
            $this->defaultMessageSet(
                self::VALIDATION_STRING_UPPER_CASE_ONLY[1],
                self::VALIDATION_STRING_UPPER_CASE_ONLY[0]
            );
            self::validateCase('/[^\p{Lu}]/u');
        };

        return $this;
    }

    /**
     * This method creates a link in the validating chain that checks if the data has more
     * characters than the number informed. If there are, an exception is thrown.
     *
     * @param  int $length                          The maximum length of the data.
     *
     * @return void
     */
    public function maxLength(int $length): self
    {
        $this->validatingChain[] = function () use ($length) {
            if (strlen($this->validatingValue) > $length) {
                $this->defaultMessageSet(
                    self::VALIDATION_STRING_MAX_LENGTH[1],
                    self::VALIDATION_STRING_MAX_LENGTH[0],
                    $length,
                    strlen($this->validatingValue)
                );
                $this->throwFail();
            }
        };

        return $this;
    }

    /**
     * This method creates a link in the validating chain that checks if the data has less
     * characters than the number informed. If there are, an exception is thrown.
     *
     * @param  int $length                          The minimum length of the data.
     *
     * @return void
     */
    public function minLength(int $length): self
    {
        $this->validatingChain[] = function () use ($length) {
            if (strlen($this->validatingValue) < $length) {
                $this->defaultMessageSet(
                    self::VALIDATION_STRING_MIN_LENGTH[1],
                    self::VALIDATION_STRING_MIN_LENGTH[0],
                    $length,
                    strlen($this->validatingValue)
                );
                $this->throwFail();
            }
        };

        return $this;
    }

    /**
     * This method is a shortcut to set a minimum and maximum length of the string.
     *
     * @param  int $minLength                       The minimum length of the string.
     *
     * @param  int $maxLength                       The maximum length of the string.
     *
     * @return void
     */
    public function lengthRange(int $minLength, int $maxLength): self
    {
        $this->minLength($minLength);
        $this->maxLength($maxLength);

        return $this;
    }

    /**
     * This method creates a link in the validating chain that checks if the data has only a allowed
     * groups of chars.
     *
     * @param  string ...$charSets                  Chars that are allowed. Any char can be informed
     *                                              here. There are also some flag names with pre
     *                                              defined groups of chars. Cannot be empty.
     *
     *                                              - Example: instead of use '0-9' to set that
     *                                              numbers are allowed, you can use '--numbers'. It
     *                                              is more readable and any change of the behavior
     *                                              of the charset will affect all methods that use
     *                                              the flag.
     *
     * @return void
     */
    public function allowCharset(string ...$charSets): self
    {
        $this->validatingChain[] = function () use ($charSets) {
            if (empty($charSets)) {
                throw new Exception(self::UNDEFINED_VALIDATION_ALLOWED_CHARSET);
            }

            /**
             * Converts possible flags to regex chars, based on the CHAR_FLAGS constant.
             */
            foreach ($charSets as &$charGroup) {
                $charGroup = self::CHAR_FLAGS[$charGroup] ?? preg_quote($charGroup);
            }
            unset($charGroup);

            /**
             * Then, it is tested if there are unmaches comparing the charsets with the validating
             * values. If there are, an exception is thrown.
             */
            preg_match_all('/[^' . implode($charSets) . ']/u', $this->validatingValue, $unmatches);

            if (!empty($unmatches[0])) {
                $this->defaultMessageSet(
                    self::VALIDATION_STRING_INVALID_CHARS[1],
                    self::VALIDATION_STRING_INVALID_CHARS[0],
                    implode(', ', array_unique($unmatches[0]))
                );
                $this->throwFail();
            }
        };

        return $this;
    }

    /**
     * This method creates a link in the validating chain that checks if the data has some chars
     * that is required. It is necessary to define how many of that charset is needed to make the
     * data valid. To set diffent quantities of charsets, you can call this method again, informing
     * different values.
     *
     * @param  int $minQty                          Minimun quantity of the given charsets.
     *
     * @param  string ...$charSets                  Chars that are allowed. Any char can be informed
     *                                              here. There are also some flag names with pre
     *                                              defined groups of chars. Works in the same way
     *                                              that allowCharset method works. Cannot be empty.
     *
     * @return void
     */
    public function requiredChars(int $minQty, string ...$charSets): self
    {
        $this->validatingChain[] = function () use ($minQty, $charSets) {
            if (empty($charSets)) {
                throw new Exception(self::UNDEFINED_VALIDATION_REQUIRED_CHARSET);
            }

            foreach ($charSets as $charGroup) {
                /**
                 * Converts possible flags to regex chars, based on the CHAR_FLAGS constant.
                 */
                $regex = self::CHAR_FLAGS[$charGroup] ?? preg_quote($charGroup);
                preg_match_all('/[' . $regex . ']/u', $this->validatingValue, $matches);

                /**
                 * Counts the matches and if it is lesser then the minimum quantity, an exception is
                 * thrown.
                 */
                if (count($matches[0]) < $minQty) {
                    $this->defaultMessageSet(
                        self::VALIDATION_STRING_REQUIRED_CHARS[1],
                        self::VALIDATION_STRING_REQUIRED_CHARS[0],
                        $minQty,
                        implode(', ', $matches[0]),
                        count($matches[0])
                    );
                    $this->throwFail();
                }
            }
        };

        return $this;
    }

    /**
     * This is an internal method, used by the lowerCase and upperCase methods.
     *
     * If first filters the value, extracting all letters. Then, it matches all the upper or lower
     * case chars, defined by the $casedUtf8CharsRegex parameter. If there are unmaches, then an
     * exceptions is thrown.
     *
     * @param  mixed $casedUtf8CharsRegex           The regex string with the commands that sets the
     *                                              case it will look for.
     *
     * @return void
     */
    private function validateCase(string $casedUtf8CharsRegex): void
    {
        preg_match_all('/[\p{L}]/u', $this->validatingValue, $allLetters);
        preg_match_all($casedUtf8CharsRegex, implode($allLetters[0]), $unmatch);

        if (!empty($unmatch[0])) {
            $this->throwFail();
        }
    }
}
