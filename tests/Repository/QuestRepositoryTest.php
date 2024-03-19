<?php

namespace App\Tests\Repository;

use App\Repository\QuestRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class QuestRepositoryTest extends KernelTestCase
{
    private QuestRepository $repository;

    protected function setUp(): void
    {
        $this->repository = static::getContainer()->get(QuestRepository::class);
    }


    public function testGetPaginatedQuestsReturnPaginatorInstance(): void
    {
        $result = $this->repository->getPaginatedQuests(page: 1, limit: 1);
        $this->assertInstanceOf(Paginator::class, $result);
    }
}