<?php

namespace App\NotificationPublisher\Application\Notification\Command;

use App\NotificationPublisher\Application\Middleware\ThrottleInterface;

readonly class SendNotificationCommand implements ThrottleInterface
{
    public function __construct(
        private string $userId,
        private string $channel,
        private string $message,
        private ?string $subject
    ) {
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function getChannel(): string
    {
        return $this->channel;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }
}
