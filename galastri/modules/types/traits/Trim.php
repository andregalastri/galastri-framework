<?php

namespace galastri\modules\types\traits;

/**
 * This trait has the methods related to filter the edges of the current value.
 */
trait Trim
{
    /**
     * Remove whitespaces and any of the given charSet from both the edges of the string (from start
     * and from end).
     *
     * @param  string $charSet                      Optional chars that will be removed from the
     *                                              string.
     *
     * @return self
     */
    public function trim(?string ...$charSet): self
    {
        $this->execHandleValue($this->execTrim($this->getValue(), $charSet));
        return $this;
    }

    /**
     * Remove whitespaces and any of the given charSet from the start edge of the string.
     *
     * @param  string $charSet                      Optional chars that will be removed from the
     *                                              string.
     *
     * @return self
     */
    public function trimStart(?string ...$charSet): self
    {
        $this->execHandleValue($this->execTrimStart($this->getValue(), $charSet));
        return $this;
    }

    /**
     * Remove whitespaces and any of the given charSet from the end edge of the string.
     *
     * @param  string $charSet                      Optional chars that will be removed from the
     *                                              string.
     *
     * @return self
     */
    public function trimEnd(?string ...$charSet): self
    {
        $this->execHandleValue($this->execTrimEnd($this->getValue(), $charSet));
        return $this;
    }


    /**
     * Prepare the charset to be recognized by the PHP ltrim() and rtrim() functions. It needs to
     * filter the charset to regex ready chars.
     *
     * It first set the whitespace as a part of the charset. After that, it check each of the
     * $charSet parameter. If its first char is equal to a backslash bar \, then it is ignored,
     * else, it is converted to a regex ready char.
     *
     * After all, the method return all the charset into one string.
     *
     * @param  array $charSet                       Chars that will be prepared by the method.
     *
     * @return string
     */
    private function prepareTrimCharSet(array $charSet): string
    {
        $charSet[] = ' ';
        foreach ($charSet as &$char) {
            if ($char[0] === '\\') {
                continue;
            }
            $char = preg_quote($char);
        }

        unset($char);

        return implode($charSet);
    }

    /**
     * This method execute the removal of the whitespaces and given charsets from the both edges of
     * the string. The charsets are prepared and then it executes a rtrim() function to filter the
     * end edge and ltrim() function to filter the start edge.
     *
     * @param  mixed $string                        The string that will be filtered
     *
     * @param  mixed $charSet                       The charsets that will be removed from the
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
     * This method execute the removal of the whitespaces and given charsets from the start edge of
     * the string. The charsets are prepared and then it executes a ltrim() function to filter the
     * start edge.
     *
     * @param  mixed $string                        The string that will be filtered
     *
     * @param  mixed $charSet                       The charsets that will be removed from the
     *                                              start edge.
     *
     * @return string
     */
    private function execTrimStart(?string $string, array $charSet): string
    {
        $charSet = $this->prepareTrimCharSet($charSet);
        return ltrim($string, $charSet);
    }


    /**
     * This method execute the removal of the whitespaces and given charsets from the start edge of
     * the string. The charsets are prepared and then it executes a rtrim() function to filter the
     * end edge.
     *
     * @param  mixed $string                        The string that will be filtered
     *
     * @param  mixed $charSet                       The charsets that will be removed from the
     *                                              end edge.
     *
     * @return string
     */
    private function execTrimEnd(?string $string, array $charSet): string
    {
        $charSet = $this->prepareTrimCharSet($charSet);
        return rtrim($string, $charSet);
    }
}
