<?php

namespace Mr\CventSdk\Exception;

class InvalidCredentialsException extends CventException
{
    public function __construct($message = "", $code = 0, \Throwable $previous = null)
    {
        parent::__construct('Invalid credentials', 401);
    }
}
