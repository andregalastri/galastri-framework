<?php

namespace galastri\extensions\output;

use \galastri\core\Route;
use \galastri\core\Debug;
use \galastri\modules\Toolbox;
use \galastri\extensions\ViewOutputData;

trait View
{
    private static string $viewTemplateFile;
    private static ?string $viewFilePath;

    private static function view(): void
    {
        self::viewCheckTemplateFile();
        self::viewDefineViewPath();
        self::checkFileExists();

        self::requireTemplate(
            new ViewOutputData(
                self::$routeController->getResultData(),
                self::$viewFilePath
            ),
            self::$viewTemplateFile
        );
    }

    private static function viewCheckTemplateFile(): void
    {
        if (empty($viewTemplateFile = self::$routeController->getViewTemplateFile())) {
            echo 'error, no template file';exit;
        }

        self::$viewTemplateFile = $viewTemplateFile;
    }

    private static function viewDefineViewPath(): void
    {
        if (empty($viewBaseFolder = self::$routeController->getViewBaseFolder())) {
            $viewBaseFolder = '/app/views';
        }

        if (empty($viewFilePath = self::$routeController->getViewFilePath())) {

            $controllerNamespace = implode(array_map(function($a){
                return str_replace(['\Index', '\\'], ['/', '/'], $a);
            }, Route::getControllerNamespace()));
            
            $childNodeName = Route::getChildNodeName();

            $viewFilePath = $controllerNamespace.'/'.$childNodeName.'.php';
        } else {
            $viewBaseFolder = '';
        }

        self::$viewFilePath = $viewBaseFolder.$viewFilePath;
    }

    private static function checkFileExists(): void
    {
        if (!Toolbox::checkFileExists(self::$viewTemplateFile)) {
            echo 'error, template file doesnt exists';exit;
        }

        if (!Toolbox::checkFileExists(self::$viewFilePath)) {
            self::$viewFilePath = null;
        }
    }

    private static function requireTemplate($galastri, $path)
    {
        require_once(Toolbox::getRealPath($path));
    }
}
