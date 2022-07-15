<?php

namespace Tests\AppBundle\Controller;

use AppBundle\Entity\Task;
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
        $client = $this->getClientNoAuth();
        $client->request('GET', '/tasksDone');
        $this->assertSame(Response::HTTP_FOUND, $client->getResponse()->getStatusCode());
        $this->assertRegExp('/\/login/',$client->getResponse()->headers->get('Location'));
    }


    // Test Task Create correct data and bad data
    /**
     * Test Create Task with correct data - Redirect tasks to do list & Flash success
     */
    public function testCreateTaskFullData()
    {
        $client = $this->getClientUser();
        $crawler = $client->request('GET', '/tasks/create');
        $form = $crawler->selectButton('Ajouter')->form([
            'task[title]' => 'ervin',
            'task[content]' => 'azerty'
        ]);
        $client->submit($form);

        $this->assertRegExp('/\/tasks/',$client->getResponse()->headers->get('Location'));
    }

    /**
     * Test Create Task with correct data - Redirect tasks to do list & Flash success
     */
    public function testCreateTaskEmptyData()
    {
        $client = $this->getClientUser();
        $crawler = $client->request('GET', '/tasks/create');
        $form = $crawler->selectButton('Ajouter')->form([
            'task[title]' => null,
            'task[content]' => 'Bonjour'
        ]);
        $client->submit($form);

        $this->assertRegExp('/\/tasks/create/',$client->getResponse()->headers->get('Location'));
    }

    // Delete
    /**
     * Test user delete his own task
     */
    public function testDeleteTask() {
        $client = $this->getClientUser();

        $task = $client->getContainer()->get('doctrine')->getRepository(Task::class)->findOneBy(['title' => 'ervin']);
        $taskId = $task->getId();

        $client->request('GET', '/tasks/'. $taskId . '/delete');
        $this->assertRegExp('/\/tasks/',$client->getResponse()->headers->get('Location'));

    }

    /**
     * Test task delete no auth
     */
    public function testDeleteTaskNoAuth() {
        $client = $this->getClientNoAuth();

        $task = $client->getContainer()->get('doctrine')->getRepository(Task::class)->findOneBy(['title' => 'ervin']);
        $taskId = $task->getId();

        $client->request('GET', '/tasks/'. $taskId . '/delete');
        $this->assertRegExp('/\/login/',$client->getResponse()->headers->get('Location'));

    }

    /**
     * User trying to delete a task that does not belong to him
     */
    public function testDeleteTaskNoAuthorUser() {
        $client = $this->getClientAdmin();

        $task = $client->getContainer()->get('doctrine')->getRepository(Task::class)->findOneBy(['title' => 'ervin']);
        $taskId = $task->getId();

        $client->request('GET', '/tasks/'. $taskId . '/delete');
        $this->assertRegExp('/\/tasks/',$client->getResponse()->headers->get('Location'));

    }

}