<?php

namespace App\NotificationPublisher\UserInterface\Controller;

use App\NotificationPublisher\Application\User\Query\GetUserNotificationsQuery;
use App\NotificationPublisher\Domain\User\UserRepositoryInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api', name: 'notification_')]
class NotificationQueryController extends AbstractController
{
    use HandleTrait;

    public function __construct(
        private readonly MessageBusInterface $queryBus,
        private readonly UserRepositoryInterface $userRepository,
        private readonly LoggerInterface $logger
    ) {
        $this->messageBus = $queryBus;
    }

    #[Route('/notifications/user/{userId}', name: 'get_notifications', methods: ['GET'])]
    public function getNotifications(string $userId): JsonResponse
    {
        try {
            $user = $this->userRepository->findById($userId);
            if (!$user) {
                return $this->json(['error' => 'User not found'], Response::HTTP_BAD_REQUEST);
            }
            $notifications = $this->handle(new GetUserNotificationsQuery($user));

            return $this->json(['notifications' => $notifications]);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());

            return $this->json(['error' => 'Cannot get notifications for user'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
