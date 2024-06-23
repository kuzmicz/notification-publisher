<?php

namespace App\NotificationPublisher\Application\Exception;

class InvalidCommandException extends \Exception
{
    public function __construct(string $message = 'Invalid command', int $code = 0, ?\Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
