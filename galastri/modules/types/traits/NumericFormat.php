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
     * This method converts the current value into the configured format. It will always use the
     * same configuration each time it is configured.
     *
     * @param  mixed $decimals                      Number of decimal places.
     *
     * @param  mixed $decimalSeparator              Char that is used as decimal separator.
     *
     * @param  mixed $thousandSeparator             Char that is used as thousand separator.
     *
     * @return self
     */
    public function formatThousands(int $decimals = 2, string $decimalSeparator = '.', string $thousandSeparator = ','): self
    {
        $this->decimals = $decimals;
        $this->decimalSeparator = $decimalSeparator;
        $this->thousandSeparator = $thousandSeparator;

        $this->execHandleValue(number_format($this->getValue(), $this->decimals, $this->decimalSeparator, $this->thousandSeparator));

        return $this;
    }

    /**
     * Based on:
     * https://www.php.net/manual/pt_BR/function.memory-get-usage.php#96280
     * Author: xelozz@gmail.com
     *
     * This method converts a number of bytes in formatted string showing its value in kb, mb, etc.
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
