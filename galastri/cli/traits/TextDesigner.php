<?php

namespace galastri\cli\traits;

use galastri\cli\classes\Language;

trait TextDesigner 
{
    /**
     * pressEnterToContinue
     *
     * @return void
     */
    private static function pressEnterToContinue(): void
    {
        readline(self::message('PRESS_ENTER_TO_CONTINUE', 0));
        echo "\n\n\n";
    }

    /**
     * wait
     *
     * @param  mixed $seconds
     * @param  mixed $writeDots
     * @param  mixed $longJump
     * @return void
     */
    private static function wait(int $seconds, bool $writeDots = true, bool $longJump = true): void
    {
        for ($i = 0; $i < $seconds; $i++) {
            echo $writeDots ? '.' : '';
            sleep(1);
        }
        echo $longJump ? "\n\n\n" : '';
    }
    
    /**
     * drawMessageBox
     *
     * @param  mixed $messages
     * @return void
     */
    private static function drawMessageBox(string ...$messages): void
    {
        self::draw('doubled', 'top');
        foreach($messages as $message) {
            self::text('doubled', $message, 'center');
        }
        self::draw('doubled', 'bottom');
    }

    /**
     * text
     *
     * @param  mixed $style
     * @param  mixed $message
     * @param  mixed $align
     * @return void
     */
    private static function text(string $style, string $message, string $align = 'left'): void
    {
        $textMaxSize = self::LAYOUT_SIZE - 2;

        if (mb_strlen($message) <= $textMaxSize) {
            echo self::BOX_STYLES[$style][5].self::stringpad(' '.$message.' ', self::LAYOUT_SIZE, ' ', self::textAlign($align)).self::BOX_STYLES[$style][5]."\n";
        } else {
            $delimiter = ' ';

            if (strpos($message, ' ') === false) {
                $delimiter = '/';
            }

            $words = explode($delimiter, $message);
            $pharase[0] = [];
            $pharaseCount = 0;
    
            foreach($words as $word) {
                if (mb_strlen(implode($delimiter, $pharase[$pharaseCount]).$delimiter.$word) > $textMaxSize) {
                    $pharaseCount++;
                    $pharase[$pharaseCount] = [];
                }
    
                $pharase[$pharaseCount][] = $word;
            }
            foreach($pharase as $line) {
                echo self::BOX_STYLES[$style][5].' '.self::stringpad(trim(implode($delimiter, $line).$delimiter), $textMaxSize, ' ', self::textAlign($align)).' '.self::BOX_STYLES[$style][5]."\n";
            }
        }

    }
    
    /**
     * textAlign
     *
     * @param  mixed $align
     * @return int
     */
    private static function textAlign(string $align): int
    {
        switch($align){
            case 'center':
                return STR_PAD_BOTH;
            
            case 'right':
                return STR_PAD_LEFT;
            
            case 'left':
                return STR_PAD_RIGHT;
            
            default:
                return STR_PAD_LEFT;
        }
    }
    
    /**
     * draw
     *
     * @param  mixed $style
     * @param  mixed $type
     * @return void
     */
    private static function draw(string $style, string $type): void
    {
        switch($type) {
            case 'top':
                echo self::BOX_STYLES[$style][0].self::stringpad('', self::LAYOUT_SIZE, self::BOX_STYLES[$style][4], STR_PAD_LEFT).self::BOX_STYLES[$style][1]."\n";
                break;

            case 'bottom':
                echo self::BOX_STYLES[$style][2].self::stringpad('', self::LAYOUT_SIZE, self::BOX_STYLES[$style][4], STR_PAD_LEFT).self::BOX_STYLES[$style][3]."\n";
                break;

            case 'line':
                echo self::BOX_STYLES[$style][6].self::stringpad('', self::LAYOUT_SIZE, self::BOX_STYLES[$style][4], STR_PAD_LEFT).self::BOX_STYLES[$style][7]."\n";
                break;
            
            case 'empty':
                echo self::BOX_STYLES[$style][5].self::stringpad('', self::LAYOUT_SIZE, " ", STR_PAD_LEFT).self::BOX_STYLES[$style][5]."\n";
                break;
        }
    }

    /**
     * Source: https://www.php.net/manual/pt_BR/function.str-pad.php#116244
     * Author: wes
     * 
     * This function is the multibyte version of str_pad() function from PHP.
     *
     * @param  mixed $string
     * 
     * @param  mixed $padlength
     * 
     * @param  mixed $pad_str
     * 
     * @param  mixed $align
     * 
     * @param  mixed $encoding
     * 
     * @return string
     */
    private static function stringpad(string $string, int $padlength, string $padstring = ' ', int $align = STR_PAD_RIGHT, ?string $encoding = NULL): string
    {
        $encoding = $encoding === NULL ? mb_internal_encoding() : $encoding;

        $padBefore = $align === STR_PAD_BOTH || $align === STR_PAD_LEFT;
        $padAfter = $align === STR_PAD_BOTH || $align === STR_PAD_RIGHT;
        $padlength -= mb_strlen($string, $encoding);
        
        $targetLength = $padBefore && $padAfter ? $padlength / 2 : $padlength;
        $strToRepeatLength = mb_strlen($padstring, $encoding);

        $repeatTimes = ceil($targetLength / $strToRepeatLength);
        $repeatedString = str_repeat($padstring, max(0, $repeatTimes)); // safe if used with valid utf-8 strings

        $stringbefore = $padBefore ? mb_substr($repeatedString, 0, floor($targetLength), $encoding) : '';
        $stringafter = $padAfter ? mb_substr($repeatedString, 0, ceil($targetLength), $encoding) : '';
        
        return $stringbefore.$string.$stringafter;
    }
}
