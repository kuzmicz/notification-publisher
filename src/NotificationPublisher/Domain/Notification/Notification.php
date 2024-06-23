<?php

namespace App\NotificationPublisher\Domain\Notification;

use App\NotificationPublisher\Domain\User\User;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;

#[ORM\Entity(repositoryClass: "App\NotificationPublisher\Infrastructure\Repository\NotificationRepository")]
class Notification
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private int $id;

    #[ManyToOne(targetEntity: User::class)]
    #[JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false)]
    private User $user;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $channel;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $subject;

    #[ORM\Column(type: Types::TEXT)]
    private string $message;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $provider;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeInterface $sentAt;

    public function __construct(User $user, string $channel, ?string $subject, string $message, string $provider, \DateTimeInterface $sentAt)
    {
        $this->user = $user;
        $this->channel = $channel;
        $this->subject = $subject;
        $this->message = $message;
        $this->provider = $provider;
        $this->sentAt = $sentAt;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getChannel(): string
    {
        return $this->channel;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getProvider(): string
    {
        return $this->provider;
    }

    public function getSentAt(): \DateTimeInterface
    {
        return $this->sentAt;
    }
}
