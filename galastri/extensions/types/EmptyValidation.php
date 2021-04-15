<?php

namespace galastri\extensions\types;

use galastri\extensions\Exception;

final class EmptyValidation implements \Language
{
    use Common;

    public function denyNull()
    {
        $this->chain[] = function () {
            if ($this->value === null) {
                $this->throwErrorMessage();
            }
        };
    }

    public function denyEmpty()
    {
        $this->chain[] = function () {
            vardump($this->value, 'denyEmpty');
            if (empty($this->value)) {
                $this->throwErrorMessage();
            }
        };
    }
}
