<?php
namespace galastri\extensions;

class Exception extends \Exception
{
    private $data;

    public function __construct(string $message = '', mixed $code = 0, array $data = [], Exception $previous = null){
        parent::__construct($message, 0, $previous);

        $this->code = $code;
        $this->data = $data;
    }

    public function getData(mixed $index = false)
    {
        return $index === false ? $this->data : $this->data[$index];
    }
}
