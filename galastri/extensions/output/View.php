<?php

namespace galastri\extensions\output;

use \galastri\core\Route;
use \galastri\core\Debug;
use \galastri\core\Parameters;
use \galastri\modules\types\TypeString;
use \galastri\extensions\Exception;

/**
 * This trait is the View output, used by the Galastri class, to return a HTML to the request.
 *
 * This trait:
 * - Defines the path for the template file, which have the HTML template.
 * - Defines the path for the view file, which has the content that can be printed with the
 *   template.
 * - Checks if these files exists.
 * - Import the template and pass the route controller data and the view to that.
 *
 * Every property and method name start with 'view' to prevent incompatibilities with other output
 * traits.
 */
trait View
{
    use traits\Common;
    use traits\TemplateEngines;

    /**
     * Stores an object that contains the path of the template file.
     *
     * @var TypeString
     */
    private static TypeString $viewTemplateFile;

    /**
     * Stores an object that contains the path of the view file. If the view file doesn't exists, it
     * wil stores null.
     *
     */
    private static TypeString $viewFilePath;

    /**
     * Stores an object that contains the path of the view file. If the view file doesn't exists, it
     * wil stores null.
     *
     */
    private static array $templateEngine;

    /**
     * This is the main method of the View output. It has a chain of methods to define and validate
     * the view data and to import the files that will return the HTML to the request.
     *
     * @return void
     */
    private static function view(): void
    {
        Debug::setBacklog();

        self::$templateEngine = $templateEngine = Parameters::getTemplateEngine();

        /**
         * Calls the methods to define and validate the template and view files.
         */
        self::viewCheckTemplateFile();
        self::viewDefineViewPath();

        self::viewCheckFileExists();

        /**
         * Calls the method that will import the template file to be returned to the request.
         */
        self::{'view'.ucwords(self::$templateEngine[0]).'TemplatePrint'}();

    }

    /**
     * This method checks if the template file path was defined. If not, it will throw an exception
     * because the template file is required. It it is defined, then it is stored in the
     * $viewTemplateFile property as a TypeString object.
     *
     * @return void
     */
    private static function viewCheckTemplateFile(): void
    {
        if (empty($viewTemplateFile = self::$routeController->getTemplateFile())) {
            throw new Exception(self::UNDEFINED_TEMPLATE_FILE[1], self::UNDEFINED_TEMPLATE_FILE[0]);
        }

        self::$viewTemplateFile = new TypeString($viewTemplateFile);

        if(self::$viewTemplateFile->fileExtension()->get() !== self::$templateEngine[0]) {
            throw new Exception(self::VIEW_INVALID_TEMPLATE_EXTENSION[1], self::VIEW_INVALID_TEMPLATE_EXTENSION[0], [
                ucfirst(self::$templateEngine[0]),
                self::$viewTemplateFile->get(),
                self::$viewTemplateFile->fileExtension()->get(),
                self::$templateEngine[0]
            ]);
        }
    }

    /**
     * This method checks if the view file path was set. The path is based on two parts:
     *
     * 1. viewBaseFolder: the directory where the view files are stored. This can be the default
     *    app/views directory or can a custom one, defined by the 'baseFolder' parameter in the
     *    route configuration.
     * 2. viewPath: the view file location inside the base folder. This can follow the default
     *    based on the namespace of the route with the child node name as the name of the view file,
     *    or can be a custom one, defined by the 'viewPath' parameter in the route
     *    configuration.
     *
     * @return void
     */
    private static function viewDefineViewPath(): void
    {
        /**
         * First it checks if the 'baseFolder' parameter is empty. If it is, the base folder will be
         * the default 'app/views' directory. If not, it will be the folder defined in the
         * parameter.
         */
        if (empty($viewBaseFolder = self::$routeController->getBaseFolder())) {
            $viewBaseFolder = self::VIEW_BASE_FOLDER;
        }

        /**
         * Second it checks if the 'viewPath' parameter is empty. If it is, then it will get the
         * namespace and use it as the directory path and the child node name as the view file name.
         */
        if (empty($viewFilePath = self::$routeController->getViewPath())) {

            $controllerNamespace = Route::getControllerNamespace();

            foreach ($controllerNamespace as &$value) {
                $value = str_replace(['\Index', '\\'], ['', '/'], $value);
            }
            unset($value);

            $childNodeName = Route::getChildNodeName();

            $viewFilePath = implode($controllerNamespace) . '/' . $childNodeName . '.' . self::$templateEngine[0];

        /**
         * However, if the parameter exists in the route configuration, then the base folder will be
         * ignored, even if it is set in the 'baseFolder' parameter. This is needed because the
         * 'viewPath' points to the absolute path of the view file.
         */
        } else {
            $viewBaseFolder = '';
        }

        self::$viewFilePath = new TypeString($viewBaseFolder . $viewFilePath);
        if(self::$viewFilePath->fileExtension()->get() !== self::$templateEngine[0]) {
            throw new Exception(self::VIEW_INVALID_VIEW_EXTENSION[1], self::VIEW_INVALID_VIEW_EXTENSION[0], [
                strtoupper(self::$templateEngine[0]),
                self::$viewFilePath->fileName()->get(),
                self::$viewFilePath->fileExtension()->get(),
                self::$templateEngine[0]
            ]);
        }
    }

    /**
     * This method checks if the template and view files exist.
     *
     * @return void
     */
    private static function viewCheckFileExists(): void
    {
        /**
         * The template file is required, so, if it doesn't exist, an exception is thrown.
         */
        if (self::$viewTemplateFile->fileNotExists()) {
            throw new Exception(self::TEMPLATE_FILE_NOT_FOUND[1], self::TEMPLATE_FILE_NOT_FOUND[0], [self::$viewTemplateFile->get()]);
        }

        /**
         * The view file isn't required if the 'viewPath' parameter wasn't defined in the route
         * configuration. In this case, if the file doesn't exist in the view directory, the
         * $viewFilePath property will be set as null. However, if there is a 'viewPath' parameter
         * defined, then an exception will be thrown if the file doesn't exist.
         */
        if (self::$viewFilePath->fileNotExists()) {
            if (empty(self::$routeController->getViewPath())) {
                self::$viewFilePath->set(null);
            } else {
                throw new Exception(self::VIEW_FILE_NOT_FOUND[1], self::VIEW_FILE_NOT_FOUND[0], [self::$viewFilePath->get()]);
            }
        }
    }

    /**
     * This method is exclusively used by the Galastri class to determine if this output requires a
     * controller to work.
     *
     * @return bool
     */
    private static function viewRequiresController(): bool
    {
        return true;
    }
}
