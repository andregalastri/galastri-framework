<?php

namespace galastri\extensions\output\templateEngines;

use galastri\extensions\Exception;

final class LatteEngine extends EngineBase implements \Language
{
    protected function checkIfEngineIsInstalled(): void
    {
        if (!class_exists('\Latte\Engine')) {
            throw new Exception(self::LATTE_NOT_FOUND[1], self::LATTE_NOT_FOUND[0]);
        }
    }

    protected function render(): void
    {
        $latte = new \Latte\Engine;
        $latte->setTempDirectory(GALASTRI_PROJECT_DIR . '/tmp/latte-cache');

        $viewFilePath = GALASTRI_PROJECT_DIR . $this->viewFilePath->get();

        $this->routeControllerData['galastri']['view'] = empty($viewFilePath) ? true : $viewFilePath;

        print($latte->renderToString($this->viewTemplateFile->realPath()->get(), $this->routeControllerData));
    }
}
