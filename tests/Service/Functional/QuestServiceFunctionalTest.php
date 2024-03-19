<?php

namespace App\Tests\Service\Functional;

use App\DataFixtures\QuestFixture;

use App\Entity\Quest;
use App\Service\Quest\QuestService;
use Doctrine\Common\DataFixtures\Executor\AbstractExecutor;
use Doctrine\ORM\EntityManagerInterface;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class QuestServiceFunctionalTest extends KernelTestCase
{

    private QuestService $questService;

    private EntityManagerInterface $entityManager;

    private AbstractDatabaseTool $databaseTool;

    protected function setUp(): void
    {
        $this->databaseTool = static::getContainer()->get(DatabaseToolCollection::class)->get();


        $this->entityManager = static::bootKernel()->getContainer()->get('doctrine')->getManager();
        $logger = $this->createMock(LoggerInterface::class);

        $requestStack = new RequestStack();
        $requestStack->push(new Request(['page' => 1, 'limit' => 10]));

        $this->questService = new QuestService($this->entityManager, $logger, $requestStack);
    }

    public function testSaveQuest(): void
    {
        $quest = (new Quest())
            ->setName('testQuest_' . time())
            ->setCost(mt_rand(1, 100));
        $this->questService->save($quest);

        $this->assertNotNull($quest->getId());
        $this->assertNotNull($this->entityManager->find(Quest::class, $quest->getId()));
    }

    public function testDeleteQuest(): void
    {
        $quest = (new Quest())
            ->setName('testQuest_' . time())
            ->setCost(mt_rand(1, 100));
        $this->entityManager->persist($quest);
        $this->entityManager->flush();

        $questId = $quest->getId();

        $this->questService->delete($quest);

        $this->assertNull($this->entityManager->find(Quest::class, $questId));
    }

    public function testFindQuest(): void
    {
        /** @var AbstractExecutor $executor */
        $executor = $this->databaseTool->loadFixtures([QuestFixture::class]);
        $quest = $executor->getReferenceRepository()->getReference(QuestFixture::REFERENCE, Quest::class);

        $newQuest = $this->questService->find($quest->getId());

        $this->assertEquals($quest->getId(), $newQuest->getId());
    }


}