<?php

namespace galastri\extensions\output;

use \galastri\core\Route;
use \galastri\core\Debug;
use \galastri\modules\types\TypeString;
use \galastri\extensions\Exception;
use \galastri\extensions\output\helpers\ViewHelper;

/**
 * This is the View output script that is used by \galastri\core\Galastri class to return a HTML
 * file to the request. The trait will find for two files, the template and the view. The template
 * is a file that can have the HTML parts, like navigation bar, menus and that sets the <head> tags.
 * The view is the content of the route, usually the middle of the page.
 */
trait View
{
    /**
     * Stores the path of the template file.
     *
     * @var string
     */
    private static TypeString $viewTemplateFile;

    /**
     * Stores the path of the view file or null if the view file doesn't exists.
     *
     * @var null|string
     */
    private static TypeString $viewFilePath;

    /**
     * Main method that call the verification chain that checks if the template and view files exist
     * and finally require the template file passing the route controller data and the view file
     * path as parameters.
     *
     * @return void
     */
    private static function view(): void
    {
        Debug::setBacklog();

        self::viewCheckTemplateFile();
        self::viewDefineViewPath();
        self::viewCheckFileExists();

        self::viewRequireTemplate(
            new ViewHelper(
                self::$routeController->getResultData(),
                self::$viewFilePath
            ),
            self::$viewTemplateFile
        );
    }

    /**
     * Checks if the template file path was set. If not, it will throw an exception. The template
     * file is required for View output.
     *
     * If the template file is set, then it is stored.
     *
     * @return void
     */
    private static function viewCheckTemplateFile(): void
    {
        if (empty($viewTemplateFile = self::$routeController->getViewTemplateFile())) {
            throw new Exception(self::UNDEFINED_TEMPLATE_FILE[1], self::UNDEFINED_TEMPLATE_FILE[0]);
        }

        self::$viewTemplateFile = new TypeString($viewTemplateFile);
    }

    /**
     * Checks if the view file path was set. The path is based on two parts:
     *
     * - viewBaseFolder: the base folder where the view files are stored.
     * - viewFilePath: is the view file location inside the base folder.
     *
     * This method checks if the base folder is set in the configurations or by the controller. If
     * it is, it is stored in the $viewBaseFolder variable. If it is not, the base folder will be
     * the default '/app/views'.
     *
     * However, the route parameter 'viewFilePath' can be used in the route configuration. If this
     * is set, everything is ignored and only the parameter value is set as view file path. If it
     * isn't set, then it gets the namespace as base of the filepath and the child node name as file
     * name.
     *
     * @return void
     */
    private static function viewDefineViewPath(): void
    {
        if (empty($viewBaseFolder = self::$routeController->getViewBaseFolder())) {
            $viewBaseFolder = self::VIEW_BASE_FOLDER;
        }

        if (empty($viewFilePath = self::$routeController->getViewFilePath())) {
            $controllerNamespace = implode(array_map(function ($a) {
                return str_replace(['\Index', '\\'], ['/', '/'], $a);
            }, Route::getControllerNamespace()));

            $childNodeName = Route::getChildNodeName();

            $viewFilePath = $controllerNamespace.'/'.$childNodeName.'.php';
        } else {
            $viewBaseFolder = '';
        }

        self::$viewFilePath = new TypeString($viewBaseFolder.$viewFilePath);

    }

    /**
     * This method checks if the template and view file exists. The template file is required, so if
     * it doesn't exist an exception is thrown. If the view doesn't exist, the method check if it
     * was manually set. If true, then an exception is thrown, if false, the view file path will be
     * just null.
     *
     * @return void
     */
    private static function viewCheckFileExists(): void
    {
        if (self::$viewTemplateFile->fileNotExists()) {
            throw new Exception(self::TEMPLATE_FILE_NOT_FOUND[1], self::TEMPLATE_FILE_NOT_FOUND[0], [self::$viewTemplateFile->get()]);
        }

        if (self::$viewFilePath->fileNotExists()) {
            if (empty(self::$routeController->getViewFilePath())) {
                self::$viewFilePath->setNull();
            } else {
                throw new Exception(self::VIEW_FILE_NOT_FOUND[1], self::VIEW_FILE_NOT_FOUND[0], [self::$viewFilePath->get()]);
            }
        }
    }

    /**
     * This is the last part of the View output. It just import the template path. An important
     * thing is the $galastri parameter. It is an object that will be used in the template file to
     * get the data returned by the route controller.
     *
     * @param  ViewHelper $galastri                 Object of ViewHelper class, with many methods to
     *                                              get and manipulate the data returned by the
     *                                              route controller.
     *
     * @param  TypeString $templatePath             The path of the template file.
     *
     * @return void
     */
    private static function viewRequireTemplate(ViewHelper $galastri, TypeString $templatePath): void
    {
        require($templatePath->realPath()->get());
    }

    private static function viewRequiresController(): bool
    {
        return true;
    }
}
