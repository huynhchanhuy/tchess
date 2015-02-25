<?php

namespace Tchess\Tests\Controller;

use Tchess\Tests\WebTestBase;

/**
 * Test RoomController.
 */
class RoomControllerTest extends WebTestBase
{
    protected $player;

    protected function setUp()
    {
        $this->player = static::createClient();
        $crawler = $this->player->request('GET', '/register');
        $form = $crawler->selectButton('register')->form(array(
            'form[name]' => 'Player'
        ));
        $this->player->submit($form);
    }

    /**
     * Tests no room has been created.
     */
    public function testNoRoomHasBeenCreated()
    {
        $crawler = $this->player->request('GET', '/rooms');
        $this->assertEquals(0, $crawler->filter('a:contains("Watch")')->count());
        $this->assertEquals(0, $crawler->filter('a:contains("Join")')->count());
        $this->assertEquals(1, $crawler->filter('a:contains("Create room")')->count());
        $this->assertEquals(0, $crawler->filter('a:contains("Go to your room")')->count());
        $this->assertEquals(0, $crawler->filter('a:contains("Leave room")')->count());
    }

    /**
     * Tests a room has been created.
     */
    public function testARoomHasBeenCreated()
    {
        // Create a room.
        $crawler = $this->player->request('GET', '/rooms');
        $link = $crawler->selectLink('Create room')->link();
        $this->player->click($link);
        $this->assertTrue($this->player->getResponse()->isRedirect('/'));

        $crawler = $this->player->request('GET', '/rooms');
        $this->assertEquals(1, $crawler->filter('a:contains("Go to your room")')->count());
        $this->assertEquals(1, $crawler->filter('a:contains("Leave room")')->count());
        $this->assertEquals(1, $crawler->filter('html:contains("Current Room")')->count());
        $this->assertEquals(0, $crawler->filter('a:contains("Watch")')->count());
        $this->assertEquals(0, $crawler->filter('a:contains("Join")')->count());
    }

}
