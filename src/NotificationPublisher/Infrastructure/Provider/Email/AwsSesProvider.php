<?php

namespace App\NotificationPublisher\Infrastructure\Provider\Email;

use App\NotificationPublisher\Application\Notification\Command\SendNotificationCommand;
use App\NotificationPublisher\Domain\Notification\Service\SendNotificationInterface;
use App\NotificationPublisher\Domain\User\User;
use App\NotificationPublisher\Infrastructure\Exception\ProviderException;
use Aws\Ses\SesClient;
use Psr\Log\LoggerInterface;

readonly class AwsSesProvider implements SendNotificationInterface
{
    public function __construct(private SesClient $sesClient, private LoggerInterface $logger, private string $fromEmail)
    {
    }

    public function send(User $user, SendNotificationCommand $command): void
    {
        try {
            $result = $this->sesClient->sendEmail(
                [
                    'Source' => $this->fromEmail,
                    'Destination' => [
                        'ToAddresses' => [$user->getEmail()],
                    ],
                    'Message' => [
                        'Subject' => [
                            'Data' => $command->getSubject(),
                            'Charset' => 'UTF-8',
                        ],
                        'Body' => [
                            'Text' => [
                                'Data' => $command->getMessage(),
                                'Charset' => 'UTF-8',
                            ],
                        ],
                    ],
                ]
            );

            $this->logger->info('Email sent successfully via AWS SES', ['messageId' => $result->get('MessageId')]);
        } catch (\Exception $e) {
            $this->logger->error('Failed to send email', ['command' => $command]);
            throw new ProviderException('AWS SES', 'Failed to send email via AWS SES', $e);
        }
    }
}
