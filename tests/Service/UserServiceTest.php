<?php

namespace App\Tests\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\User\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Faker\Factory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class UserServiceTest extends TestCase
{
    private UserService $userService;
    private EntityManagerInterface&MockObject $entityManager;

    protected function setUp(): void
    {
        $userRepository = $this->createMock(UserRepository::class);
        $userRepository->expects($this->any())->method('getPaginatedUsers')
            ->willReturn(new Paginator($this->createMock(QueryBuilder::class)));

        $entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $entityManagerMock->method('getRepository')->willReturn($userRepository);

        $loggerMock = $this->createMock(LoggerInterface::class);

        $requestStack = new RequestStack();
        $requestStack->push(new Request(['page' => 1, 'limit' => 10]));

        $this->userService = new UserService($entityManagerMock, $loggerMock, $requestStack);
        $this->entityManager = $entityManagerMock;
    }

    public function testSaveUser(): void
    {
        $user = (new User())->setName(Factory::create()->userName());

        $this->entityManager->expects($this->once())->method('persist')->with($this->isInstanceOf(User::class));
        $this->entityManager->expects($this->once())->method('flush');

        $this->userService->save($user);
    }

    public function testDeleteUser(): void
    {
        $user = (new User())->setName(Factory::create()->userName());

        $this->entityManager->expects($this->once())->method('remove')->with($this->isInstanceOf(User::class));
        $this->entityManager->expects($this->once())->method('flush');

        $this->userService->delete($user);
    }

    public function testGetUsersPaginator(): void
    {
        $this->entityManager->expects($this->once())->method('getRepository')->with($this->isType('string'));
        $result = $this->userService->get();

        $this->assertInstanceOf(Paginator::class, $result);
    }

    public function testFindUser(): void
    {
        $userId = -1;
        $user = (new User())->setName('name');

        $this->entityManager->expects($this->once())->method('find')
            ->with($this->isType('string'), $this->isType('int'))
            ->willReturn($user);

        $this->userService->find($userId);
    }

}