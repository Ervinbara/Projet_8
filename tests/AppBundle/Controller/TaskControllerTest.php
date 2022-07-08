<?php

namespace Tests\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use tests\AppBundle\Traits\loginTest;

class TaskControllerTest extends WebTestCase
{
    private $client = null;

    public function setUp():void
    {
        $this->client = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'a',
            'PHP_AUTH_PW'   => 'azercxty',
        ));
        $this->login();
    }

    /**
     * User Auth, access to tasks list
     */
    public function testTasksAccessAuthUser()
    {
        $this->client->request('GET', '/tasks');
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
//        var_dump($d);
        $this->assertRegExp('/\/tasks/',$this->client->getResponse()->headers->get('Location'));
    }

    public function testTasksAccessNoAuth()
    {
        $this->client->request('GET', '/tasks');
        $this->assertSame(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());
        $this->assertRegExp('/\/login/',$this->client->getResponse()->headers->get('Location'));
    }

    public function testTasksDoneAccessAuth()
    {
        $this->client->request('GET', '/tasksDone');
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertRegExp('/\/tasksDone/',$this->client->getResponse()->headers->get('Location'));
    }

    public function testTasksDoneAccessNoAuth()
    {
        $this->client->request('GET', '/tasks');
        $this->assertSame(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());
        $this->assertRegExp('/\/login/',$this->client->getResponse()->headers->get('Location'));
    }


    // Test Task Create correct data and bad data
    // Delete

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
}