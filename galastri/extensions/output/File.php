<?php

namespace galastri\extensions\output;

use \galastri\core\Route;
use \galastri\core\Parameters;
use \galastri\core\Debug;
use \galastri\modules\types\TypeString;
use \galastri\extensions\Exception;

/**
 * This is the View output script that is used by \galastri\core\Galastri class to return a HTML
 * file to the request. The trait will find for two files, the template and the view. The template
 * is a file that can have the HTML parts, like navigation bar, menus and that sets the <head> tags.
 * The view is the content of the route, usually the middle of the page.
 */
trait File
{
    /**
     * Stores the path of the template file.
     *
     * @var string
     */
    private static ?array $fileData = null;

    /**
     * Stores the path of the template file.
     *
     * @var string
     */
    private static string $baseFolder;

    // /**
    //  * Stores the path of the view file or null if the view file doesn't exists.
    //  *
    //  * @var null|string
    //  */
    private static TypeString $filePath;

    // /**
    //  * Main method that call the verification chain that checks if the template and view files exist
    //  * and finally require the template file passing the route controller data and the view file
    //  * path as parameters.
    //  *
    //  * @return void
    //  */
    private static function file(): void
    {
        Debug::setBacklog();

        self::fileSetFileData();

        if(Parameters::getDownloadable()){
            self::fileDownload();
        } else {
            self::filePrintContent();
        }
    }

    /**
     * fileSetDataFromController
     *
     * @return void
     */
    private static function fileSetFileData(): void
    {
        self::$fileData = self::$routeController ? self::$routeController->getFileContents() : null;

        if (self::$fileData === null) {
            self::fileCheckBaseFolder();
        }
    }

    private static function fileDownload(): void
    {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.self::$fileData[2].'"');
        header('Expires: 0');
        header('Content-Length: '.mb_strlen(self::$fileData[0], '8bit'));
        flush();
        ob_start();
        @print(self::$fileData[0]);
        ob_end_flush;
        flush();
    }

    private static function filePrintContent(): void
    {
        if (null !== $browserCache = Parameters::getBrowserCache()) {
            $etag = md5(substr(self::$fileData[0], 0, 1000).self::$fileData[2]);

            header('Last-Modified: '.gmdate('r', time()));
            if (isset($browserCache[1])) {
                header('Cache-Control: '.$browserCache[1]);
            }
            header('Expires: Tue, 01 Jul 1980 1:00:00 GMT');
            header('Etag: '.$etag);

            $cached = false;
            if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])){
                if(time() <= strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE'])+$browserCache[0]){
                    $cached = true;
                }
            }
            if(isset($_SERVER['HTTP_IF_NONE_MATCH'])){
                if(str_replace('"', '', stripslashes($_SERVER['HTTP_IF_NONE_MATCH'])) != $etag){
                    $cached = false;
                }
            }
            if($cached){
                header('HTTP/1.1 304 Not Modified');
                exit();
            }
        }

        header('Content-type: '.self::$fileData[1]);
        @print(self::$fileData[0]);
    }

    /**
     * fileSetDataFromController
     *
     * @return void
     */
    private static function fileCheckBaseFolder(): void
    {
        if (empty($baseFolder = Parameters::getBaseFolder())) {
            throw new Exception(self::UNDEFINED_BASE_FOLDER[1], self::UNDEFINED_BASE_FOLDER[0]);
        }

        self::$baseFolder = $baseFolder;
        self::fileCheckFilePath();
    }

    /**
     * fileSetDataFromController
     *
     * @return void
     */
    private static function fileCheckFilePath(): void
    {
        if (empty(Route::getUrlArray())) {
            if (Parameters::getDisplayErrors()) {
                throw new Exception(self::UNDEFINED_FILE_PATH[1], self::UNDEFINED_FILE_PATH[0]);
            }

            self::return404();
        }

        self::fileCheckFileExtension();
    }

    /**
     * fileSetDataFromController
     *
     * @return void
     */
    private static function fileCheckFileExtension(): void
    {
        $urlArray = Route::getUrlArray();

        $fileKeyValue = $urlArray[array_key_last($urlArray)];
        $allowedExtensions = Parameters::getAllowedExtensions();

        $fileArray = explode('.', $fileKeyValue);

        $fileName = $fileArray[0];
        $fileExtension = mb_strtolower($fileArray[1]);

        $filePath = '/'.implode('/', Route::getUrlArray());

        if ($allowedExtensions !== null) {
            $allowedExtensions = array_flip($allowedExtensions);

            if (!isset($allowedExtensions[$fileExtension])) {
                self::return404();
            }
        }

        if (!isset(GALASTRI_CONTENT_TYPE[$fileExtension])) {
            if (Parameters::getDisplayErrors()) {
                throw new Exception(self::UNDEFINED_EXTENSION_CONTENT_TYPE[1], self::UNDEFINED_EXTENSION_CONTENT_TYPE[0], [$fileExtension]);
            }

            self::return404();
        }

        self::$filePath = new TypeString (self::$baseFolder.$filePath);

        self::fileCheckFileExists($fileName, $fileExtension);
    }

    private static function fileCheckFileExists($fileName, $fileExtension): void
    {
        if (self::$filePath->fileNotExists()) {
            self::return404();
        }

        self::$fileData = [self::$filePath->fileGetContents(), GALASTRI_CONTENT_TYPE[$fileExtension], $fileName.'.'.$fileExtension];
    }

    private static function fileRequiresController(): bool
    {
        return false;
    }
}
