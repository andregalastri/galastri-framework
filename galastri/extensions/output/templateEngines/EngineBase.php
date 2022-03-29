<?php

namespace galastri\extensions\output\templateEngines;

use galastri\extensions\Exception;
use galastri\modules\types\TypeString;

abstract class EngineBase implements \Language
{
    protected array $routeControllerData;
    protected TypeString $viewFilePath;
    protected TypeString $viewTemplateFile;
    protected bool $browserCache;
    protected ?array $templateOptions;

    public function __construct(array $routeControllerData, TypeString $viewFilePath, TypeString $viewTemplateFile, bool $browserCache, ?array $templateOptions)
    {
        $this->routeControllerData = $routeControllerData;
        $this->viewFilePath = $viewFilePath;
        $this->viewTemplateFile = $viewTemplateFile;
        $this->browserCache = $browserCache;
        $this->templateOptions = $templateOptions;

        $this->checkIfEngineIsInstalled();

        $this->tryRender();
    }

    protected function tryRender(): void
    {
        if (!$this->browserCache) {
            try {
                $this->render();
            } catch (\RuntimeException | \BadMethodCallException | \InvalidArgumentException | \TypeError $e) {
                throw new Exception(str_replace('%', '%%', $e->getMessage()), 'TEMPLATE'.$e->getCode());
            } catch (\Twig\Error\Error | \Twig\Error\LoaderError | \Twig\Error\RuntimeError | \Twig\Error\SyntaxError $e) {
                throw new Exception(str_replace('%', '%%', $e->getMessage()), 'TWIG'.$e->getCode());
            }
        }
    }
}
