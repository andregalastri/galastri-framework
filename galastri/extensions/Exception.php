<?php

namespace galastri\extensions;

/**
 * This class is an extension of the PHP Exception class. The original class doesn't allow to inform
 * a string numeral, only integer and this extension resolves it.
 */
class Exception extends \Exception implements \Language
{
    /**
     * Stores an array with additional data that will be trasmitted with the message and the code.
     *
     * @var array
     */
    protected array $data = [];
    protected ?Exception $previous = null;

    /**
     * The construct of this extension allows to pass the code as string and add additional data to
     * be worked inside the catch when an exception occurs.
     *
     * @param  string $message                      The text message describing the exception.
     *
     * @param  int|string $code                     The code that defines the exception. Can be a
     *                                              string.
     *
     * @param  array $data                          Additional data to be trasmitted to the catch
     *                                              command.
     *
     * @param  Exception $previous                  The previous exception data.
     *
     * @return void
     */
    // public function __construct(string $message = '', /*int|string*/ $code = 0, array $data = [], Exception $previous = null)
    // {
    //     parent::__construct($message, 0, $previous);

    //     $this->code = $code;
    //     $this->data = $data;
    // }
    public function __construct()
    {
        $parameter = func_get_args();

        if (!empty($parameter)) {
            if (gettype($parameter[0]) == 'array'){
                if (count($parameter[0]) < 2) {
                    throw new Exception(self::EXCEPTION_PARAMETER_ARRAY_1_NEEDS_2_VALUES);
                }
                $this->setCode(array_shift($parameter[0]));
                $this->setMessage(array_shift($parameter[0]));

                if (array_key_exists(1, $parameter)) {
                    $this->setData($parameter[1]);
                }

                if (array_key_exists(2, $parameter)) {
                    $this->setPrevious($parameter[2]);
                }
            } else {
                $this->setMessage($parameter[0]);

                if (array_key_exists(1, $parameter)) {
                    $this->setCode($parameter[1]);
                }

                if (array_key_exists(2, $parameter)) {
                    $this->setData($parameter[2]);
                }

                if (array_key_exists(3, $parameter)) {
                    $this->setPrevious($parameter[3]);
                }
            }
        }

        parent::__construct($this->getMessage(), 0, $this->getPrevious());
    }

    private function setMessage(string $message): void
    {
        $this->message = $message;
    }

    private function setCode(/*int|string*/ $code): void
    {
        if (gettype($code) === 'integer' or gettype($code) === 'string') {
            $this->code = $code;
        } else {
            throw new Exception(self::EXCEPTION_INVALID_CODE_TYPE, [gettype($code)]);
        }
    }

    private function setPrevious(?Exception $previous): void
    {
        $this->previous = $previous;
    }

    private function setData(array $data): void
    {
        $this->data = $data;
    }
    /**
     * Gets all additional data trasmitted to the exception or a specific key of the array.
     *
     * @param  null|int|string $key                 Specifies a key of the array to be returned.
     *                                              When null, returns the entire array.
     * @return mixed
     */
    public function getData(/*null|int|string*/$key = null) // : mixed
    {
        return $key === null ? $this->data : $this->data[$key];
    }

}
