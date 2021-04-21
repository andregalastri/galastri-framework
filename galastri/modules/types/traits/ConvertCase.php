<?php

namespace galastri\modules\types\traits;

/**
 * This trait has the methods related to case conversion of strings.
 */
trait ConvertCase
{
    /**
     * Converts all chars of the current value to upper case.
     * 
     * Example:
     * My String   ->   MY STRING
     *
     * @return self
     */
    public function setUpperCase()
    {
        $this->execSetValue(mb_convert_case($this->value, MB_CASE_UPPER, 'UTF-8'));
        return $this;
    }

    /**
     * Converts all chars of the current value to lower case.
     *
     * Example:
     * My String   ->   my string
     * 
     * @return self
     */
    public function setLowerCase()
    {
        $this->execSetValue(mb_convert_case($this->value, MB_CASE_LOWER, 'UTF-8'));
        return $this;
    }

    /**
     * Converts all chars of the current value to camel case.
     *
     * Example:
     * My String   ->   myString
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
     * Converts the current value to pascal case.
     *
     * Example:
     * My String   ->   MyString
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
     * Converts the current value to constant case.
     *
     * Example:
     * My String   ->   MY_STRING
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
     * Converts the current value to snake case.
     *
     * Example:
     * My String   ->   my_string
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
     * Converts the current value to param case.
     *
     * Example:
     * My String   ->   my-string
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
     * Converts the current value to dot case.
     *
     * Example:
     * My String   ->   my.string
     * 
     * @return self
     */
    public function setDotCase()
    {
        $string = $this->execFilterProgrammingCase($this->value, 'DOT_CASE');
        
        $this->execSetValue($string);
        return $this;
    }

    /**
     * This method converts a string to a special cases, usually used in programming. The
     * explanation of how this method works was done inside the method, for better understanding.
     *
     * @param null|string $string                   The string that will be converted.
     *
     * @param string $type                          The type of the conversion. Can be:
     *
     *                                              - CAMEL_CASE    : converts to camelCase
     *                                              - PASCAL_CASE   : converts to PascalCase
     *                                              - CONSTANT_CASE : converts to CONSTANT_CASE
     *                                              - SNAKE_CASE    : converts to snake_case
     *                                              - PARAM_CASE    : converts to param-case
     *                                              - DOT_CASE      : converts to dot.case
     *
     * @return string
     */
    private function execFilterProgrammingCase(?string $string, string $type): string
    {
        /**
         * Internal closure function that checks if there are two or more upper case chars together.
         * When this occurs, the first char is kept in upper case, but the others will be converted
         * to lower case.
         * 
         * It is stored in a closure because it is used multiple times.
         */
        $funcConvertUpperGroups = function ($string) {
            return preg_replace_callback('/([A-Z]{2,})/', function($match){
                return mb_convert_case($match[0], MB_CASE_TITLE, 'UTF-8');
            }, $string);
        };

        /**
         * The first action is to get any alphanumeric char whose position it right after a
         * non-alphanumeric char and convert it to upper case.
         * 
         * Example: THis! .sTring  ->  THis! .STring
         */
        $string = preg_replace_callback('/([^a-zA-Z0-9][a-zA-Z0-9]+?)/', function($match){
            return mb_convert_case($match[0], MB_CASE_TITLE, 'UTF-8');
        }, $string);

        /**
         * The internal closure function is called. Its execution will convert all upper case chars
         * into title case:
         * 
         * Example: THis! .STring  ->  This! .String
         */
        $string = $funcConvertUpperGroups($string);

        /**
         * Next, this remove every non-alphanumeric char from the string. Following the example
         * above, the result is:
         *
         * Example: This! .String  ->  ThisString
         */
        $string = preg_replace('/([^a-zA-Z0-9])/', '', $string);

        /**
         * After this filters, the case itself will be set, based on the $type parameter:
         */
        switch ($type) {
            /**
             * When CAMEL_CASE, it gets the first char of the string and converts to lower case and
             * return the result.
             *
             * Example: ThisString  ->  thisString
             */
            case 'CAMEL_CASE':
                $string[0] = mb_convert_case($string[0], MB_CASE_LOWER, 'UTF-8');
                return $funcConvertUpperGroups($string);

            /**
             * When PASCAL_CASE, it just gets string filtered until now and return (it is already in
             * pascal case).
             * 
             * Example: ThisString  ->  ThisString
             */
            case 'PASCAL_CASE':
                return $string;

            /**
             * When CONSTANT_CASE, it gets every upper case in the filtered string and adds a
             * underscore _ in front of it. Next, it trims the underscore from the left and converts
             * all the chars into upper case and return the result.
             *
             * Example: ThisString  ->  THIS_STRING
             */
            case 'CONSTANT_CASE':
                $string = preg_replace('/([A-Z])/', '_$1', $string);
                return ltrim(mb_convert_case($string, MB_CASE_UPPER, 'UTF-8'), '_');

            /**
             * When SNAKE_CASE, it gets every upper case in the filtered string and adds a
             * underscore _ in front of it. Next, it trims the underscore from the left and converts
             * all the chars into lower case and return the result.
             *
             * Example: ThisString  ->  this_string
             */
            case 'SNAKE_CASE':
                $string = preg_replace('/([A-Z])/', '_$1', $string);
                return ltrim(mb_convert_case($string, MB_CASE_LOWER, 'UTF-8'), '_');
    
            /**
             * When PARAM_CASE, it gets every upper case in the filtered string and adds a dash - in
             * front of it. Next, it trims the dash from the left and converts all the chars into
             * lower case and return the result.
             *
             * Example: ThisString  ->  this-string
             */
            case 'PARAM_CASE':
                $string = preg_replace('/([A-Z])/', '-$1', $string);
                return ltrim(mb_convert_case($string, MB_CASE_LOWER, 'UTF-8'), '-');
    
            /**
             * When DOT_CASE, it gets every upper case in the filtered string and adds a dot . in
             * front of it. Next, it trims the dot from the left and converts all the chars into
             * lower case and return the result.
             *
             * Example: ThisString  ->  this.string
             */
            case 'DOT_CASE':
                $string = preg_replace('/([A-Z])/', '.$1', $string);
                return ltrim(mb_convert_case($string, MB_CASE_LOWER, 'UTF-8'), '.');
        }
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
