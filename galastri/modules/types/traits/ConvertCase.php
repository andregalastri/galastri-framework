<?php

namespace galastri\modules\types\traits;

trait ConvertCase
{
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
