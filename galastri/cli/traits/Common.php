<?php

namespace galastri\cli\traits;

trait Common
{    
    /**
     * getVersion
     *
     * @param  mixed $program
     * @return void
     */
    private static function getVersion(?string $program = null)// : array|string
    {
        $versions = require(self::VERSION_FILE);

        return $versions[$program] ?? $versions;
    }
    
    /**
     * invalidOption
     *
     * @param  mixed $option
     * @return void
     */
    private static function invalidOption($option)
    {
        self::drawMessageBox(
            self::message('INVALID_OPTION', 0).
            $option.
            self::message('INVALID_OPTION', 1)
        );
        self::pressEnterToContinue();
    }
    
    /**
     * invalidValue
     *
     * @param  mixed $value
     * @return void
     */
    private static function invalidValue($value)
    {
        self::drawMessageBox(
            self::message('INVALID_VALUE', 0).
            $value.
            self::message('INVALID_VALUE', 1)
        );
        self::pressEnterToContinue();
    }
    
    
    /**
     * exit
     *
     * @return void
     */
    private static function exit()
    {
        echo "\n\n\n";
        self::drawMessageBox(self::message('EXIT', 0), self::message('EXIT', 1));
        self::wait(1, false);
        exit();
    }
    
    /**
     * chooseValue
     *
     * @param  mixed $drawWindow
     * @return void
     */
    private static function chooseValue($drawWindow)
    {
        self::$drawWindow();
        return readline(self::message('INFORM_VALUE', 0));
    }
    
    /**
     * chooseYesOrNo
     *
     * @param  mixed $drawWindow
     * @return void
     */
    private static function chooseYesOrNo($drawWindow)
    {
        self::$drawWindow();
        switch (readline(self::message('CHOOSE_YES_OR_NO', 0))) {
            case 'y':
            case 'Y':
            case 's':
            case 'S':
                return true;
        }
        return false;
    }
    
    /**
     * chooseYesOrNo
     *
     * @param  mixed $drawWindow
     * @return void
     */
    private static function chooseNoOrYes($drawWindow)
    {
        self::$drawWindow();
        switch (readline(self::message('CHOOSE_NO_OR_YES', 0))) {
            case 'y':
            case 'Y':
            case 's':
            case 'S':
                return true;
        }
        return false;
    }
    
    /**
     * chooseAnOption
     *
     * @param  mixed $drawWindow
     * @return void
     */
    private static function chooseAnOption($drawWindow)
    {
        self::$drawWindow();
        return readline(self::message('CHOOSE_AN_OPTION', 0));
    }
    
    /**
     * done
     *
     * @param  mixed $drawWindow
     * @return void
     */
    private static function done($drawWindow)
    {
        self::$drawWindow();
        self::pressEnterToContinue();
        self::mainWindow();
    }

    private static function executionMessage($message)
    {
        self::drawMessageBox($message);
        self::wait(1, false, false);
    }

    /**
     * Source: https://gist.github.com/lavoiesl/4217733
     * Author: Sébastien Lavoie
     * 
     * Equivalent of array_merge, but overwrites an existing key instead of merging.
     *
     * @param $original array Array to be overwritten
     * @param $overwrite array Array to merge into $original
     */
    private static function arrayOverwrite(&$original, $overwrite)
    {
        // Not included in function signature so we can return silently if not an array
        if (!is_array($overwrite)) {
            return;
        }
        if (!is_array($original)) {
            $original = $overwrite;
        }

        foreach($overwrite as $key => $value) {
            if (array_key_exists($key, $original) && is_array($value)) {
                self::arrayOverwrite($original[$key], $overwrite[$key]);
            } else {
                $original[$key] = $value;
            }
        }
    }
}
