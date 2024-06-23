<?php

namespace App\Tests\Unit\NotificationPublisher\Infrastructure\Provider\Sms;

use App\NotificationPublisher\Application\Notification\Command\SendNotificationCommand;
use App\NotificationPublisher\Domain\User\User;
use App\NotificationPublisher\Infrastructure\Exception\ProviderException;
use App\NotificationPublisher\Infrastructure\Provider\Sms\TwilioProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Twilio\Rest\Api\V2010\Account\MessageInstance;
use Twilio\Rest\Api\V2010\Account\MessageList;
use Twilio\Rest\Client;

class TwilioProviderTest extends TestCase
{
    private $twilioClient;
    private $logger;
    private $messageList;
    private $user;
    private $command;
    private $provider;
    private $fromPhoneNumber = '+1234567890';

    protected function setUp(): void
    {
        $this->twilioClient = $this->createMock(Client::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->messageList = $this->createMock(MessageList::class);
        $this->user = $this->createMock(User::class);
        $this->command = $this->createMock(SendNotificationCommand::class);
        $this->provider = new TwilioProvider($this->twilioClient, $this->fromPhoneNumber, $this->logger);
        $this->twilioClient->method('__get')->with('messages')->willReturn($this->messageList);
        $this->user->method('getPhoneNumber')->willReturn('0987654321');
        $this->command->method('getMessage')->willReturn('Test message');
    }

    public function testSendSmsSuccessfully()
    {
        $this->messageList->expects($this->once())
            ->method('create')
            ->with(
                '+0987654321',
                [
                    'from' => $this->fromPhoneNumber,
                    'body' => 'Test message',
                ]
            )
            ->willReturn($this->createMock(MessageInstance::class));

        $this->logger->expects($this->once())->method('info');

        $this->provider->send($this->user, $this->command);
    }

    public function testSendSmsThrowsException()
    {
        $this->expectException(ProviderException::class);

        $this->messageList->expects($this->once())
            ->method('create')
            ->with(
                '+0987654321',
                [
                    'from' => $this->fromPhoneNumber,
                    'body' => 'Test message',
                ]
            )
            ->willThrowException(new ProviderException('Failed to send SMS via Twilio'));

        $this->logger->expects($this->once())->method('error');

        $this->provider->send($this->user, $this->command);
    }
}
