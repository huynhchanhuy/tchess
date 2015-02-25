<?php

namespace Tchess\Tests\Controller;

use Tchess\Tests\WebTestBase;

/**
 * Test GameController.
 */
class GameControllerTest extends WebTestBase
{

    /**
     * Test registration.
     */
    public function testRegistration()
    {
        // Not register.
        $client = static::createClient();
        $crawler = $client->request('GET', '/');
        $this->assertTrue($client->getResponse()->isRedirect('/register'));

        // Registered.
        $crawler = $client->request('GET', '/register');
        $form = $crawler->selectButton('register')->form(array(
            'form[name]' => 'My Name'
        ));
        $crawler = $client->submit($form);
        $this->assertTrue($client->getResponse()->isRedirect('/rooms'));
    }

    /**
     * Test invalid move.
     */
    public function testInvalidMove()
    {

    }

    /**
     * Test valid move.
     */
    public function testValidMove()
    {

    }

}
