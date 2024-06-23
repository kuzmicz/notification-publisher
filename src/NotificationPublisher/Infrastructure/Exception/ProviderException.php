<?php

namespace App\NotificationPublisher\Infrastructure\Exception;

class ProviderException extends \Exception
{
    public function __construct(string $service, string $message = 'Provider error', ?\Exception $previous = null)
    {
        parent::__construct("Provider error $service: $message", 0, $previous);
    }
}
