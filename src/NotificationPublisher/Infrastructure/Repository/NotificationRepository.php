<?php

namespace App\NotificationPublisher\Infrastructure\Repository;

use App\NotificationPublisher\Domain\Notification\Notification;
use App\NotificationPublisher\Domain\Notification\NotificationRepositoryInterface;
use App\NotificationPublisher\Domain\User\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class NotificationRepository extends ServiceEntityRepository implements NotificationRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Notification::class);
    }

    public function findByUserId(User $user): array
    {
        $qb = $this->createQueryBuilder('n')
            ->innerJoin('n.user', 'u')
            ->addSelect('u')
            ->where('n.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getArrayResult();

        $notifications = [];
        foreach ($qb as $result) {
            $notification = [
                'notificationId' => $result['id'],
                'channel' => $result['channel'],
                'subject' => $result['subject'],
                'message' => $result['message'],
                'providerClass' => $result['provider'],
                'sentAt' => $result['sentAt']->format('Y-m-d H:i:s'),
                'toEmail' => $result['user']['email'],
                'toPhoneNumber' => $result['user']['phoneNumber'],
            ];
            $notifications[] = $notification;
        }

        return $notifications;
    }

    public function save(Notification $notification): void
    {
        $entityManager = $this->getEntityManager();
        $entityManager->persist($notification);
        $entityManager->flush();
    }
}
