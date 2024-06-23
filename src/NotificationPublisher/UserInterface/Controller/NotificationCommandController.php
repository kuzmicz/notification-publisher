<?php

namespace App\NotificationPublisher\UserInterface\Controller;

use App\NotificationPublisher\Application\Notification\Command\SendNotificationCommand;
use App\NotificationPublisher\Domain\User\UserRepositoryInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api', name: 'notification_command_')]
class NotificationCommandController extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $commandBus,
        private readonly LoggerInterface $logger,
        private readonly UserRepositoryInterface $userRepository
    ) {
    }

    #[Route('/notify', name: 'notify', methods: ['POST'])]
    public function sendNotification(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            if ($validationError = $this->validateRequestData($data)) {
                return new JsonResponse(['error' => $validationError], Response::HTTP_BAD_REQUEST);
            }

            if (!$this->userRepository->findById($data['userId'])) {
                return new JsonResponse(['error' => "User ID: {$data['userId']} not found"], Response::HTTP_NOT_FOUND);
            }

            $definedChannels = $this->getParameter('notification')['channels'];

            foreach ($data['channels'] as $channel) {
                if (!isset($definedChannels[$channel])) {
                    return new JsonResponse(['error' => "Channel: {$channel} does not exist"], Response::HTTP_BAD_REQUEST);
                }

                $command = new SendNotificationCommand(
                    $data['userId'],
                    $channel,
                    $data['message'],
                    $data['subject'] ?? null
                );
                $this->commandBus->dispatch($command);
            }

            return new JsonResponse(['status' => 'Notification has been sent.']);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());

            return new JsonResponse(['error' => 'Cannot create a notification'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function validateRequestData(?array $data): ?string
    {
        if (!$data || !isset($data['userId'], $data['message'], $data['channels']) || !is_array($data['channels'])) {
            return 'Invalid data provided';
        }

        if (in_array('email', $data['channels']) && !isset($data['subject'])) {
            return 'Subject is required for email channel';
        }

        return null;
    }
}
