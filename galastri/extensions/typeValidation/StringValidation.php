<?php

namespace galastri\extensions\typeValidation;

use galastri\extensions\Exception;

/**
 * This validation class has methods that allows to check if the informed data has certain
 * characters, or force the data to have some of them. It also strict the length of the data, and
 * many other verifications.
 */
final class StringValidation implements \Language
{
    /**
     * Importing traits to the class.
     */
    use Common;

    /**
     * Constants that store special and accented chars. This is used by the charset method to allow
     * a pre defined group os chars that is categorized as accented or special chars.
     */
    const LOWER_ACCENTED_CHARS = 'àáâãäåçèéêëìíîïñòóôõöùúûüýÿŕ';
    const UPPER_ACCENTED_CHARS = 'ÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝŸŔ';
    const LOWER_EXTENDED_ACCENTED_CHARS = 'ẃṕśǵḱĺźǘńḿẁỳǜǹẽỹũĩṽŵŷŝĝĥĵẑẅẗḧẍæœ';
    const UPPER_EXTENDED_ACCENTED_CHARS = 'ẂṔŚǴḰĹŹǗŃḾẀỲǛǸẼỸŨĨṼŴŶŜĜĤĴẐẄT̈ḦẌÆŒ';
    const SPECIAL_CHARS = "¹²³£¢¬º\\\\\/\-,.!@#$%\"'&*()_°ª+=\[\]{}^~`?<>:;";

    /**
     * This constant stores flags and their values. Flags are pre defined groups of chars that are
     * easy to set and understand, instead of the use of regex combinations.
     */
    const CHAR_FLAGS = [
        '--numbers' => '0-9',
        '--numbersUtf8' => '\p{Nl}',
        '--letters' => 'a-zA-Z',
        '--lettersUtf8' => '\p{L}',
        '--upperLetters' => 'A-Z',
        '--upperLettersUtf8' => '\p{Lu}',
        '--lowerLetters' => 'a-z',
        '--lowerLettersUtf8' => '\p{Ll}',
        '--specialChars' => self::SPECIAL_CHARS,
        '--accentedChars' => self::UPPER_ACCENTED_CHARS.self::LOWER_ACCENTED_CHARS,
        '--upperAccentedChars' => self::UPPER_ACCENTED_CHARS,
        '--lowerAccentedChars' => self::LOWER_ACCENTED_CHARS,
        '--extendedAccentedChars' => self::UPPER_EXTENDED_ACCENTED_CHARS.self::UPPER_EXTENDED_ACCENTED_CHARS,
        '--upperExtendedAccentedChars' => self::UPPER_EXTENDED_ACCENTED_CHARS,
        '--lowerExtendedAccentedChars' => self::UPPER_EXTENDED_ACCENTED_CHARS,
        '--spaces' => '\s',
    ];
    
    /**
     * This method adds a chain link with a function that checks if the data has only lowercase
     * chars. If there are other chars instead of lowercase, an exception is thrown.
     *
     * @return void
     */
    public function lowerCase(): void
    {
        $this->chain[] = function () {
            self::validateCase('/[^\p{Ll}]/u');
        };
    }
    
    /**
     * This method adds a chain link with a function that checks if the data has only uppercase
     * chars. If there are other chars instead of uppercase, an exception is thrown.
     *
     * @return void
     */
    public function upperCase(): void
    {
        $this->chain[] = function () {
            self::validateCase('/[^\p{Lu}]/u');
        };
    }
    
    
    /**
     * This method adds a chain link with a function that checks if the data has more length than
     * the number informed. If there is, an exception is thrown.
     *
     * @param  int $length                          The max length of the data.
     * 
     * @return void
     */
    public function maxLength(int $length): void
    {
        $this->chain[] = function () use ($length){
            if (strlen($this->value) > $length) {
                $this->throwErrorMessage();
            }
        };
    }
    
    /**
     * This method adds a chain link with a function that checks if the data has less length than
     * the number informed. If there is, an exception is thrown.
     *
     * @param  int $length                          The max length of the data.
     * 
     * @return void
     */
    public function minLength(int $length): void
    {
        $this->chain[] = function () use ($length){
            if (strlen($this->value) < $length) {
                $this->throwErrorMessage();
            }
        };
    }
    
    /**
     * This method adds a chain link with a function that checks if the data has only the allowed
     * groups of chars defined.
     * 
     * It first converts possible flags to regex chars, based on the CHAR_FLAGS constant. Then, the
     * charset is tested if there are unmaches. If there are, an exception is thrown.
     *
     * @param  array $charSets                      The chars that are allowed. Any char can be
     *                                              informed here. There are also some flag names
     *                                              with pre defined groups of chars.
     *
     *                                              - Example: instead of use '0-9' to set that
     *                                              numbers are allowed, you can use '--numbers'. It
     *                                              is more readable and any change of the behaviour
     *                                              of the charset will affect all methods that use
     *                                              the flag.
     *
     * @return void
     */
    public function allowCharset(array $charSets): void
    {
        $this->chain[] = function () use ($charSets){
            if (empty($charSets)) {
                throw new Exception(self::UNDEFINED_VALIDATION_ALLOWED_CHARSET[1], self::UNDEFINED_VALIDATION_ALLOWED_CHARSET[0]);
            }

            foreach ($charSets as &$charGroup) {
                $charGroup = self::CHAR_FLAGS[$charGroup] ?? preg_quote($charGroup);
            }
            unset($charGroup);

            preg_match_all('/[^' . implode($charSets) . ']/u', $this->value, $unmatches);
    
            if (!empty($unmatches[0])) {
                $this->throwErrorMessage();
            }
        };
    }
    
    /**
     * This method adds a chain link with a function that checks if the data has some chars that it
     * is required to have. In any case, it is necessary to define how many of that charset is
     * needed to make the data valid. To set diffent quantity of various charsets, you can call this
     * method as many times it is needed.
     *
     * @param  int $minQty                          Minimun quantity of the given charsets.
     *
     * @param  array $charSets                      The chars that are allowed. Any char can be
     *                                              informed here. There are also some flag names
     *                                              with pre defined groups of chars. Works the same
     *                                              way that allowCharset() method works.
     *
     * @return void
     */
    public function requiredChars(int $minQty, array $charSets)
    {
        $this->chain[] = function () use ($minQty, $charSets){
            if (empty($charSets)) {
                throw new Exception(self::UNDEFINED_VALIDATION_REQUIRED_CHARSET[1], self::UNDEFINED_VALIDATION_REQUIRED_CHARSET[0]);
            }

            foreach ($charSets as $charGroup) {
                $regex = self::CHAR_FLAGS[$charGroup] ?? preg_quote($charGroup);
    
                preg_match_all('/[' . $regex . ']/u', $this->value, $matches);
    
                if (count($matches[0]) < $minQty) {
                    $this->throwErrorMessage();
                }
            }
        };
    }
    
    /**
     * This is an internal method, used by the lowerCase() and upperCase() methods.
     * 
     * If first filters the value extracting all letters. Then, it matches all the upper or lower
     * case chars, defined by the $casedUtf8CharsRegex parameter. If there are unmaches, then an
     * exceptions is thrown.
     *
     * @param  mixed $casedUtf8CharsRegex           The regex string with the commands that sets the
     *                                              case it will look for.
     * 
     * @return void
     */
    private function validateCase($casedUtf8CharsRegex)
    {
        preg_match_all('/[\p{L}]/u', $this->value, $allLetters);
        preg_match_all($casedUtf8CharsRegex, implode($allLetters[0]), $unmatch);

        if (!empty($unmatch[0])) {
            $this->throwErrorMessage();
        }
    }
}
