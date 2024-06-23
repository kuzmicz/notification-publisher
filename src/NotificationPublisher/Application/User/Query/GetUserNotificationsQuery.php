<?php

namespace App\NotificationPublisher\Application\User\Query;

use App\NotificationPublisher\Domain\User\User;

readonly class GetUserNotificationsQuery
{
    public function __construct(private User $user)
    {
    }

    public function getUser(): User
    {
        return $this->user;
    }
}
