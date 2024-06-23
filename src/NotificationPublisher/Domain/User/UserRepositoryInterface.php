<?php

namespace App\NotificationPublisher\Domain\User;

interface UserRepositoryInterface
{
    public function findById(int $id): ?User;
}
