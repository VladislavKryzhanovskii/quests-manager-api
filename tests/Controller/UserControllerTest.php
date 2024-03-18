<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class UserControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    /**
     * @covers \App\Controller\UserController::get()
     */
    public function testGetAllUsers(): void
    {
        $this->client->request(Request::METHOD_GET, '/api/users/');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');
        $this->assertJson($this->client->getResponse()->getContent());

        $jsonContent = json_decode($this->client->getResponse()->getContent());
        $this->assertArrayHasKey('totalCount', $jsonContent);
        $this->assertArrayHasKey('pageCount', $jsonContent);
        $this->assertArrayHasKey('result', $jsonContent);
    }

    /**
     * @covers \App\Controller\UserController::create()
     */
    public function testCreateUser(): void
    {
        $this->client->request('POST', '/api/users/', server: ['Content-Type' => 'application/json'], content: '{"name": testUser}');
        $this->assertEquals(Response::HTTP_CREATED, $this->client->getResponse()->getStatusCode());
        $this->assertResponseHeaderSame('Content-Type', 'application/json');
        $this->assertJson($this->client->getResponse()->getContent());
        $this->assertArrayHasKey('id', json_decode($this->client->getResponse()->getContent()));
    }




}
