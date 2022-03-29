<?php

namespace galastri\extensions\output\traits;

use \galastri\core\Parameters;
use \galastri\extensions\output\templateEngines\PhpEngine;
use \galastri\extensions\output\templateEngines\TwigEngine;
use \galastri\extensions\output\templateEngines\BladeEngine;
use \galastri\extensions\output\templateEngines\LatteEngine;

/**
 * This trait has common methods that are shared within the type classes.
 */
trait TemplateEngines
{
    /**
     * This is the last part of the View output. This method import the template file, based on the
     * template path.
     *
     * Note that the $galastri parameter isn't used here, despite it being declared and required.
     * This is because the $galastri parameter is an object for the PhpEngine, which has methods to
     * retrive or print route controller data and import other template parts to the main template
     * file.
     *
     * This method also check if the browser cache is set in the route configuration and if the file
     * view and template files were changed. If it was, its content is downloaded from the server,
     * if not, the cached template and view will be used instead.
     *
     * @param  PhpEngine $galastri                  An object used in the template and view files to
     *                                              handle route controller data. It isn't used
     *                                              here, but in the imported files.
     *
     * @param  TypeString $templatePath             The template file path.
     *
     * @return void
     */
    private static function viewPhpTemplatePrint(): void
    {
        $data = new PhpEngine(
            self::$routeController->getResultData()['data'],
            self::$viewFilePath,
        );

        $galastri = new PhpEngine(
            self::$routeController->getResultData()['galastri'],
            self::$viewFilePath,
        );

        if (!self::browserCache(self::viewCheckFileLastModified())) {
            require(self::$viewTemplateFile->realPath()->get());
        }
    }

    private static function viewTwigTemplatePrint(): void
    {
        new TwigEngine(
            self::$routeController->getResultData(),
            self::$viewFilePath,
            self::$viewTemplateFile,
            self::browserCache(self::viewCheckFileLastModified()),
            self::$templateEngine[1] ?? null
        );
    }

    private static function viewBladeTemplatePrint(): void
    {
        new BladeEngine(
            self::$routeController->getResultData(),
            self::$viewFilePath,
            self::$viewTemplateFile,
            self::browserCache(self::viewCheckFileLastModified()),
            self::$templateEngine[1] ?? null
        );
    }

    private static function viewLatteTemplatePrint(): void
    {
        new LatteEngine(
            self::$routeController->getResultData(),
            self::$viewFilePath,
            self::$viewTemplateFile,
            self::browserCache(self::viewCheckFileLastModified()),
            self::$templateEngine[1] ?? null
        );
    }

    private static function viewCheckFileLastModified(): string
    {
        $viewLastModified = '';
        $templateLastModified = self::$viewTemplateFile->fileLastModified()->get();

        if (self::$viewFilePath->isNotNull()) {
            if (self::$viewFilePath->fileExists()) {
                $viewLastModified = self::$viewFilePath->fileLastModified()->get();
            }
        }

        return $viewLastModified.$templateLastModified;
    }
}
