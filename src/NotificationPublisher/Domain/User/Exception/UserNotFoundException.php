<?php

namespace App\NotificationPublisher\Domain\User\Exception;

class UserNotFoundException extends \Exception
{
    public function __construct(string $userId)
    {
        parent::__construct("User not found: $userId");
    }
}
