<?php

namespace galastri\extensions;

/**
 * This class is an extension of the PHP Exception class. The original class doesn't allow to inform
 * a string numeral, only integer and this extension resolves it.
 */
class Exception extends \Exception
{
    /**
     * Stores an array with additional data that will be trasmitted with the message and the code.
     *
     * @var array
     */
    private array $data;

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
    public function __construct(string $message = '', /*int|string*/ $code = 0, array $data = [], Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);

        $this->code = $code;
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
