<?php

namespace App\NotificationPublisher\Domain\User;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: "App\NotificationPublisher\Infrastructure\Repository\UserRepository")]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    private string $email;

    #[ORM\Column(name: 'phone_number', type: 'string', length: 20, unique: true)]
    private string $phoneNumber;

    public function __construct(string $email, string $phoneNumber)
    {
        $this->email = $email;
        $this->phoneNumber = $phoneNumber;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPhoneNumber(): string
    {
        return $this->phoneNumber;
    }
}
