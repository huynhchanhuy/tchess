<?php

namespace Tchess\Tests\Controller;

use Tchess\Tests\WebTestBase;

/**
 * Test RoomController.
 */
class RoomControllerTest extends WebTestBase
{

    /**
     * Both players do not registered.
     */
    public function testNotRegister()
    {
        $crawler = static::$client->request('GET', '/rooms');

        $this->assertEquals(0, $crawler->filter('a:contains("Watch")')->count());
    }

}
