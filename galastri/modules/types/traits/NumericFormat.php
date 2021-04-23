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
    public function numberFormatConfig(int $decimals, string $decimalSeparator = '.', string $thousandSeparator = ','): self
    {
        $this->decimals = $decimals;
        $this->decimalSeparator = $decimalSeparator;
        $this->thousandSeparator = $thousandSeparator;

        return $this;
    }
    
    /**
     * This method format the number based on the configuration and return an formatted string. It
     * can, also return the formatted number within a string, using the ## as flag to replace the
     * number.
     *
     * @param  string $textReplace                  A string that can be placed with the returning
     *                                              number. When a ## is used, it is replaced by the
     *                                              formatted number and any other char placed
     *                                              together will be place as part of the returning
     *                                              string.
     *
     *                                              Examples
     *
     *                                                  $myInt->setNumber(123)->getFormattedNumber()
     *                                                  - Result: '123,00'
     *
     *                                                  $myInt->setNumber(123)->getFormattedNumber('Price: US$ ## (un.)')
     *                                                  - Result: 'Price: US$ 123,00 (un.)'
     *
     * @return string
     */
    public function getFormattedValue(string $textReplace = '##'): string
    {
        $numberFormat = number_format($this->value, $this->decimals, $this->decimalSeparator, $this->thousandSeparator);
        
        return preg_replace('/##/', $numberFormat, $textReplace, 1);
    }
}
