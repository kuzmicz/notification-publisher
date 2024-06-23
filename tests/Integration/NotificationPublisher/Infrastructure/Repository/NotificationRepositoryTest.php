<?php

namespace App\Tests\Integration\NotificationPublisher\Infrastructure\Repository;

use App\NotificationPublisher\Domain\Notification\Notification;
use App\NotificationPublisher\Domain\User\User;
use App\NotificationPublisher\Infrastructure\Repository\NotificationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class NotificationRepositoryTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private NotificationRepository $notificationRepository;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()->get('doctrine')->getManager();
        $this->notificationRepository = $this->entityManager->getRepository(Notification::class);

        $this->createSchema();
    }

    protected function tearDown(): void
    {
        $this->dropSchema();
        parent::tearDown();
        $this->entityManager->close();
    }

    private function createSchema(): void
    {
        $schemaTool = new SchemaTool($this->entityManager);
        $metadata = $this->entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool->createSchema($metadata);
    }

    private function dropSchema(): void
    {
        $schemaTool = new SchemaTool($this->entityManager);
        $metadata = $this->entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool->dropSchema($metadata);
    }

    public function testFindByUserId()
    {
        $user = new User('email@example.com', '123456789');
        $this->entityManager->persist($user);

        $notification = new Notification($user, 'email', 'Test Subject', 'Test Message', 'TestProvider', new \DateTimeImmutable());
        $this->entityManager->persist($notification);
        $this->entityManager->flush();

        $results = $this->notificationRepository->findByUserId($user);

        $this->assertCount(1, $results);
        $this->assertEquals('Test Subject', $results[0]['subject']);
    }

    public function testSave()
    {
        $user = new User('email@example.com', '123456789');
        $this->entityManager->persist($user);

        $notification = new Notification($user, 'email', 'Test Subject', 'Test Message', 'TestProvider', new \DateTimeImmutable());
        $this->notificationRepository->save($notification);

        $savedNotification = $this->entityManager->getRepository(Notification::class)->find($notification->getId());
        $this->assertNotNull($savedNotification);
        $this->assertEquals('Test Subject', $savedNotification->getSubject());
    }
}
