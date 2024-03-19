<?php

namespace App\Tests\Controller;

use App\DataFixtures\QuestFixture;
use App\DataFixtures\UserFixture;
use App\Entity\Quest;
use App\Entity\User;
use Doctrine\Common\DataFixtures\Executor\AbstractExecutor;
use Faker\Factory;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class QuestControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private \Faker\Generator $faker;

    private AbstractDatabaseTool $databaseTool;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->faker = Factory::create();
        $this->databaseTool = static::getContainer()->get(DatabaseToolCollection::class)->get();
    }

    /**
     * @covers \App\Controller\QuestController::get()
     */
    public function testGetAllUsers(): void
    {
        $this->client->request(Request::METHOD_GET, '/api/quests');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');
        $this->assertJson($this->client->getResponse()->getContent());

        $jsonContent = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('totalCount', $jsonContent);
        $this->assertArrayHasKey('pageCount', $jsonContent);
        $this->assertArrayHasKey('result', $jsonContent);
    }

    public function testFindQuestById(): void
    {
        /** @var AbstractExecutor $executor */
        $executor = $this->databaseTool->loadFixtures([QuestFixture::class]);
        $quest = $executor->getReferenceRepository()->getReference(QuestFixture::REFERENCE, Quest::class);

        $this->client->request(Request::METHOD_GET, "/api/quests/{$quest->getId()}");

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $this->assertJson($this->client->getResponse()->getContent());

        $jsonContent = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('id', $jsonContent);
        $this->assertArrayHasKey('name', $jsonContent);
        $this->assertArrayHasKey('cost', $jsonContent);
        $this->assertArrayHasKey('users', $jsonContent);

        $this->assertEquals($quest->getId(), $jsonContent['id']);
        $this->assertEquals($quest->getName(), $jsonContent['name']);
        $this->assertEquals($quest->getCost(), $jsonContent['cost']);
    }

    public function testSaveQuest(): void
    {
        $this->client->request(Request::METHOD_POST, '/api/quests',
            server: ['Content-Type' => 'application/json'],
            content: sprintf('{"name": "testQuest_%d", "cost": %d}', time(), mt_rand(100, 200))
        );

        $this->assertEquals(Response::HTTP_CREATED, $this->client->getResponse()->getStatusCode());
        $this->assertJson($this->client->getResponse()->getContent());

        $jsonContent = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('id', $jsonContent);

        $this->client->request(Request::METHOD_GET, "api/quests/{$jsonContent['id']}");
        $this->assertNotEquals(null, json_decode($this->client->getResponse()->getContent()));
    }

    public function testDeleteQuestWithoutUsersRelation(): void
    {
        /** @var AbstractExecutor $executor */
        $executor = $this->databaseTool->loadFixtures([QuestFixture::class]);
        /** @var Quest $quest */
        $quest = $executor->getReferenceRepository()->getReference(QuestFixture::REFERENCE, Quest::class);
        $questId = $quest->getId();

        $this->client->request(Request::METHOD_DELETE, "/api/quests/$questId");
        $this->assertEquals(Response::HTTP_NO_CONTENT, $this->client->getResponse()->getStatusCode());

        $this->client->request(Request::METHOD_GET, "/api/quests/$questId");

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(null, json_decode($this->client->getResponse()->getContent()));
    }

    public function testDeleteQuestWithUsersRelationThrowsError(): void
    {
        /** @var AbstractExecutor $executor */
        $executor = $this->databaseTool->loadFixtures([QuestFixture::class, UserFixture::class]);
        /** @var Quest $quest */
        $quest = $executor->getReferenceRepository()->getReference(QuestFixture::REFERENCE, Quest::class);
        /** @var User $user */
        $user = $executor->getReferenceRepository()->getReference(UserFixture::REFERENCE, User::class);

        /** Add relation */
        $this->client->request(Request::METHOD_PATCH, "/api/users/{$user->getId()}/quests/{$quest->getId()}");

        $this->client->request(Request::METHOD_DELETE, "/api/quests/{$quest->getId()}");

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(
            ['message' => 'Невозможно удалять задачи с существующей историей выполнения.'],
            json_decode($this->client->getResponse()->getContent(), true)
        );
    }

    public function testUpdateQuestWithoutUserRelation(): void
    {
        /** @var AbstractExecutor $executor */
        $executor = $this->databaseTool->loadFixtures([QuestFixture::class, UserFixture::class]);
        /** @var Quest $quest */
        $quest = $executor->getReferenceRepository()->getReference(QuestFixture::REFERENCE, Quest::class);

        $oldName = $quest->getName();
        $oldCost = $quest->getCost();
        $newName = $quest->getName() . '_updated';
        $newCost = $quest->getCost() + mt_rand(1, 100);


        $this->client->request(Request::METHOD_PUT, "/api/quests/{$quest->getId()}",
            server: ['Content-Type' => 'application/json'],
            content: sprintf('{"name": "%s", "cost": %d}', $newName, $newCost)
        );

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertJson($this->client->getResponse()->getContent());

        $jsonContent = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('id', $jsonContent);
        $this->assertEquals($quest->getId(), $jsonContent['id']);

        $this->assertNotEquals($oldCost, $quest->getCost());
        $this->assertNotEquals($oldName, $quest->getName());

        $this->assertEquals($newCost, $quest->getCost());
        $this->assertEquals($newName, $quest->getName());
    }

    public function testUpdateUserWithUserRelationWillFailedWithBadRequest(): void
    {
        /** @var AbstractExecutor $executor */
        $executor = $this->databaseTool->loadFixtures([QuestFixture::class, UserFixture::class]);
        /** @var Quest $quest */
        $quest = $executor->getReferenceRepository()->getReference(QuestFixture::REFERENCE, Quest::class);
        /** @var User $user */
        $user = $executor->getReferenceRepository()->getReference(UserFixture::REFERENCE, User::class);

        /** Add relation */
        $this->client->request(Request::METHOD_PATCH, "/api/users/{$user->getId()}/quests/{$quest->getId()}");

        $this->client->request(Request::METHOD_PUT, "/api/quests/{$quest->getId()}",
            server: ['Content-Type' => 'application/json'],
            content: sprintf('{"name": "%s", "cost": %d}', $quest->getName() . '_update', $quest->getCost() + mt_rand(1, 100))
        );

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(
            ['message' => 'Невозможно изменять задачи с существующей историей выполнения.'],
            json_decode($this->client->getResponse()->getContent(), true)
        );
    }

    public function testUpdateQuestMethodWillCreateNewResourceIfPassedQuestDoesNotExist(): void
    {
        /** @var AbstractExecutor $executor */
        $executor = $this->databaseTool->loadFixtures([QuestFixture::class, UserFixture::class]);
        /** @var Quest $quest */
        $quest = $executor->getReferenceRepository()->getReference(QuestFixture::REFERENCE, Quest::class);

        /** assert if new ID is 1 more than the previous */
        $newId = $quest->getId() + 1;
        $newName = $quest->getName() . '_updated';
        $newCost = $quest->getCost() + mt_rand(1, 100);

        $this->client->request(Request::METHOD_PUT, "/api/quests/$newId",
            server: ['Content-Type' => 'application/json'],
            content: sprintf('{"name": "%s", "cost": %d}', $newName, $newCost)
        );

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertEquals($newId, json_decode($this->client->getResponse()->getContent())->id);
    }

    public function testUpdateQuestThrowsErrorOnNotCompleteRequestBody()
    {
        /** @var AbstractExecutor $executor */
        $executor = $this->databaseTool->loadFixtures([QuestFixture::class, UserFixture::class]);
        /** @var Quest $quest */
        $quest = $executor->getReferenceRepository()->getReference(QuestFixture::REFERENCE, Quest::class);

        /** try to update only quest name */
        $this->client->request(Request::METHOD_PUT, "/api/quests/{$quest->getId()}",
            server: ['Content-Type' => 'application/json'],
            content: sprintf('{"name": "%s"}', $quest->getName() . '_updated')
        );

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
    }

    public function testPatchQuest(): void
    {
        /** @var AbstractExecutor $executor */
        $executor = $this->databaseTool->loadFixtures([QuestFixture::class]);
        /** @var Quest $quest */
        $quest = $executor->getReferenceRepository()->getReference(QuestFixture::REFERENCE, Quest::class);

        $oldName = $quest->getName();
        $newName = $quest->getName() . '_updated';

        $this->client->request(Request::METHOD_PATCH, "/api/quests/{$quest->getId()}",
            server: ['Content-Type' => 'application/json'],
            content: sprintf('{"name": "%s"}', $newName)
        );

        $this->assertEquals(Response::HTTP_NO_CONTENT, $this->client->getResponse()->getStatusCode());

        $this->assertNotEquals($oldName, $quest->getName());
        $this->assertEquals($newName, $quest->getName());

    }

    protected function tearDown(): void
    {
        parent::tearDown();
        unset($this->databaseTool);
    }

}