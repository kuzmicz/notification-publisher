<?php

namespace App\NotificationPublisher\UserInterface\Controller;

use App\NotificationPublisher\Application\User\Command\CreateUserCommand;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api', name: 'user_command_')]
class UserCommandController extends AbstractController
{
    public function __construct(
        private MessageBusInterface $commandBus,
        private LoggerInterface $logger
    ) {
    }

    #[Route('/create-user', name: 'create_user', methods: ['POST'])]
    public function createUser(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            if (!$data || !isset($data['email'], $data['phoneNumber'])) {
                return new JsonResponse(['error' => 'Invalid data'], Response::HTTP_BAD_REQUEST);
            }

            $command = new CreateUserCommand($data['email'], $data['phoneNumber']);
            $envelope = $this->commandBus->dispatch($command);

            /**
             * @var HandledStamp $handledStamp
             */
            $handledStamp = $envelope->last(HandledStamp::class);
            if (null === $handledStamp) {
                throw new \RuntimeException('Command was not handled');
            }

            $userId = $handledStamp->getResult();

            return new JsonResponse(
                [
                    'status' => 'User created successfully.',
                    'userId' => $userId,
                ], Response::HTTP_CREATED
            );
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());

            return new JsonResponse(['error' => 'Cannot create a new user.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
