<?php

namespace galastri\extensions\output\templateEngines;

use galastri\extensions\Exception;

final class TwigEngine extends EngineBase implements \Language
{
    protected function checkIfEngineIsInstalled(): void
    {
        if (!class_exists('\Twig\Loader\FilesystemLoader')) {
            throw new Exception(self::TWIG_FILESYSTEMLOADER_NOT_FOUND[1], self::TWIG_FILESYSTEMLOADER_NOT_FOUND[0]);
        }

        if (!class_exists('\Twig\Environment')) {
            throw new Exception(self::TWIG_ENVIRONMENT_NOT_FOUND[1], self::TWIG_ENVIRONMENT_NOT_FOUND[0]);
        }
    }

    protected function render(): void
    {
        $loader = new \Twig\Loader\FilesystemLoader(GALASTRI_PROJECT_DIR);
        
        $options['cache'] = GALASTRI_PROJECT_DIR . '/tmp/twig-cache';

        if (!empty($this->templateOptions)) {
            foreach($this->templateOptions as $option => $value) {
                $options[$option] = $value;
            }
        }

        $twig = new \Twig\Environment($loader, $options);

        $this->routeControllerData['galastri']['view'] = $this->viewFilePath->get();
        
        print($twig->render($this->viewTemplateFile->get(), $this->routeControllerData));
    }
}
