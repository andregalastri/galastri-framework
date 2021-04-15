<?php

namespace galastri\extensions\types;

use galastri\extensions\Exception;

final class StringValidation implements \Language
{
    use Common;

    const LOWER_ACCENTED_CHARS = 'àáâãäåçèéêëìíîïñòóôõöùúûüýÿŕ';
    const UPPER_ACCENTED_CHARS = 'ÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝŸŔ';
    const LOWER_EXTENDED_ACCENTED_CHARS = 'ẃṕśǵḱĺźǘńḿẁỳǜǹẽỹũĩṽŵŷŝĝĥĵẑẅẗḧẍæœ';
    const UPPER_EXTENDED_ACCENTED_CHARS = 'ẂṔŚǴḰĹŹǗŃḾẀỲǛǸẼỸŨĨṼŴŶŜĜĤĴẐẄT̈ḦẌÆŒ';
    const SPECIAL_CHARS = "¹²³£¢¬º\\\\\/\-,.!@#$%\"'&*()_°ª+=\[\]{}^~`?<>:;";

    const CHAR_FLAGS = [
        'Numbers' => '0-9',
        'NumbersUtf8' => '\p{Nl}',
        'Letters' => 'a-zA-Z',
        'LettersUtf8' => '\p{L}',
        'UpperLetters' => 'A-Z',
        'UpperLettersUtf8' => '\p{Lu}',
        'LowerLetters' => 'a-z',
        'LowerLettersUtf8' => '\p{Ll}',
        'SpecialChars' => self::SPECIAL_CHARS,
        'AccentedChars' => self::UPPER_ACCENTED_CHARS.self::LOWER_ACCENTED_CHARS,
        'UpperAccentedChars' => self::UPPER_ACCENTED_CHARS,
        'LowerAccentedChars' => self::LOWER_ACCENTED_CHARS,
        'ExtendedAccentedChars' => self::UPPER_EXTENDED_ACCENTED_CHARS.self::UPPER_EXTENDED_ACCENTED_CHARS,
        'UpperExtendedAccentedChars' => self::UPPER_EXTENDED_ACCENTED_CHARS,
        'LowerExtendedAccentedChars' => self::UPPER_EXTENDED_ACCENTED_CHARS,
        'Spaces' => '\s',
    ];

    public function lowerCase()
    {
        $this->chain[] = function () {
            self::validateCase('/[^\p{Ll}]/u');
        };
    }

    public function upperCase()
    {
        $this->chain[] = function () {
            self::validateCase('/[^\p{Lu}]/u');
        };
    }

    public function maxLength($length)
    {
        $this->chain[] = function () use ($length){
            if (strlen($this->value) > $length) {
                $this->throwErrorMessage();
            }
        };
    }

    public function minLength($length)
    {
        $this->chain[] = function () use ($length){
            if (strlen($this->value) < $length) {
                $this->throwErrorMessage();
            }
        };
    }

    public function allowCharset($charGroups)
    {
        $this->chain[] = function () use ($charGroups){
            if (empty($charGroups)) {
                throw new Exception('No group chars informed');
            }

            foreach ($charGroups as &$charGroup) {
                $charGroup = self::CHAR_FLAGS[$charGroup] ?? preg_quote($charGroup);
            }
            unset($charGroup);

            preg_match_all('/[^' . implode($charGroups) . ']/u', $this->value, $unmatches);
    
            if (!empty($unmatches[0])) {
                $this->throwErrorMessage();
            }
        };
    }

    public function requiredChars($minQty, $charGroups)
    {
        $this->chain[] = function () use ($minQty, $charGroups){
            if (empty($charGroups)) {
                throw new Exception('No group chars informed');
            }

            foreach ($charGroups as $charGroup) {
                $regex = self::CHAR_FLAGS[$charGroup] ?? preg_quote($charGroup);
    
                preg_match_all('/[' . $regex . ']/u', $this->value, $matches);
    
                if (count($matches[0]) < $minQty) {
                    $this->throwErrorMessage();
                }
            }
        };
    }

    private function validateCase($casedUtf8CharsRegex)
    {
        preg_match_all('/[\p{L}]/u', $this->value, $allLetters);
        preg_match_all($casedUtf8CharsRegex, implode($allLetters[0]), $unmatch);

        if (!empty($unmatch[0])) {
                $this->throwErrorMessage();
        }
    }
}
