<?php

namespace App\Tests\Unit\NotificationPublisher\Infrastructure\Provider\Email;

use App\NotificationPublisher\Application\Notification\Command\SendNotificationCommand;
use App\NotificationPublisher\Domain\User\User;
use App\NotificationPublisher\Infrastructure\Exception\ProviderException;
use App\NotificationPublisher\Infrastructure\Provider\Email\SmtpProvider;
use PHPMailer\PHPMailer\Exception as PHPMailerException;
use PHPMailer\PHPMailer\PHPMailer;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class SmtpProviderTest extends TestCase
{
    private $mailer;
    private $logger;
    private $provider;
    private $user;
    private $command;

    protected function setUp(): void
    {
        $this->mailer = $this->createMock(PHPMailer::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->provider = new SmtpProvider(
            $this->mailer,
            $this->logger,
            'smtp.example.com',
            'user@example.com',
            'password',
            'tls',
            587,
            'no-reply@example.com',
            'Example Sender'
        );

        $this->user = $this->createMock(User::class);
        $this->user->method('getEmail')->willReturn('recipient@example.com');

        $this->command = $this->createMock(SendNotificationCommand::class);
        $this->command->method('getSubject')->willReturn('Subject');
        $this->command->method('getMessage')->willReturn('Message body');
    }

    public function testSendEmailSuccessfully()
    {
        $this->mailer->expects($this->once())->method('send')->willReturn(true);
        $this->logger->expects($this->once())->method('info');
        $this->provider->send($this->user, $this->command);
    }

    public function testSendEmailThrowsException()
    {
        $this->expectException(ProviderException::class);
        $this->mailer->expects($this->once())
            ->method('send')
            ->willThrowException(new PHPMailerException());

        $this->logger->expects($this->once())->method('error');
        $this->provider->send($this->user, $this->command);
    }
}
