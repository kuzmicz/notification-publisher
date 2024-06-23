<?php

namespace App\NotificationPublisher\Application\User\QueryHandler;

use App\NotificationPublisher\Application\User\Query\GetUserNotificationsQuery;
use App\NotificationPublisher\Domain\Notification\NotificationRepositoryInterface;

readonly class GetUserNotificationsQueryHandler
{
    public function __construct(private NotificationRepositoryInterface $notificationRepository)
    {
    }

    public function __invoke(GetUserNotificationsQuery $query): array
    {
        return $this->notificationRepository->findByUserId($query->getUser());
    }
}
