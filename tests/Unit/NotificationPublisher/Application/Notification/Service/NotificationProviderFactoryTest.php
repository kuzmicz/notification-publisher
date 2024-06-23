<?php

namespace App\Tests\Unit\NotificationPublisher\Application\Notification\Service;

use App\NotificationPublisher\Application\Notification\Service\NotificationProviderFactory;
use App\NotificationPublisher\Infrastructure\Provider\Email\AwsSesProvider;
use App\NotificationPublisher\Infrastructure\Provider\Email\SmtpProvider;
use App\NotificationPublisher\Infrastructure\Provider\Sms\TwilioProvider;
use PHPUnit\Framework\TestCase;

class NotificationProviderFactoryTest extends TestCase
{
    private NotificationProviderFactory $factory;
    private AwsSesProvider $awsSesProvider;
    private SmtpProvider $smtpProvider;
    private TwilioProvider $twilioProvider;

    protected function setUp(): void
    {
        $notificationConfig = [
            'channels' => [
                'email' => [
                    'providers' => ['aws_ses', 'smtp'],
                ],
                'sms' => [
                    'providers' => ['twilio'],
                ],
            ],
        ];

        $this->awsSesProvider = $this->createMock(AwsSesProvider::class);
        $this->smtpProvider = $this->createMock(SmtpProvider::class);
        $this->twilioProvider = $this->createMock(TwilioProvider::class);

        $this->factory = new NotificationProviderFactory(
            $notificationConfig,
            $this->awsSesProvider,
            $this->smtpProvider,
            $this->twilioProvider
        );
    }

    public function testGetProvidersForEmailChannel()
    {
        $providers = $this->factory->getProviders('email');

        $this->assertCount(2, $providers);
        $this->assertSame($this->awsSesProvider, $providers[0]);
        $this->assertSame($this->smtpProvider, $providers[1]);
    }

    public function testGetProvidersForSmsChannel()
    {
        $providers = $this->factory->getProviders('sms');

        $this->assertCount(1, $providers);
        $this->assertSame($this->twilioProvider, $providers[0]);
    }

    public function testGetProvidersThrowsExceptionForUnknownProvider()
    {
        $notificationConfig = [
            'channels' => [
                'email' => [
                    'providers' => ['unknown_provider'],
                ],
            ],
        ];

        $factory = new NotificationProviderFactory(
            $notificationConfig,
            $this->awsSesProvider,
            $this->smtpProvider,
            $this->twilioProvider
        );

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Unknown provider unknown_provider');

        $factory->getProviders('email');
    }

    public function testGetProvidersThrowsExceptionForUnknownChannel()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Unknown channel unknown_channel');

        $this->factory->getProviders('unknown_channel');
    }
}
