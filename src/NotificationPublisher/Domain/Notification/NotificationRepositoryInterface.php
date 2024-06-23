<?php

namespace App\NotificationPublisher\Domain\Notification;

use App\NotificationPublisher\Domain\User\User;

interface NotificationRepositoryInterface
{
    public function save(Notification $notification): void;

    public function findByUserId(User $user): array;
}
