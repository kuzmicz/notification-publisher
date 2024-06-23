<?php

namespace App\NotificationPublisher\Application\Exception;

class ThrottledException extends \Exception
{
    public function __construct(string $message = 'Rate limit exceeded', int $code = 0, ?\Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
