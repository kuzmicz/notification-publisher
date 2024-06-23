<?php

namespace App\Tests\Integration\NotificationPublisher\Infrastructure\Repository;

use App\NotificationPublisher\Domain\User\User;
use App\NotificationPublisher\Infrastructure\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserRepositoryTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private UserRepository $userRepository;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()->get('doctrine')->getManager();
        $this->userRepository = $this->entityManager->getRepository(User::class);

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

    public function testFindById()
    {
        $user = new User('email@example.com', '123456789');
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $foundUser = $this->userRepository->findById($user->getId());

        $this->assertNotNull($foundUser);
        $this->assertEquals($user->getEmail(), $foundUser->getEmail());
        $this->assertEquals($user->getPhoneNumber(), $foundUser->getPhoneNumber());
    }

    public function testSave()
    {
        $user = new User('email@example.com', '123456789');
        $this->userRepository->save($user);

        $savedUser = $this->entityManager->getRepository(User::class)->find($user->getId());
        $this->assertNotNull($savedUser);
        $this->assertEquals('email@example.com', $savedUser->getEmail());
        $this->assertEquals('123456789', $savedUser->getPhoneNumber());
    }
}
