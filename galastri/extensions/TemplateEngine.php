<?php

namespace galastri\extensions;

use galastri\extensions\Exception;

abstract class TemplateEngine implements \Language
{
    private bool $isConstructed = false;
    private array $controllerData;
    private ?string $viewPath;
    private string $templatePath;
    private bool $browserCache;

    public function __construct(array $controllerData, ?string $viewPath, string $templatePath, bool $browserCache)
    {
        if (!$this->isConstructed) {
            $this->controllerData = $controllerData;
            $this->controllerData['galastri']['view'] = $viewPath;

            $this->templatePath = $templatePath;
            $this->browserCache = $browserCache;
    
            $this->isConstructed = true;
        } else {
            throw new Exception(self::TEMPLATE_ENGINE_CLASS_ALREADY_CONSTRUCTED);
        }
    }

    protected function getControllerData(): array
    {
        return $this->controllerData;
    }

    protected function getTemplatePath(): string
    {
        return $this->templatePath;
    }

    protected function getBrowserCache(): bool
    {
        return $this->browserCache;
    }

    public function execRender(): void
    {
        try {
            $this->render();
        } catch (\Error $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    abstract public function render(): void;
}
