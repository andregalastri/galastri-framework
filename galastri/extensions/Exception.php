<?php
namespace galastri\extensions;

/**
 * This class is an extension of the PHP Exception class. The original class
 * doesn't allow to inform a string numeral, only integer and this extension
 * resolves it.
 */
class Exception extends \Exception
{
    /**
     * An array with additional data the will be trasmitted with the message and
     * the code.
     *
     * @var array
     */
    private $data;
    
    /**
     * Called when the Exception class is instanciated inside the try/catch
     * commands, storing the message, the code and additional data to be worked
     * inside the catch when an exception occurs.
     *
     * @param  string $message          The text message describing the
     *                                  exception.
     *
     * @param  string|int $code         The code that defines the exception.
     *
     * @param  array $data              Additional data to be trasmitted to the
     *                                  catch command.
     *
     * @param  Exception $previous      The previous exception data.
     *
     * @return void
     */
    public function __construct(string $message = '', mixed $code = 0, array $data = [], Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);

        $this->code = $code;
        $this->data = $data;
    }
    
    /**
     * Returns all additional data trasmitted to the exception or a specific key
     * of the array.
     *
     * @param  string|int|bool $key     Specify a key of the array to be
     *                                  returned. When false, returns the entire
     *                                  array.
     * @return mixed
     */
    public function getData(mixed $key = false)
    {
        return $key === false ? $this->data : $this->data[$key];
    }
}
