<?php

namespace App\NotificationPublisher\Domain\Notification\Exception;

class NotificationSendFailedException extends \Exception
{
    public function __construct(string $message = 'All providers failed to send the notification.', ?\Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
