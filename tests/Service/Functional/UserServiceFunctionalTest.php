<?php

namespace App\Tests\Service\Functional;

use App\DataFixtures\UserFixture;
use App\Entity\User;
use App\Service\User\UserService;
use Doctrine\Common\DataFixtures\Executor\AbstractExecutor;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Faker\Factory;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class UserServiceFunctionalTest extends KernelTestCase
{
    private UserService $userService;

    private EntityManagerInterface $entityManager;

    private AbstractDatabaseTool $databaseTool;

    protected function setUp(): void
    {
        $this->databaseTool = static::getContainer()->get(DatabaseToolCollection::class)->get();


        $this->entityManager = static::bootKernel()->getContainer()->get('doctrine')->getManager();
        $logger = $this->createMock(LoggerInterface::class);

        $requestStack = new RequestStack();
        $requestStack->push(new Request(['page' => 1, 'limit' => 10]));

        $this->userService = new UserService($this->entityManager, $logger, $requestStack);
    }

    public function testSaveUser(): void
    {
        $user = (new User())->setName(Factory::create()->userName());

        $this->userService->save($user);

        $this->assertNotNull($user->getId());
        $this->assertNotNull($this->entityManager->find(User::class, $user->getId()));
    }

    public function testDeleteUser(): void
    {
        $user = (new User())->setName(Factory::create()->userName());
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $userId = $user->getId();

        $this->userService->delete($user);

        $this->assertNull($this->entityManager->find(User::class, $userId));
    }

    public function testFindUser(): void
    {
        /** @var AbstractExecutor $executor */
        $executor = $this->databaseTool->loadFixtures([UserFixture::class]);
        $user = $executor->getReferenceRepository()->getReference(UserFixture::REFERENCE, User::class);

        $newUser = $this->userService->find($user->getId());

        $this->assertEquals($user->getId(), $newUser->getId());
    }

}