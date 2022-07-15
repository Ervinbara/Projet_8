<?php

namespace Tests\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Tests\AppBundle\Traits\LoginTest;

class UserControllerTest extends WebTestCase
{
    use LoginTest;

    public function testAccessCreateUserAuthUser()
    {
        $client = $this->getClientUser();
        $crawler = $client->request('GET', '/admin/users/create');
        $this->assertSame('Bienvenue sur Todo List, l\'application vous permettant de gérer l\'ensemble de vos tâches sans effort !', $crawler->filter('h1')->text());
    }

    public function testCreateUserAuthAdminFullData()
    {
        $client = $this->getClientAdmin();
        $crawler = $client->request('GET', '/admin/users/create');
        $form = $crawler->selectButton('Ajouter')->form([
            'user[username]' => 'Benitoo',
            'user[password][first]' => 'Bonjour',
            'user[password][second]' => 'Bonjour',
            'user[email]' => 'benitoo@gmail.com',
        ]);
        $client->submit($form);

//        var_dump($client->getResponse()->headers->get('Location'));
        $this->assertRegExp('/\/users/',$client->getResponse()->headers->get('Location'));
    }

    public function testCreateUserAuthAdminEmptyData()
    {
        $client = $this->getClientAdmin();
        $crawler = $client->request('GET', '/admin/users/create');
        $form = $crawler->selectButton('Ajouter')->form([
            'user[username]' => null,
            'user[password][first]' => 'Bonjour',
            'user[password][second]' => 'Bonjour',
            'user[email]' => 'beniiitoo@gmail.com',
        ]);
        $client->submit($form);

        var_dump($client->getResponse()->headers->get('Location'));
//        $this->assertSelectorExists('.help-block');
        $this->assertRegExp('/\/admin/users/create/',$client->getResponse()->headers->get('Location'));
    }

    public function testAccessCreateUserNoAuthUser()
    {
        $client = $this->getClientNoAuth();
        $client->request('GET', '/admin/users/create');
        $this->assertRegExp('/\/login/',$client->getResponse()->headers->get('Location'));
    }
}