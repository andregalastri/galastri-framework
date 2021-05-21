<?php

namespace galastri\modules\validation\interfaces;

/**
 * This interface stores constants that are used by the StringValidation class.
 */
interface StringConstants
{
    const LOWER_ACCENTED_CHARS = 'àáâãäåçèéêëìíîïñòóôõöùúûüýÿŕ';
    const UPPER_ACCENTED_CHARS = 'ÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝŸŔ';
    const LOWER_EXTENDED_ACCENTED_CHARS = 'ẃṕśǵḱĺźǘńḿẁỳǜǹẽỹũĩṽŵŷŝĝĥĵẑẅẗḧẍæœ';
    const UPPER_EXTENDED_ACCENTED_CHARS = 'ẂṔŚǴḰĹŹǗŃḾẀỲǛǸẼỸŨĨṼŴŶŜĜĤĴẐẄT̈ḦẌÆŒ';
    const SPECIAL_CHARS = "¹²³£¢¬º\\\\\/\-,.!@#$%\"'&*()_°ª+=\[\]{}^~`?<>:;";

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
