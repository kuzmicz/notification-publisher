<?php

namespace App\NotificationPublisher\Application\User\Command;

readonly class CreateUserCommand
{
    public function __construct(
        private string $email,
        private string $phoneNumber
    ) {
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPhoneNumber(): string
    {
        return $this->phoneNumber;
    }
}
