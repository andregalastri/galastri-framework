<?php

namespace galastri\extensions\typeValidation\interfaces;

/**
 * This interface stores the various messages in English language. It is dynamically implemented in
 * many classes based on the debug configuration 'language' parameter.
 */
interface StringConstants
{
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
}
