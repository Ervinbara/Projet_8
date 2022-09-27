<?php

namespace Tests\AppBundle\Controller;

use AppBundle\Entity\Task;
use AppBundle\Entity\User;
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

    public function testAccessCreateUserNoAuthUser()
    {
        $client = $this->getClientNoAuth();
        $client->request('GET', '/admin/users/create');
        $this->assertRegExp('/\/login/',$client->getResponse()->headers->get('Location'));
    }

    public function testAccessCreateUserAuthAdmin()
    {
        $client = $this->getClientAdmin();
        $crawler = $client->request('GET', '/admin/users/create');
        $this->assertSame('Créer un utilisateur', $crawler->filter('h1')->text());

    }

    public function testCreateUserAuthAdminFullData()
    {
        $client = $this->getClientAdmin();
        $crawler = $client->request('GET', '/admin/users/create');
        $form = $crawler->selectButton('Ajouter')->form([
            'user[username]' => 'Besssnicsdcdxstoo',
            'user[password][first]' => 'Bonjour',
            'user[password][second]' => 'Bonjour',
            'user[email]' => 'bensxsxsxsitoo@gmail.com',
        ]);
        $client->submit($form);

        $this->assertRegExp('/\/users/',$client->getResponse()->headers->get('Location'));
    }

    // TODO : Ajouter un test sur le sur la classe .help-block lors de la montée de version symfony
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
//        $this->assertSelectorExists('.help-block');
        $this->assertNull($client->getResponse()->headers->get('Location'));
    }

    // TODO : Ajouter un test sur le sur la classe .help-block lors de la montée de version symfony
    public function testCreateUserAuthAdminWithAlreadyExistData()
    {
        $client = $this->getClientAdmin();
        $crawler = $client->request('GET', '/admin/users/create');
        $form = $crawler->selectButton('Ajouter')->form([
            'user[username]' => 'ervin',
            'user[password][first]' => 'Bonjour',
            'user[password][second]' => 'Bonjour',
            'user[email]' => 'beniiitoo@gmail.com',
        ]);
        $client->submit($form);
        var_dump($client->getResponse()->getContent());
//        $this->assertSame('This value is already used.', $crawler->filter('.glyphicon-exclamation-sign')->text());
//        $this->assertSelectorExists('.help-block');
        $this->assertNull($client->getResponse()->headers->get('Location'));
    }

    public function testUpdateUserAuthAdminFullData()
    {
        $client = $this->getClientAdmin();
        $formData = ['user_edit[username]' => 'Benixsqxtoqao',
            'user_edit[email]' => 'bwa@gmail.com'];
//        $username = $client->getContainer()->get('doctrine')->getRepository(User::class)->findOneById('21')->getUsername();
        $crawler = $client->request('GET', '/admin/users/21/edit');
        $form = $crawler->selectButton('Modifier')->form();
        $form['user_edit[username]'] = 'symfonyfan';
        $form['user_edit[email]'] = 'anypass@gmail.com';
        $form->disableValidation();
        $form->setValues(['user_edit' => [
            'roles' => ['ROLE_ADMIN','ROLE_USER'] // it uses the input value to determine which checkbox to tick
        ]]);
        $client->submit($form);
        $this->assertRegExp('/\/users/',$client->getResponse()->headers->get('Location'));
    }

    // TODO : Ajouter un test sur le sur la classe .help-block lors de la montée de version symfony
    public function testUpdateUserAuthAdminEmptyData()
    {
        $client = $this->getClientAdmin();
        $crawler = $client->request('GET', '/admin/users/21/edit');
        $form = $crawler->selectButton('Modifier')->form([
            'user_edit[username]' => '',
            'user_edit[email]' => 'b@gmail.com',
        ]);
        $form->disableValidation();
        $form->setValues(['user_edit' => [
            'roles' => ['ROLE_ADMIN','ROLE_USER'] // it uses the input value to determine which checkbox to tick
        ]]);
        $crawler = $client->submit($form);

//        var_dump($client->getResponse()->getContent());
        // Faire un test si le contenu du champs est vide

        $help = $crawler->filter('.help-block');
//        var_dump($help->count());
////        var_dump($client->getResponse()->getContent());
//        var_dump($crawler->html());
//        $this->assertSelectorExists('.help-block');
        $this->assertEquals(1, $help->count());
        $this->assertNull($client->getResponse()->headers->get('Location'));
    }
}