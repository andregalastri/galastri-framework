<?php

namespace galastri\modules\types\traits;

trait Trim
{
    /**
     * Undocumented function
     *
     * @param string ...$charList
     * @return void
     */
    public function trim(string ...$charList)
    {
        $this->execSetValue($this->execTrim($this->getValue(), $charList));
        return $this;
    }

    /**
     * Undocumented function
     *
     * @param string ...$charList
     * @return void
     */
    public function trimStart(string ...$charList)
    {
        $this->execSetValue($this->execTrimStart($this->getValue(), $charList));
        return $this;
    }

    /**
     * Undocumented function
     *
     * @param string ...$charList
     * @return void
     */
    public function trimEnd(string ...$charList)
    {
        $this->execSetValue($this->execTrimEnd($this->getValue(), $charList));
        return $this;
    }

    private function prepareTrimCharList($charList)
    {
        $charList[] = ' ';
        foreach ($charList as &$char) {
            if (substr($char, 0, 1) === '\\') {
                continue;
            }
            $char = preg_quote($char);
        }

        unset($char);

        return implode($charList);
    }

    private function execTrim($string, $charList)
    {
        $charList = $this->prepareTrimCharList($charList);
        return ltrim(rtrim($string, $charList), $charList);
    }

    private function execTrimStart($string, $charList)
    {
        $charList = $this->prepareTrimCharList($charList);
        return ltrim($string, $charList);
    }

    private function execTrimEnd($string, $charList)
    {
        $charList = $this->prepareTrimCharList($charList);
        return rtrim($string, $charList);
    }
}
