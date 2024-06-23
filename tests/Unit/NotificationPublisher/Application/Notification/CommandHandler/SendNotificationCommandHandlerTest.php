<?php

namespace App\Tests\Unit\NotificationPublisher\Application\Notification\CommandHandler;

use App\NotificationPublisher\Application\Notification\Command\SendNotificationCommand;
use App\NotificationPublisher\Application\Notification\CommandHandler\SendNotificationCommandHandler;
use App\NotificationPublisher\Application\Notification\Service\NotificationProviderFactory;
use App\NotificationPublisher\Domain\Notification\Exception\NotificationSendFailedException;
use App\NotificationPublisher\Domain\Notification\NotificationRepositoryInterface;
use App\NotificationPublisher\Domain\Notification\Service\SendNotificationInterface;
use App\NotificationPublisher\Domain\User\User;
use App\NotificationPublisher\Domain\User\UserRepositoryInterface;
use App\NotificationPublisher\Infrastructure\Exception\ProviderException;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class SendNotificationCommandHandlerTest extends TestCase
{
    private $userRepositoryMock;
    private $providerFactoryMock;
    private $notificationRepositoryMock;
    private $loggerMock;
    private $handler;

    protected function setUp(): void
    {
        $this->userRepositoryMock = $this->createMock(UserRepositoryInterface::class);
        $this->providerFactoryMock = $this->createMock(NotificationProviderFactory::class);
        $this->notificationRepositoryMock = $this->createMock(NotificationRepositoryInterface::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);

        $this->handler = new SendNotificationCommandHandler(
            $this->notificationRepositoryMock,
            $this->providerFactoryMock,
            $this->userRepositoryMock,
            $this->loggerMock
        );
    }

    public function testHandleSendNotificationCommandSuccessfully()
    {
        $user = new User(1, '123321123');
        $this->userRepositoryMock->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($user);

        $providerMock = $this->createMock(SendNotificationInterface::class);
        $this->providerFactoryMock->expects($this->once())
            ->method('getProviders')
            ->with('channel_name')
            ->willReturn([$providerMock]);

        $providerMock->expects($this->once())
            ->method('send');

        $this->notificationRepositoryMock->expects($this->once())
            ->method('save');

        $command = new SendNotificationCommand(1, 'channel_name', 'subject', 'message');
        $this->handler->__invoke($command);
    }

    public function testHandleUserNotFound()
    {
        $this->userRepositoryMock->expects($this->once())
            ->method('findById')
            ->willReturn(null);

        $command = new SendNotificationCommand(321, 'channel_name', 'subject', 'message');

        $this->loggerMock->expects($this->once())
            ->method('error')
            ->with('User not found', ['userId' => 321]);

        $this->handler->__invoke($command);
    }

    public function testHandleSendNotificationFailure()
    {
        $this->expectException(NotificationSendFailedException::class);
        $user = new User(1, '123321123');
        $this->userRepositoryMock->expects($this->once())
            ->method('findById')
            ->willReturn($user);

        $providerMock = $this->createMock(SendNotificationInterface::class);
        $providerMock->expects($this->once())
            ->method('send')
            ->willThrowException(new ProviderException('MockProvider', 'Sending failed'));

        $this->providerFactoryMock->expects($this->once())
            ->method('getProviders')
            ->willReturn([$providerMock]);

        $loggerCallCount = 0;
        $this->loggerMock->expects($this->exactly(2))->method('error');

        $command = new SendNotificationCommand(1, 'channel_name', 'subject', 'message');
        $this->handler->__invoke($command);
    }
}
