<?php
namespace galastri\extensions;

class Exception extends \Exception
{
    private $data;

    public function __construct($message = null, $code = 0, $data = null, Exception $previous = null){
        parent::__construct($message, 0, $previous);

        $this->code = $code;
        $this->data = $data;
    }

    public function getData($index = false)
    {
        return $index === false ? $this->data : $this->data[$index];
    }
}
