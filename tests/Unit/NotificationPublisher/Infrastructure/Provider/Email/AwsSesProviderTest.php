<?php

namespace App\Tests\Unit\NotificationPublisher\Infrastructure\Provider\Email;

use App\NotificationPublisher\Application\Notification\Command\SendNotificationCommand;
use App\NotificationPublisher\Domain\User\User;
use App\NotificationPublisher\Infrastructure\Exception\ProviderException;
use App\NotificationPublisher\Infrastructure\Provider\Email\AwsSesProvider;
use Aws\CommandInterface;
use Aws\Exception\AwsException;
use Aws\Result;
use Aws\Ses\SesClient;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class AwsSesProviderTest extends TestCase
{
    private MockObject $sesClient;
    private MockObject $logger;
    private AwsSesProvider $provider;
    private MockObject $user;
    private MockObject $command;
    private string $fromEmail = 'no-reply@example.com';

    protected function setUp(): void
    {
        $this->sesClient = $this->getMockBuilder(SesClientProxy::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['sendEmail'])
            ->getMock();
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->provider = new AwsSesProvider(
            $this->sesClient,
            $this->logger,
            $this->fromEmail
        );

        $this->user = $this->createMock(User::class);
        $this->user->method('getEmail')->willReturn('recipient@example.com');

        $this->command = $this->createMock(SendNotificationCommand::class);
        $this->command->method('getSubject')->willReturn('Subject');
        $this->command->method('getMessage')->willReturn('Message body');
    }

    public function testSendEmailSuccessfully()
    {
        $result = $this->createMock(Result::class);
        $result->method('get')->with('MessageId')->willReturn('12345');

        $this->sesClient->expects($this->once())
            ->method('sendEmail')
            ->with($this->callback(function ($params) {
                return $params['Source'] === $this->fromEmail
                    && 'recipient@example.com' === $params['Destination']['ToAddresses'][0]
                    && 'Subject' === $params['Message']['Subject']['Data']
                    && 'Message body' === $params['Message']['Body']['Text']['Data'];
            }))
            ->willReturn($result);

        $this->logger->expects($this->once())->method('info');

        $this->provider->send($this->user, $this->command);
    }

    public function testSendEmailThrowsException()
    {
        $this->expectException(ProviderException::class);
        $awsException = new AwsException('AWS SES Error', $this->createMock(CommandInterface::class));

        $this->sesClient->expects($this->once())
            ->method('sendEmail')
            ->willThrowException($awsException);
        $this->logger->expects($this->once())->method('error');
        $this->provider->send($this->user, $this->command);
    }
}

class SesClientProxy extends SesClient
{
    public function sendEmail(array $params)
    {
    }
}
