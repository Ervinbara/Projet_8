<?php

namespace Tests\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;

class AuthentificationControllerTest extends WebTestCase
{
    private $client = null;

    public function setUp():void
    {
        $this->client = static::createClient();
    }

    public function testNoAccessAdminPageNoAuth()
    {
        $this->client->request('GET', '/admin/users/create');
        $this->assertInstanceOf(RedirectResponse::class, $this->client->getResponse());
        // Vérifie si on retourne bien sur la page de login
        $this->assertRegExp('/\/login/',$this->client->getResponse()->headers->get('Location'));
    }

    public function testLoginWithBadCredentials()
    {
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Se connecter')->form([
            '_username' => 'john@doe.fr',
            '_password' => 'fakepassword'
        ]);
        $this->client->submit($form);
        $this->assertRegExp('/\/login/',$this->client->getResponse()->headers->get('Location'));
    }

    public function testSuccessfullLogin ()
    {
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Se connecter')->form([
            '_username' => 'ervin',
            '_password' => 'azerty'
        ]);
        $this->client->submit($form);
        // Si le formulaire est validé on est redirigée sur / (page d'acceuil)
        $this->assertRegExp('/\//',$this->client->getResponse()->headers->get('Location'));

    }

    public function testNoAccessAdminPageWhenAuthWithUserRole() {
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Se connecter')->form([
            '_username' => 'ervin',
            '_password' => 'azerty'
        ]);
        $this->client->submit($form);
        // Si le formulaire est validé on est redirigée sur / (page d'accueil)
        $this->assertRegExp('/\//',$this->client->getResponse()->headers->get('Location'));
        // On essai d'accèder à une page liée à l'admin
        $crawler = $this->client->request('GET', '/admin/users/create');
        // Si on est connecter et essai d'accéder à une page de l'admin on est redirigé sur la homepage
        $this->assertSame('Bienvenue sur Todo List, l\'application vous permettant de gérer l\'ensemble de vos tâches sans effort !', $crawler->filter('h1')->text());
    }

    /**
     * Test Logout - Redirect login
     */
    public function testLogoutAuthUser()
    {
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Se connecter')->form([
            '_username' => 'ervin',
            '_password' => 'azerty'
        ]);
        $this->client->submit($form);
        // Si le formulaire est validé on est redirigée sur / (page d'accueil)
        $this->assertRegExp('/\//',$this->client->getResponse()->headers->get('Location'));
        // Déconnection
        $crawler = $this->client->request('GET', '/logout');
        // Une fois déconnecter on retourne sur la page de login
        $this->assertRegExp('/\/login/',$this->client->getResponse()->headers->get('Location'));
    }

}