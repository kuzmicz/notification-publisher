<?php

namespace App\NotificationPublisher\Infrastructure\Provider\Sms;

use App\NotificationPublisher\Application\Notification\Command\SendNotificationCommand;
use App\NotificationPublisher\Domain\Notification\Service\SendNotificationInterface;
use App\NotificationPublisher\Domain\User\User;
use App\NotificationPublisher\Infrastructure\Exception\ProviderException;
use Psr\Log\LoggerInterface;
use Twilio\Rest\Client;

readonly class TwilioProvider implements SendNotificationInterface
{
    public function __construct(
        private Client $twilioClient,
        private string $fromPhoneNumber,
        private LoggerInterface $logger
    ) {
    }

    public function send(User $user, SendNotificationCommand $command): void
    {
        try {
            $message = $this->twilioClient->messages->create(
                '+'.$user->getPhoneNumber(),
                [
                    'from' => $this->fromPhoneNumber,
                    'body' => $command->getMessage(),
                ]
            );

            $this->logger->info('SMS sent successfully via Twilio', ['messageSid' => $message->sid]);
        } catch (\Exception $e) {
            $this->logger->error('Failed to send SMS via Twilio', ['command' => $command]);
            throw new ProviderException('Twilio', 'Failed to send email via Twilio', $e);
        }
    }
}
