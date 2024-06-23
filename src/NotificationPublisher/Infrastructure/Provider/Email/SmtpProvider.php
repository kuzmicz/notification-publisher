<?php

namespace App\NotificationPublisher\Infrastructure\Provider\Email;

use App\NotificationPublisher\Application\Notification\Command\SendNotificationCommand;
use App\NotificationPublisher\Domain\Notification\Service\SendNotificationInterface;
use App\NotificationPublisher\Domain\User\User;
use App\NotificationPublisher\Infrastructure\Exception\ProviderException;
use PHPMailer\PHPMailer\PHPMailer;
use Psr\Log\LoggerInterface;

class SmtpProvider implements SendNotificationInterface
{
    public function __construct(
        private PHPMailer $mailer,
        private LoggerInterface $logger,
        private string $host,
        private string $username,
        private string $password,
        private string $encryption,
        private int $port,
        private string $fromEmail,
        private string $fromName
    ) {
        $this->mailer->isSMTP();
        $this->mailer->Host = $host;
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = $username;
        $this->mailer->Password = $password;
        $this->mailer->SMTPSecure = $encryption;
        $this->mailer->Port = $port;
        $this->mailer->setFrom($fromEmail, $fromName);
    }

    public function send(User $user, SendNotificationCommand $command): void
    {
        try {
            $this->mailer->addAddress($user->getEmail());
            $this->mailer->Subject = $command->getSubject();
            $this->mailer->Body = $command->getMessage();
            $this->mailer->send();

            $this->logger->info('Email sent successfully via SMTP');
        } catch (\Exception $e) {
            $this->logger->error('Failed to send email via SMTP', ['command' => $command]);
            throw new ProviderException('SMTP', 'Failed to send email via SMTP', $e);
        }
    }
}
