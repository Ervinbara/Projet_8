<?php

namespace Tests\AppBundle\Traits;

use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

trait LoginTest
{
    public function getClientUser()
    {
        $client = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'ervin',
            'PHP_AUTH_PW'   => 'azerty',
        ));
        $this->login($client);
        return $client;
    }

    public function getClientAdmin()
    {
        $client = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'a',
            'PHP_AUTH_PW'   => 'azerty',
        ));
        $this->login($client);
        return $client;
    }

    public function getClientNoAuth()
    {
        return static::createClient();
    }

    public function login($client)
    {
        $session = $client->getContainer()->get('session');

        $firewallName = 'secure_area';
        // if you don't define multiple connected firewalls, the context defaults to the firewall name
        // See https://symfony.com/doc/current/reference/configuration/security.html#firewall-context
        $firewallContext = 'secured_area';

        // you may need to use a different token class depending on your application.
        // for example, when using Guard authentication you must instantiate PostAuthenticationGuardToken
//        $token = new UsernamePasswordToken('a', null, $firewallName, ['ROLE_ADMIN']);
        $token = new UsernamePasswordToken('', null, $firewallName);
        $session->set('_security_'.$firewallContext, serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $client->getCookieJar()->set($cookie);
    }
}