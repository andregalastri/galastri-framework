<?php

namespace galastri\modules\types\traits;

/**
 * This trait has the methods related to filter the edges of the current value.
 */
trait Trim
{
    /**
     * This method removes the given charSet from both the edges of the string, from start and from
     * the end.
     *
     * @param  string $charSet                      Chars that will be removed from the string.
     *
     * @return self
     */
    public function trim(?string ...$charSet): self
    {
        $this->execHandleValue($this->execTrim($this->getValue(), $charSet));
        return $this;
    }

    /**
     * This method removes the given charSet from the start of the string.
     *
     * @param  string $charSet                      Chars that will be removed from the string.
     *
     * @return self
     */
    public function trimStart(?string ...$charSet): self
    {
        $this->execHandleValue($this->execTrimStart($this->getValue(), $charSet));
        return $this;
    }

    /**
     * This method removes the given charSet from the end edge of the string.
     *
     * @param  string $charSet                      Chars that will be removed from the string.
     *
     * @return self
     */
    public function trimEnd(?string ...$charSet): self
    {
        $this->execHandleValue($this->execTrimEnd($this->getValue(), $charSet));
        return $this;
    }
    /**
     * This method executes the removal of the given charsets from the both edges of the string. The
     * charsets are prepared and then it executes a rtrim function to filter the end edge and
     * ltrim() function to filter the start edge.
     *
     * @param  null|string $string                  The string that will be filtered
     *
     * @param  array $charSet                       The charsets that will be removed from the
     *                                              edges.
     *
     * @return string
     */
    private function execTrim(?string $string, array $charSet): string
    {
        $charSet = $this->prepareTrimCharSet($charSet);
        return ltrim(rtrim($string, $charSet), $charSet);
    }

    /**
     * This method executes the removal of the given charsets from the start of the string. The
     * charsets are prepared and then it executes a ltrim function to filter the start edge.
     *
     * @param  mixed $string                        The string that will be filtered
     *
     * @param  mixed $charSet                       The charsets that will be removed from the start
     *                                              edge.
     *
     * @return string
     */
    private function execTrimStart(?string $string, array $charSet): string
    {
        $charSet = $this->prepareTrimCharSet($charSet);
        return ltrim($string, $charSet);
    }

    /**
     * This method executes the removal of the given charsets from the end of the string. The
     * charsets are prepared and then it executes a rtrim function to filter the end edge.
     *
     * @param  mixed $string                        The string that will be filtered
     *
     * @param  mixed $charSet                       The charsets that will be removed from the end
     *                                              edge.
     *
     * @return string
     */
    private function execTrimEnd(?string $string, array $charSet): string
    {
        $charSet = $this->prepareTrimCharSet($charSet);
        return rtrim($string, $charSet);
    }

    /**
     * This method prepares the charset to the charset to regex ready chars, to be recognizable by
     * the PHP ltrim and rtrim functions.
     *
     * @param  array $charSet                       Chars that will be prepared by the method.
     *
     * @return string
     */

    private function prepareTrimCharSet(array $charSet): string
    {
        /**
         * Sets the whitespace as a part of the charset by default.
         */
        $charSet[] = ' ';

        /**
         * Checks each of the $charSet parameter. If its first char is equal to a backslash bar \,
         * then it is ignored, else, it is converted to a regex ready char.
         */
        foreach ($charSet as &$char) {
            if ($char[0] === '\\') {
                continue;
            }
            $char = preg_quote($char);
        }
        unset($char);

        /**
         * Returns all the charset into one string.
         */
        return implode($charSet);
    }
}
