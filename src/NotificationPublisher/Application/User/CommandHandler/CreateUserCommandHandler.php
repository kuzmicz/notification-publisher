<?php

namespace App\NotificationPublisher\Application\User\CommandHandler;

use App\NotificationPublisher\Application\User\Command\CreateUserCommand;
use App\NotificationPublisher\Domain\User\User;
use App\NotificationPublisher\Domain\User\UserRepositoryInterface;

class CreateUserCommandHandler
{
    public function __construct(private UserRepositoryInterface $userRepository)
    {
    }

    public function __invoke(CreateUserCommand $command): int
    {
        $user = new User($command->getEmail(), $command->getPhoneNumber());
        $this->userRepository->save($user);

        return $user->getId();
    }
}
