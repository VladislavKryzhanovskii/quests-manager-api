<?php

namespace App\Tests\Repository;

use App\Repository\UserRepository;

use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserRepositoryTest extends KernelTestCase
{
    private UserRepository $repository;

    protected function setUp(): void
    {
        $this->repository = static::getContainer()->get(UserRepository::class);
    }


    public function testGetPaginatedUsersReturnPaginatorInstance(): void
    {
        $result = $this->repository->getPaginatedUsers(page: 1, limit: 1);
        $this->assertInstanceOf(Paginator::class, $result);
    }

}