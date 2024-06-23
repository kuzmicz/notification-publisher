<?php

namespace App\NotificationPublisher\Application\Notification\CommandHandler;

use App\NotificationPublisher\Application\Notification\Command\SendNotificationCommand;
use App\NotificationPublisher\Application\Notification\Service\NotificationProviderFactory;
use App\NotificationPublisher\Domain\Notification\Exception\NotificationSendFailedException;
use App\NotificationPublisher\Domain\Notification\Notification;
use App\NotificationPublisher\Domain\Notification\NotificationRepositoryInterface;
use App\NotificationPublisher\Domain\User\UserRepositoryInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class SendNotificationCommandHandler
{
    public function __construct(
        private NotificationRepositoryInterface $notificationRepository,
        private NotificationProviderFactory $providerFactory,
        private UserRepositoryInterface $userRepository,
        private LoggerInterface $logger
    ) {
    }

    public function __invoke(SendNotificationCommand $command): void
    {
        $user = $this->userRepository->findById($command->getUserId());
        if (!$user) {
            $this->logger->error('User not found', ['userId' => $command->getUserId()]);

            return;
        }

        $providers = $this->providerFactory->getProviders($command->getChannel());
        $lastException = null;

        foreach ($providers as $provider) {
            try {
                $provider->send($user, $command);

                $notification = new Notification(
                    $user,
                    $command->getChannel(),
                    $command->getSubject(),
                    $command->getMessage(),
                    get_class($provider),
                    new \DateTimeImmutable()
                );

                $this->notificationRepository->save($notification);

                return;
            } catch (\Exception $e) {
                $lastException = $e;
                $this->logger->error(
                    'Failed to send notification', [
                        'provider' => get_class($provider),
                        'error' => $e->getMessage(),
                        'command' => $command,
                    ]
                );
            }
        }

        if (null !== $lastException) {
            $this->logger->error('All providers failed, retrying message', ['command' => $command]);
            throw new NotificationSendFailedException('All providers failed to send the notification.', $lastException);
        }
    }
}
