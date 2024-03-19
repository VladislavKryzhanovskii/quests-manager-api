<?php

namespace App\Tests\Controller;

use App\Controller\UserController;
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

/**
 * @see UserController
 */
class UserControllerTest extends WebTestCase
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
     * @covers UserController::get()
     */
    public function testGetAllUsers(): void
    {
        $this->client->request(Request::METHOD_GET, '/api/users');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');
        $this->assertJson($this->client->getResponse()->getContent());

        $jsonContent = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('totalCount', $jsonContent);
        $this->assertArrayHasKey('pageCount', $jsonContent);
        $this->assertArrayHasKey('result', $jsonContent);
    }

    /**
     * @covers UserController::create()
     */
    public function testCreateUser(): void
    {
        $this->client->request(Request::METHOD_POST, '/api/users',
            server: ['Content-Type' => 'application/json'],
            content: sprintf('{"name": "%s"}', $this->faker->userName())
        );

        $this->assertEquals(Response::HTTP_CREATED, $this->client->getResponse()->getStatusCode());
        $this->assertResponseHeaderSame('Content-Type', 'application/json');
        $this->assertJson($this->client->getResponse()->getContent());

        $jsonContent = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('id', $jsonContent);

        $this->client->request(Request::METHOD_GET, "api/users/{$jsonContent['id']}");
        $this->assertNotEquals(null, json_decode($this->client->getResponse()->getContent()));

    }

    /**
     * @covers UserController::find()
     */
    public function testFindUserById(): void
    {
        /** @var AbstractExecutor $executor */
        $executor = $this->databaseTool->loadFixtures([UserFixture::class]);
        $user = $executor->getReferenceRepository()->getReference(UserFixture::REFERENCE, User::class);

        $this->client->request(Request::METHOD_GET, "/api/users/{$user->getId()}");

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertJson($this->client->getResponse()->getContent());

        $jsonContent = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('id', $jsonContent);
        $this->assertArrayHasKey('name', $jsonContent);
        $this->assertArrayHasKey('balance', $jsonContent);
        $this->assertArrayHasKey('history', $jsonContent);

        $this->assertEquals($user->getId(), $jsonContent['id']);
        $this->assertEquals($user->getName(), $jsonContent['name']);
    }

    /**
     * @covers UserController::update()
     */
    public function testUpdateUserName(): void
    {
        /** @var AbstractExecutor $executor */
        $executor = $this->databaseTool->loadFixtures([UserFixture::class]);
        $user = $executor->getReferenceRepository()->getReference(UserFixture::REFERENCE, User::class);

        $newName = $this->faker->userName();

        $this->client->request(Request::METHOD_PATCH, "/api/users/{$user->getId()}",
            server: ['Content-Type' => 'application/json'],
            content: sprintf('{"name": "%s"}', $newName)
        );

        $this->assertEquals(Response::HTTP_NO_CONTENT, $this->client->getResponse()->getStatusCode());

        $this->client->request(Request::METHOD_GET, "/api/users/{$user->getId()}");

        $jsonContent = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals($user->getId(), $jsonContent['id']);
        $this->assertEquals($newName, $jsonContent['name']);
    }

    /**
     * @covers UserController::delete()
     */
    public function testDeleteUser(): void
    {
        /** @var AbstractExecutor $executor */
        $executor = $this->databaseTool->loadFixtures([UserFixture::class]);
        /** @var User $user */
        $user = $executor->getReferenceRepository()->getReference(UserFixture::REFERENCE, User::class);
        $userId = $user->getId();

        $this->client->request(Request::METHOD_DELETE, "/api/users/$userId");
        $this->assertEquals(Response::HTTP_NO_CONTENT, $this->client->getResponse()->getStatusCode());

        $this->client->request(Request::METHOD_GET, "/api/users/$userId");


        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(null, json_decode($this->client->getResponse()->getContent()));
    }

    /**
     * @covers UserController::completeQuest()
     */
    public function testUserCompleteQuest(): void
    {
        /** @var AbstractExecutor $executor */
        $executor = $this->databaseTool->loadFixtures([UserFixture::class, QuestFixture::class]);
        $user = $executor->getReferenceRepository()->getReference(UserFixture::REFERENCE, User::class);
        $quest = $executor->getReferenceRepository()->getReference(QuestFixture::REFERENCE, Quest::class);

        $balance = $user->getBalance();

        $this->client->request(Request::METHOD_PATCH, "/api/users/{$user->getId()}/quests/{$quest->getId()}");
        $this->assertEquals(Response::HTTP_NO_CONTENT, $this->client->getResponse()->getStatusCode());

        $this->client->request(Request::METHOD_GET, "/api/users/{$user->getId()}");
        $jsonContent = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertNotEmpty($jsonContent['history']);
        $this->assertNotEquals($balance, $jsonContent['balance']);
        $this->assertEquals($quest->getId(), $jsonContent['history'][0]['id']);
        $this->assertEquals($jsonContent['balance'], $jsonContent['history'][0]['cost']);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        unset($this->databaseTool);
    }


}
