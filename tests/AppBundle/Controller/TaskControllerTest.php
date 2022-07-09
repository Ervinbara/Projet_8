<?php

namespace Tests\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Tests\AppBundle\Traits\LoginTest;

class TaskControllerTest extends WebTestCase
{
    use LoginTest;

    /**
     * User Auth, access to tasks list
     */
    public function testTasksAccessAuthUser()
    {
        $client = $this->getClientUser();
        $client->request('GET', '/tasks');
        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());
    }

    /**
     * No Auth, access to tasks list
     */
    public function testTasksAccessNoAuth()
    {
        $client = $this->getClientNoAuth();
        $client->request('GET', '/tasks');
        $this->assertSame(Response::HTTP_FOUND, $client->getResponse()->getStatusCode());
        $this->assertRegExp('/\/login/',$client->getResponse()->headers->get('Location'));
    }

    /**
     * User Auth, access to tasks done list
     */
    public function testTasksDoneAccessAuth()
    {
        $client = $this->getClientUser();
        $client->request('GET', '/tasksDone');
        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());
    }

    /**
     * No Auth, access to tasks done list
     */
    public function testTasksDoneAccessNoAuth()
    {
        $this->client->request('GET', '/tasks');
        $this->assertSame(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());
        $this->assertRegExp('/\/login/',$this->client->getResponse()->headers->get('Location'));
    }


    // Test Task Create correct data and bad data
    /**
     * Test Create Task with correct data - Redirect tasks to do list & Flash success
     */
    public function testCreateTaskFullData()
    {
        $crawler = $this->client->request('GET', '/tasks/create');
        $form = $crawler->selectButton('Se connecter')->form([
            'task[title]' => 'ervin',
            'task[content]' => 'azerty'
        ]);
        $this->client->submit($form);

        $this->assertRegExp('/\/tasks/',$this->client->getResponse()->headers->get('Location'));
        $this->assertSame('Superbe ! La tâche a été bien été ajoutée.', $crawler->filter('div.alert alert-success')->text());
    }

    /**
     * Test Create Task with correct data - Redirect tasks to do list & Flash success
     */
    public function testCreateTaskEmptyData()
    {
        $crawler = $this->client->request('GET', '/tasks/create');
        $form = $crawler->selectButton('Se connecter')->form([
            'task[title]' => null,
            'task[content]' => 'Bonjour'
        ]);
        $this->client->submit($form);

        $this->assertRegExp('/\/tasks/create/',$this->client->getResponse()->headers->get('Location'));
    }

    // Delete

}