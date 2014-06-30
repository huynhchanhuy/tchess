<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

/**
 * Test team
 */
class GameControllerTest extends PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        $env = 'test';
        $this->sc = include __DIR__ . '/../src/container.php';
    }

    private function getRequest($path, $method = 'GET', $content = null)
    {
        $request = Request::create($path, $method, array(), array(), array(), array(), $content);
        $session = new Session(new MockArraySessionStorage());
        $request->setSession($session);
        $this->sc->get('context')->fromRequest($request);

        return $request;
    }

    /**
     * @group start
     */
    public function testStartGameThatHasStarted()
    {
        $request = $this->getRequest('/start-game', 'POST');
        $request->getSession()->set('started', true);

        $response = $this->sc->get('framework')->handle($request);

        $content = json_decode($response->getContent());
        $this->assertEquals(1, $content->code);
        $this->assertEquals('Something went wrong! (Game already started, try to stop game first.)', $content->message);
    }

    /**
     * @group start
     */
    public function testStartGameThatHasNotStarted()
    {
        $request = $this->getRequest('/start-game', 'POST');
        $request->getSession()->set('started', false);

        $response = $this->sc->get('framework')->handle($request);

        $this->assertEquals('Game started', $response->getContent());
    }

    /**
     * @group stop
     */
    public function testStopGameThatHasNotStarted()
    {
        $request = $this->getRequest('/stop-game', 'POST');
        $request->getSession()->set('started', false);

        $response = $this->sc->get('framework')->handle($request);

        $content = json_decode($response->getContent());
        $this->assertEquals(2, $content->code);
        $this->assertEquals('Something went wrong! (Game is not started, try to start game first.)', $content->message);
    }

    /**
     * @group stop
     */
    public function testStopGameThatHasStarted()
    {
        $request = $this->getRequest('/stop-game', 'POST');
        $request->getSession()->set('started', true);

        $response = $this->sc->get('framework')->handle($request);

        $this->assertEquals('Game stopped', $response->getContent());
    }

    /**
     * @group restart
     */
    public function testRestartGameThatHasNotStarted()
    {
        $request = $this->getRequest('/restart-game', 'POST');
        $request->getSession()->set('started', false);

        $response = $this->sc->get('framework')->handle($request);

        $this->assertEquals('Game re-started', $response->getContent());
    }

    /**
     * @group restart
     */
    public function testRestartGameThatHasStarted()
    {
        $request = $this->getRequest('/restart-game', 'POST');
        $request->getSession()->set('started', true);

        $response = $this->sc->get('framework')->handle($request);

        $this->assertEquals('Game re-started', $response->getContent());
    }

}
