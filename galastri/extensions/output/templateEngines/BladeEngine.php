<?php

namespace galastri\extensions\output\templateEngines;

use galastri\extensions\Exception;

final class BladeEngine extends EngineBase implements \Language
{
    protected function checkIfEngineIsInstalled(): void
    {
        if (!class_exists('\eftec\bladeone\BladeOne')) {
            throw new Exception(self::BLADE_NOT_FOUND[1], self::BLADE_NOT_FOUND[0]);
        }
    }

    protected function render(): void
    {
        $blade = new \eftec\bladeone\BladeOne(GALASTRI_PROJECT_DIR, GALASTRI_PROJECT_DIR . '/tmp/blade-cache', \eftec\bladeone\BladeOne::MODE_DEBUG);
        $blade->pipeEnable = true;

        $viewFilePath = $this->viewFilePath->trimStart('/')->get();

        $this->routeControllerData['galastri']['view'] = empty($viewFilePath) ? true : $viewFilePath;

        print($blade->run($this->viewTemplateFile->get(), $this->routeControllerData));
    }
}
