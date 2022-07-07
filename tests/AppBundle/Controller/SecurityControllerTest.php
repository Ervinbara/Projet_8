<?php

namespace Tests\AppBundle\Controller;

use AppBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Guard\Token\PostAuthenticationGuardToken;

class SecurityControllerTest extends WebTestCase
{
    private $client = null;

    public function setUp():void
    {
        $this->client = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'a',
            'PHP_AUTH_PW'   => 'azerty',
        ));
        $this->login();
    }

    public function testLogin()
    {
        $crawler = $this->client->request('GET', '/');

        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSame('Bienvenue sur Todo List, l\'application vous permettant de gérer l\'ensemble de vos tâches sans effort !', $crawler->filter('h1')->text());
    }

    private function login()
    {
        $session = $this->client->getContainer()->get('session');

        $firewallName = 'secure_area';
        // if you don't define multiple connected firewalls, the context defaults to the firewall name
        // See https://symfony.com/doc/current/reference/configuration/security.html#firewall-context
        $firewallContext = 'secured_area';

        // you may need to use a different token class depending on your application.
        // for example, when using Guard authentication you must instantiate PostAuthenticationGuardToken
        $token = new UsernamePasswordToken('a', null, $firewallName, ['ROLE_ADMIN']);
        $session->set('_security_'.$firewallContext, serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);
    }

    public function testTasksAuthAccess()
    {
        $this->client->request('GET', '/tasks');
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Test d'accès à une page liée à l'administration
     */
    public function testAccessToAdminPage()
    {
        $crawler = $this->client->request('GET', '/admin/users');
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSame('Liste des utilisateurs', $crawler->filter('h1')->text());
    }

    /**
     * Test access to Create User page with No Auth User - Redirect login & Flash error
     */


}