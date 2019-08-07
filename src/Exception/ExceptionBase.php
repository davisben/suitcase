<?php

namespace Suitcase\Exception;

use Throwable;

class ExceptionBase extends \Exception
{
    /**
     * A detailed error message.
     *
     * @var string $error
     */
    protected $error;

    /**
     * Constructs an exception object.
     *
     * @param string $message
     *   The exception message to throw.
     * @param string $error
     *   The error that caused the exception.
     * @param int $code
     *   The exception code.
     * @param Throwable|null $previous
     *   The previous exception used for the exception chaining.
     */
    public function __construct($message = '', $error = '', $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->error = $error;
    }

    /**
     * Gets error that caused the exception.
     *
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }
}
