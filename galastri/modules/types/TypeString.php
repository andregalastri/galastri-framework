<?php

namespace galastri\modules\types;

use galastri\extensions\Exception;
use galastri\extensions\types\StringValidation;
use galastri\extensions\types\EmptyValidation;

final class TypeString implements \Language
{
    private bool $initialized = false;
    private StringValidation $stringValidation;
    private EmptyValidation $emptyValidation;

    private ?string $value = null;
    private ?string $initialValue = null;

    private string $concatSpacer = '';
    private ?string $errorMessage = null;

    private bool   $storeHistory;
    private array  $history = [];

    /**
     * __construct
     *
     * @param null|string $value
     * @param bool $storeHistory
     */
    public function __construct($value = null, $storeHistory = false)
    {
        $this->storeHistory = $storeHistory;
        $this->stringValidation = new StringValidation();
        $this->emptyValidation = new EmptyValidation();

        $this->execSetValue($value);
    }



    /**********************************************
     * Initialize and setters and getters
     **********************************************/

    /**
     * value
     *
     * @param bool $value
     * @return self|string
     */
    public function setValue($value)
    {
        $this->execSetValue($value);
        return $this;
    }

    /**
     * value
     *
     * @param bool $value
     * @return self|string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Undocumented function
     *
     * @param [type] $length
     * @return void
     */
    public function setRandomValue($length = 15)
    {
        if (function_exists("random_bytes")) {
            $bytes = random_bytes(ceil($length / 2));
        } elseif (function_exists("openssl_random_pseudo_bytes")) {
            $bytes = openssl_random_pseudo_bytes(ceil($length / 2));
        } else {
            throw new Exception("No cryptographically secure random function available");
        }

        $this->execSetValue(bin2hex($bytes));
        return $this;
    }

    /**
     * initialValue
     *
     * @return null|string
     */
    public function getInitialValue()
    {
        $this->checkValue($this->value);

        return $this->initialValue;
    }

    /**
     * resetValue
     *
     * @return self
     */
    public function resetValue()
    {
        $this->execSetValue($this->initialValue);
        return $this;
    }



    /**********************************************
     * Information
     **********************************************/

    /**
     * length
     *
     * @return int
     */
    public function getLength()
    {
        $this->checkValue($this->value);

        return strlen($this->value);
    }

    /**
     * Undocumented function
     *
     * @param [type] $start
     * @param [type] $length
     * @return void
     */
    public function setSubstring($start, $length = null)
    {

        if ($length === null) {
            $this->execSetValue(substr($this->value, $start));
        } else {
            $this->execSetValue(substr($this->value, $start, $length));
        }

        return $this;
    }

    /**
     * Undocumented function
     *
     * @param [type] $start
     * @param [type] $length
     * @return void
     */
    public function getSubstring($start, $length = null)
    {
        $this->checkValue($this->value);

        if ($length === null) {
            return substr($this->value, $start);
        } else {
            return substr($this->value, $start, $length);
        }
    }




    /**********************************************
     * Conversion
     **********************************************/

    /**
     * split
     *
     * @return array
     */
    public function split($delimiter, $limit = PHP_INT_MAX)
    {
        $this->checkValue($this->value);

        return explode($delimiter, $this->value, $limit);
    }

    /**
     * toInt
     *
     * @return array
     */
    public function getInt()
    {
        $this->checkValue($this->value);

        return (int)$this->value;
    }

    /**
     * toFloat
     *
     * @return array
     */
    public function getFloat()
    {
        $this->checkValue($this->value);

        return (float)$this->value;
    }

    /**********************************************
     * Validation
     **********************************************/
    
    public function lowerCase(...$charSet)
    {
        $this->stringValidation
            ->lowerCase($charSet);
        return $this;
    }

    public function upperCase(...$charSet)
    {
        $this->stringValidation
            ->upperCase($charSet);

        return $this;
    }

    public function requiredChars($qty, ...$charSet)
    {
        $this->stringValidation
            ->requiredChars($qty, $charSet);

        return $this;
    }

    public function allowCharset(...$charSet)
    {
        $this->stringValidation
            ->allowCharset($charSet);

        return $this;
    }

    public function minLength($length)
    {
        $this->stringValidation
            ->minLength($length);

        return $this;
    }

    public function maxLength($length)
    {
        $this->stringValidation
            ->maxLength($length);

        return $this;
    }

    public function denyNull()
    {
        $this->emptyValidation
            ->denyNull();

        return $this;
    }

    public function denyEmpty()
    {
        $this->emptyValidation
            ->denyEmpty();

        return $this;
    }

    public function onError($message)
    {
        $this->errorMessage = $message;

        $this->stringValidation->onError($message);
        $this->emptyValidation->onError($message);

        return $this;
    }

    public function validate($value = null)
    {
        $this->stringValidation->setValue($value ?? $this->value)->validate();
        $this->emptyValidation->setValue($value ?? $this->value)->validate();

        return $this;
    }



    /**********************************************
     * Concatenation
     **********************************************/

    /**
     * Undocumented function
     *
     * @param [type] ...$values
     * @return void
     */
    public function concat(...$values)
    {
        $spacer = $this->isEmpty() ? '' : $this->concatSpacer;
        $concat = $spacer . implode($this->concatSpacer, $values);
        $this->concatSpacer = '';

        $this->execSetValue($this->value . $concat);

        
        return $this;
    }

    /**
     * Undocumented function
     *
     * @param [type] ...$values
     * @return void
     */
    public function concatAtStart(...$values)
    {
        $spacer = $this->isEmpty() ? '' : $this->concatSpacer;
        $concat = implode($this->concatSpacer, $values) . $spacer;
        $this->concatSpacer = '';

        $this->execSetValue($concat . $this->value);

        return $this;
    }

    /**
     * Undocumented function
     *
     * @param [type] $value
     * @return void
     */
    public function concatSpacer($value)
    {
        $this->checkValue($this->value);

        $this->concatSpacer = $value;
        
        return $this;
    }



    /**********************************************
     * Case converters
     **********************************************/

    /**
     * toUpperCase
     *
     * @return self
     */
    public function setUpperCase()
    {
        $this->execSetValue(mb_convert_case($this->value, MB_CASE_UPPER, 'UTF-8'));
        return $this;
    }

    /**
     * toLowerCase
     *
     * @return self
     */
    public function setLowerCase()
    {
        $this->execSetValue(mb_convert_case($this->value, MB_CASE_LOWER, 'UTF-8'));
        return $this;
    }

    /**
     * toCamelCase
     * 
     * @return self
     */
    public function setCamelCase()
    {
        $string = $this->execFilterProgrammingCase($this->value, 'CAMEL_CASE');

        $this->execSetValue($string);
        
        return $this;
    }

    /**
     * toPascalCase
     *
     * @return self
     */
    public function setPascalCase()
    {
        $string = $this->execFilterProgrammingCase($this->value, 'PASCAL_CASE');
        
        $this->execSetValue($string);
        return $this;
    }
    
    /**
     * toConstantCase
     *
     * @return self
     */
    public function setConstantCase()
    {
        $string = $this->execFilterProgrammingCase($this->value, 'CONSTANT_CASE');

        $this->execSetValue($string);
        return $this;
    }

    /**
     * toSnakeCase
     *
     * @return self
     */
    public function setSnakeCase()
    {
        $string = $this->execFilterProgrammingCase($this->value, 'SNAKE_CASE');

        $this->execSetValue($string);
        return $this;
    }

    /**
     * toParamCase
     *
     * @return self
     */
    public function setParamCase()
    {
        $string = $this->execFilterProgrammingCase($this->value, 'PARAM_CASE');
        
        $this->execSetValue($string);
        return $this;
    }

    /**
     * toDotCase
     *
     * @return self
     */
    public function setDotCase()
    {
        $string = $this->execFilterProgrammingCase($this->value, 'DOT_CASE');
        
        $this->execSetValue($string);
        return $this;
    }



    /**********************************************
     * Filter edges
     **********************************************/

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



    /**********************************************
     * String manipulation
     **********************************************/



    public function setReplace($search, $replace)
    {
        $this->execSetValue(str_replace($search, $replace, $this->value));

        return $this;
    }



    /**********************************************
     * History and state
     **********************************************/

    /**
     * history
     *
     * @param int|null $key
     * @return array|string
     */
    public function getHistory($key = null)
    {
        $this->checkValue($this->value);

        if ($this->storeHistory) {
            if ($key) {
                if (isset($this->history[$key])) {
                    return $this->history[$key];
                } else {
                    throw new Exception('Key not found in stored history');
                }
            }
        }

        return $this->history;
    }

    /**
     * revertToHistory
     *
     * @param int $key
     * @return void
     */
    public function revertToHistory($key)
    {
        if ($this->storeHistory) {
            if (isset($this->history[$key])) {
                $this->execSetValue($this->history[$key]);
            } else {
                throw new Exception('Key not found in stored history');
            }
        } else {
            throw new Exception('Stored history is not active');
        }

        return $this;
    }


    
    /**********************************************
     * Internal executions
     **********************************************/

    /**
     * execSetValue
     *
     * @param null|string $value
     * @param bool $allowNull
     * @return void
     */
    private function execSetValue($value)
    {
        $this->checkValue($value);

        $this->value = $value;
        $this->execSaveHistory($value);
    }

    /**
     * execSaveHistory
     *
     * @param string $value
     * @return void
     */
    private function execSaveHistory($value)
    {
        if ($this->storeHistory) {
            $this->history[] = $value;
        }
    }
    
    /**
     * Undocumented function
     *
     * @param [type] $string
     * @param [type] $type
     * @return void
     */
    private function execFilterProgrammingCase($string, $type)
    {
        $string = preg_replace_callback('/([A-Z]{2,})/', function($match){
            return mb_convert_case($match[0], MB_CASE_TITLE, 'UTF-8');
        }, $string);

        $string = preg_replace_callback('/([^a-zA-Z0-9][a-zA-Z0-9]+?)/', function($match){
            return mb_convert_case($match[0], MB_CASE_TITLE, 'UTF-8');
        }, $string);

        $string = preg_replace('/([^a-zA-Z0-9])/', '', $string);

        switch ($type) {
            case 'CAMEL_CASE':
            break;

            case 'PASCAL_CASE':
                $string[0] = mb_convert_case($string[0], MB_CASE_UPPER, 'UTF-8');
            break;

            case 'CONSTANT_CASE':
                $string = preg_replace('/([A-Z])/', '_$1', $string);
                $string = mb_convert_case($string, MB_CASE_UPPER, 'UTF-8');
            break;

            case 'SNAKE_CASE':
                $string = preg_replace('/([A-Z])/', '_$1', $string);
                $string = mb_convert_case($string, MB_CASE_LOWER, 'UTF-8');
            break;
    
            case 'PARAM_CASE':
                $string = preg_replace('/([A-Z])/', '-$1', $string);
                $string = mb_convert_case($string, MB_CASE_LOWER, 'UTF-8');
            break;
    
            case 'DOT_CASE':
                $string = preg_replace('/([A-Z])/', '.$1', $string);
                $string = mb_convert_case($string, MB_CASE_LOWER, 'UTF-8');
            break;
        }

        return $string;
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



    /**********************************************
     * Internal verifications
     **********************************************/
    
    /**
     * checkValue
     *
     * @return void
     */
    private function checkValue($value)
    {
        if (gettype($value) === 'NULL' or gettype($value) === 'string') {
            $this->validate($value);
            return true;
        }

        throw new Exception(
            $this->errorMessage ?? self::VALIDATION_DEFAULT_INVALID_MESSAGE[1],
            self::VALIDATION_DEFAULT_INVALID_MESSAGE[0],
            [var_export($value, true)]
        );
    }

    /**
     * Undocumented function
     *
     * @return boolean
     */
    private function isEmpty()
    {
        $this->checkValue($this->value);
        return empty($this->value);
    }



    /**********************************************
     * Debug
     **********************************************/

    /**
     * varDump
     *
     * @return void
     */
    public function varDump()
    {
        echo '<pre>';
        var_dump($this->value);
        echo '</pre>';
    }



    // private function _capitalize($string, bool $asArticle = false, bool $keepUpperChars = false)
    // {
    //     if ($asArticle) {
    //         $string = preg_split('/(\.|\!|\?)/', $string, -1, PREG_SPLIT_DELIM_CAPTURE);
    //         $string = array_map('trim', $string);
    //     } else {
    //         $string = [$string];
    //     }

    //     /**
    //      * An internal method to capitalize only the first letter of the string.
    //      */
    //     $upperCaseFirst = function (string $string, bool $lowerStringEnd = false, string $encoding = 'UTF-8'): string {
    //         $firstLetter = mb_strtoupper(mb_substr($string, 0, 1, $encoding), $encoding);
    //         $stringingEnd = '';
    //         if ($lowerStringEnd) {
    //             $stringingEnd = mb_strtolower(mb_substr($string, 1, mb_strlen($string, $encoding), $encoding), $encoding);
    //         } else {
    //             $stringingEnd = mb_substr($string, 1, mb_strlen($string, $encoding), $encoding);
    //         }
    //         $string = $firstLetter . $stringingEnd;
    //         return $string;
    //     };

    //     foreach ($string as $key => $value) {
    //         $value = $asArticle ? $upperCaseFirst($value, !$keepUpperChars) : mb_convert_case($value, MB_CASE_TITLE, 'UTF-8');

    //         preg_match('/(\.|\!|\?)/', $value, $match);
    //         if (!array_key_exists(1, $match)) {
    //             $match[1] = false;
    //         }

    //         $space = $value != $match[1] ? ' ' : '';
    //         $string[$key] = $space . $value;
    //     }

    //     return trim(implode('', $string));
    // }
}
