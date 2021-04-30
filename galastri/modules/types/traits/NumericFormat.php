<?php

namespace galastri\modules\types\traits;

/**
 * This trait has the methods related to formatting a number.
 */
trait NumericFormat
{
    /**
     * Stores the number of decimal places of the number when formatting.
     *
     * @var int
     */
    private int $decimals = 2;

    /**
     * Stores the char that is used as decimal separator.
     *
     * @var string
     */
    private string $decimalSeparator = '.';

    /**
     * Stores the char that is used as thousand separator.
     *
     * @var string
     */
    private string $thousandSeparator = ',';

    /**
     * This method configures the default behavior of the object when the getNumberFormat() method
     * is called. Once configured, it will always use the same configuration. This method do not
     * return the formatted value, it just configures it.
     *
     * @param  mixed $decimals                      Number of decimal places.
     *
     * @param  mixed $decimalSeparator              Char that is used as decimal separator.
     *
     * @param  mixed $thousandSeparator             Char that is used as thousand separator.
     *
     * @return self
     */
    public function formatThousands(int $decimals, string $decimalSeparator = '.', string $thousandSeparator = ','): self
    {
        $this->decimals = $decimals;
        $this->decimalSeparator = $decimalSeparator;
        $this->thousandSeparator = $thousandSeparator;

        $this->execHandleValue(number_format($this->getValue(), $this->decimals, $this->decimalSeparator, $this->thousandSeparator));

        return $this;
    }

    /**
     * Author: xelozz@gmail.com
     * https://www.php.net/manual/pt_BR/function.memory-get-usage.php#96280
     *
     * Converts a number of bytes in formatted string showing its value in kb, mb, etc.
     *
     * @return string
     */
    public function formatBytes(): self
    {
        $bytes = $this->getValue();

        $unit = ['b', 'kb', 'mb', 'gb', 'tb', 'pb'];

        $this->execHandleValue(@round($bytes / pow(1024, ($i = floor(log($bytes, 1024)))), 2) . ' ' . $unit[$i]);

        return $this;
    }
}
