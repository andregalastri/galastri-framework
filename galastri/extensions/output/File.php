<?php

namespace galastri\extensions\output;

use galastri\core\Route;
use galastri\core\Parameters;
use galastri\core\Debug;
use galastri\modules\types\TypeString;
use galastri\extensions\Exception;

/**
 * This trait is the File output, used by the Galastri class, to return a file to the request.
 *
 * This trait:
 * - Gets the file content when defined by the route controller.
 * - If it doesn't defined by the route controller.
 *   - Checks and set a file path.
 *   - Checks the file extension.
 *   - Checks if the file exists.
 *   - Define the file content.
 * - Checks if the file is downloadable.
 *   - If it is true, downloads the file.
 *   - If it is false, it prints the file to return it to the request.
 *
 * Every property and method name start with 'file' to prevent incompatibilities with other output
 * traits.
 */
trait File
{
    use traits\Common;

    /**
     * Stores an array with file data. It needs to follow this format:
     *
     * Key 0: Stores the file content.
     * Key 1: Stores the MIME type of the file.
     * Key 2: Stores the file name and extension.
     *
     * @var array|null
     */
    private static ?array $fileData = null;

    /**
     * Stores the base directory path where the files are stored.
     *
     * @var string
     */
    private static string $baseFolder;

    /**
     * Stores an object that contains the file path.
     *
     * @var TypeString
     */
    private static TypeString $filePath;

    /**
     * This is the main method of the File output. It has methods to define and validate
     * the file data and to handle it to return the file to the request.
     *
     * @return void
     */
    private static function file(): void
    {
        Debug::setBacklog();

        self::fileCheckControllerData();

        if(Parameters::getDownloadable()){
            self::fileDownload();
        } else {
            self::filePrintContent();
        }
    }

    /**
     * This method checks if the route controller defined a file content. When this occurs, the File
     * output won't search for it in the base folder.
     *
     * However, if there is no controller or if the file contents wasn't defined by the route
     * controller, then a chain of methods will be executed to search for the file.
     *
     * @return void
     */
    private static function fileCheckControllerData(): void
    {
        /**
         * Checks if the route controller defined a file content. If it wasn't defined, it will
         * return null.
         */
        self::$fileData = self::$routeController ? self::$routeController->getFileContents() : null;

        /**
         * When undefined by the route controller, the File output will start searching for the file
         * in its directories.
         */
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
        ob_end_flush();
        flush();
    }

    private static function filePrintContent(): void
    {
        if(!self::browserCache(self::$fileData[0].self::$fileData[2])) {
            header('Content-type: '.self::$fileData[1]);
            @print(self::$fileData[0]);
        }
    }

    /**
     * This method checks if the base folder is defined. The base folder is required for the File
     * output to work, so, if it isn't defined, an exception will be thrown.
     *
     * @return void
     */
    private static function fileCheckBaseFolder(): void
    {
        if (empty($baseFolder = Parameters::getBaseFolder())) {
            throw new Exception(self::UNDEFINED_BASE_FOLDER);
        }

        self::$baseFolder = $baseFolder;

        self::fileCheckFilePath();
    }

    /**
     * This method checks if there is URL nodes in the URL array. The existence of keys in the URL
     * array indicates that there is a file path defined in the URL. Each URL node refers to the
     * path of the file.
     *
     *
     * @return void
     */
    private static function fileCheckFilePath(): void
    {
        /**
         * If there is no remaining URL nodes, and if the debug parameter 'displayErrors' is true,
         * then an exception is thrown with an error message.
         *
         * However, if the debug parameter is false, then the framework will return error 404.
         */
        if (empty(Route::getUrlArray())) {
            if (Parameters::getDisplayErrors()) {
                throw new Exception(self::UNDEFINED_FILE_PATH);
            }

            self::return404();
        }

        self::fileCheckFileExtension();
    }

    /**
     * This method checks if the file extension is allowed (based on the 'allowedExtensions'
     * pariameter the route configuration), and if it is defined in the mime type configuration
     * file.
     *
     * Any file need to be defined in the mime type configuration because the framework need to
     * return the file content with the mime type defined in the header. Without it, the file can be
     * shown or downloaded wrongly.
     *
     * @return void
     */
    private static function fileCheckFileExtension(): void
    {
        /**
         * Declaring variables
         */
        $urlArray = Route::getUrlArray();
        $allowedExtensions = Parameters::getAllowedExtensions();

        /**
         * Getting the last URL node, which is the file name.
         *
         * From the file name is extracted the extension. To do this, the file name is splitted in
         * parts, which the dot . works as delimiter. The last array key from this split is the
         * extension.
         */
        $fileName = $urlArray[array_key_last($urlArray)];

        $fileExtension = explode('.', $fileName);
        $fileExtension = mb_strtolower(array_pop($fileExtension));

        /**
         * The file path is built joining the URL array in to a string, with each array key
         * concatenated with / between them.
         */
        $filePath = '/'.implode('/', $urlArray);

        /**
         * If the allowedExtensions parameter is set, then it is checked if the current file
         * extension is set in the allowed extension list. If not, it will return error 404.
         */
        if ($allowedExtensions !== null) {
            $allowedExtensions = array_flip($allowedExtensions);

            if (!isset($allowedExtensions[$fileExtension])) {
                self::return404();
            }
        }

        /**
         * Here, the method checks if the file extension exists in the MIME type configuration. If
         * not, it will return an exception (if the displayErrors debug parameter is defined as
         * true) or will return error 404.
         */
        if (!isset(GALASTRI_MIME_TYPE[$fileExtension])) {
            if (Parameters::getDisplayErrors()) {
                throw new Exception(self::UNDEFINED_EXTENSION_MIME_TYPE, [$fileExtension]);
            }

            self::return404();
        }

        /**
         * Everything is OK so far, so the filePath parameter is set.
         */
        self::$filePath = new TypeString (self::$baseFolder . $filePath);

        /**
         * These two parameters do the final verifications
         */
        self::fileCheckFileExists();
        self::fileCheckMimeType($fileName, $fileExtension);
    }


    /**
     * This method just checks if the file do not exists. If it is true, an error 404 is returned.
     *
     * @return void
     */
    private static function fileCheckFileExists(): void
    {
        if (self::$filePath->fileNotExists()) {
            self::return404();
        }
    }

    /**
     * This method checks the MIME type of the file and compares with the MIME types defined in the
     * MIME type configuration file. If the MIME type of the file doesn't match with one of defined
     * in the configuration, an exception will be thrown.
     *
     * @param  mixed $fileName                      The name of the file from the last URL node.
     *
     * @param  mixed $fileExtension                 The extension of the file, extracted from the
     *                                              last URL node.
     *
     * @return void
     */
    private static function fileCheckMimeType($fileName, $fileExtension): void
    {
        /**
         * Check if the MIME type is set in the MIME type configuration file. If it is, the
         * $mimeType is set based on the index of the array of the extension.
         *
         * The validation will only occur if there is no ignoreMimeType route parameter defined or
         * if it is false. When true, the validation will be ignored.
         */
        if (Parameters::getIgnoreMimeType() !== true) {
            /**
             * Gets the MIME type of the file.
             */
            $mimeType = self::$filePath->mimeType()->get();

            $mimeTypeIndex = array_search($mimeType, GALASTRI_MIME_TYPE[$fileExtension]);

            if ($mimeTypeIndex !== false) {
                $mimeType = GALASTRI_MIME_TYPE[$fileExtension][$mimeTypeIndex];

            /**
             * If not, an exception is thrown.
             */
            } else {
                throw new Exception(
                    self::INVALID_MIME_TYPE_FOR_EXTENSION,
                    [
                        implode(', ', GALASTRI_MIME_TYPE[$fileExtension]),
                        $fileExtension,
                        $mimeType
                    ]
                );
            }
        }

        /**
         * Finally, sets the $fileData property.
         */
        self::$fileData = [self::$filePath->fileGetContents()->get(), GALASTRI_MIME_TYPE[$fileExtension][0], $fileName];
    }

    /**
     * This method is exclusively used by the Galastri class to determine if this output requires a
     * controller to work.
     *
     * @return bool
     */
    private static function fileRequiresController(): bool
    {
        return false;
    }
}
