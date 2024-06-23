<?php

namespace App\NotificationPublisher\Domain\Notification\Service;

use App\NotificationPublisher\Application\Notification\Command\SendNotificationCommand;
use App\NotificationPublisher\Domain\User\User;

interface SendNotificationInterface
{
    public function send(User $user, SendNotificationCommand $command): void;
}
