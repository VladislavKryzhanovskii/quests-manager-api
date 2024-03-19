<?php

namespace App\Tests\Service;

use App\Entity\Quest;
use App\Repository\QuestRepository;
use App\Service\Quest\QuestService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class QuestServiceTest extends TestCase
{

    private QuestService $questService;
    private EntityManagerInterface&MockObject $entityManager;

    protected function setUp(): void
    {
        $questRepository = $this->createMock(QuestRepository::class);
        $questRepository->expects($this->any())->method('getPaginatedQuests')
            ->willReturn(new Paginator($this->createMock(QueryBuilder::class)));

        $entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $entityManagerMock->method('getRepository')->willReturn($questRepository);

        $loggerMock = $this->createMock(LoggerInterface::class);

        $requestStack = new RequestStack();
        $requestStack->push(new Request(['page' => 1, 'limit' => 10]));

        $this->questService = new QuestService($entityManagerMock, $loggerMock, $requestStack);
        $this->entityManager = $entityManagerMock;
    }

    public function testSaveQuest(): void
    {
        $quest = (new Quest())
            ->setName('testQuest_' . time())
            ->setCost(mt_rand(1, 100));

        $this->entityManager->expects($this->once())->method('persist')->with($this->isInstanceOf(Quest::class));
        $this->entityManager->expects($this->once())->method('flush');

        $this->questService->save($quest);
    }

    public function testFindQuest(): void
    {
        $questId = -1;
        $quest = (new Quest())
            ->setName('testQuest_' . time())
            ->setCost(mt_rand(1, 100));


        $this->entityManager->expects($this->once())->method('find')
            ->with($this->isType('string'), $this->isType('int'))
            ->willReturn($quest);

        $this->questService->find($questId);
    }


    public function testDeleteQuest(): void
    {
        $quest = (new Quest())
            ->setName('testQuest_' . time())
            ->setCost(mt_rand(1, 100));

        $this->entityManager->expects($this->once())->method('remove')->with($this->isInstanceOf(Quest::class));
        $this->entityManager->expects($this->once())->method('flush');

        $this->questService->delete($quest);
    }

    public function testGetQuestsPaginator(): void
    {
        $this->entityManager->expects($this->once())->method('getRepository')->with($this->isType('string'));
        $result = $this->questService->get();

        $this->assertInstanceOf(Paginator::class, $result);
    }

}