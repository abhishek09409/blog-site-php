<?php

declare(strict_types=1);

class HttpException extends RuntimeException
{
    public function __construct(string $message, public int $status = 500, ?Throwable $previous = null)
    {
        parent::__construct($message, $status, $previous);
    }
}
