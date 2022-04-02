<?php

namespace galastri\cli\traits;

use galastri\cli\classes\Language;

trait FileManager 
{
    /**
     * isWritable
     *
     * @param  mixed $fileOrDirectory
     * @return void
     */
    private static function isWritable($fileOrDirectory): void
    {
        if (!is_writable($fileOrDirectory)) {
            self::drawMessageBox(self::message('NO_WRITING_PERMISSION', 0), '', $fileOrDirectory, '', self::message('NO_WRITING_PERMISSION', 1));
            self::pressEnterToContinue();
            exit;
        }
    }

    /**
     * Source: https://stackoverflow.com/a/2050909
     * Author: Felix Kling
     * 
     * This function copy the entire source directory to a destination directory. PHP's native copy()
     * function doesn't copy folders, much less do it recursively.
     *
     * @param  string $sourceDirectory                  The directory that will be copied.
     * 
     * @param  string $destinationDirectory             The destination folder that will receive the
     *                                                  copy of the source directory.
     * 
     * @param  string $childFolder                      (Optional) Adds a child folder inside the
     *                                                  destination directory and copies the source
     *                                                  directory to this child folder.
     * 
     * @return void
     */
    private static function copyDirectory(string $sourceDirectory, string $destinationDirectory, string $childFolder = '', array $ignorePaths = []): void {
        $directory = opendir($sourceDirectory);

        if (is_dir($destinationDirectory) === false) {
            mkdir($destinationDirectory);
        }

        if ($childFolder !== '') {
            if (is_dir("$destinationDirectory/$childFolder") === false) {
                mkdir("$destinationDirectory/$childFolder");
            }

            while (($file = readdir($directory)) !== false) {
                if ($file === '.' || $file === '..') {
                    continue;
                }

                if (is_dir("$sourceDirectory/$file") === true) {
                    self::copyDirectory("$sourceDirectory/$file", "$destinationDirectory/$childFolder/$file", '', $ignorePaths);
                } else {
                    foreach ($ignorePaths as $ignore) {
                        if ($ignore == substr($sourceDirectory, 0, strlen($ignore))) {
                            return;
                        }
                    }
                    copy("$sourceDirectory/$file", "$destinationDirectory/$childFolder/$file");
                }
            }

            closedir($directory);

            return;
        }

        while (($file = readdir($directory)) !== false) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            if (is_dir("$sourceDirectory/$file") === true) {
                self::copyDirectory("$sourceDirectory/$file", "$destinationDirectory/$file", '', $ignorePaths);
            }
            else {
                foreach ($ignorePaths as $ignore) {
                    if ($ignore == substr($sourceDirectory, 0, strlen($ignore))) {
                        return;
                    }
                }
                copy("$sourceDirectory/$file", "$destinationDirectory/$file");
            }
        }

        closedir($directory);
    }
    
    /**
     * copyFile
     *
     * @param  mixed $sourceFile
     * @param  mixed $destinationFile
     * @return void
     */
    private static function copyFile($sourceFile, $destinationFile) {
        $path = pathinfo($destinationFile);

        if (!is_dir($path['dirname'])) {
            mkdir($path['dirname'], 0777, true);
        }
        
        copy($sourceFile, $destinationFile);
    }

    /**
     * Source: https://intecsols.com/delete-files-and-folders-from-a-folder-using-php-by-intecsols/
     * Author: Syed Muhammad Waqas
     * 
     * This function delete the entire directory even if it has files inside it. PHP's native rmdir()
     * function doesn't remove folders with files inside.
     *
     * @param  string $directory                              Directory that will be removed.
     * 
     * @return void
     */
    private static function deleteAll(string $directory): void
    {
        foreach(glob($directory . '/*') as $file) {
            if(is_dir($file)) {
                self::deleteAll($file);
            } else {
                unlink($file);
            }
        }

        rmdir($directory);
    }

    private static function createDirectory(string $path): void
    {
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
    }

    private static function writeFile(string $path, string $value, string $type = 'w'): void
    {
        $fopen = fopen($path, $type);
        fwrite($fopen, $value);
        fclose($fopen);
    }

    private static function createFile(string $path, string $value = '', string $type = 'w'): void
    {
        if (!file_exists($path)) {
            self::writeFile($path, $value, $type);
        }
    }
}
