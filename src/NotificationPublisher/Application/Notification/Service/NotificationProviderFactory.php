<?php

namespace App\NotificationPublisher\Application\Notification\Service;

use App\NotificationPublisher\Infrastructure\Provider\Email\AwsSesProvider;
use App\NotificationPublisher\Infrastructure\Provider\Email\SmtpProvider;
use App\NotificationPublisher\Infrastructure\Provider\Sms\TwilioProvider;

readonly class NotificationProviderFactory
{
    public function __construct(
        private array $notificationConfig,
        private AwsSesProvider $awsSesProvider,
        private SmtpProvider $smtpProvider,
        private TwilioProvider $twilioProvider
    ) {
    }

    public function getProviders(string $channel): array
    {
        if (!isset($this->notificationConfig['channels'][$channel])) {
            throw new \Exception("Unknown channel $channel");
        }

        $providers = $this->notificationConfig['channels'][$channel]['providers'];
        $providerInstances = [];

        foreach ($providers as $provider) {
            $providerInstances[] = match ($provider) {
                'aws_ses' => $this->awsSesProvider,
                'smtp' => $this->smtpProvider,
                'twilio' => $this->twilioProvider,
                default => throw new \Exception("Unknown provider $provider"),
            };
        }

        return $providerInstances;
    }
}
