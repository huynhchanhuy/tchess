<?php

/**
 * Test team
 */
class GameControllerTest extends TchessTestBase
{

    public function setUp()
    {
        $entityManager = static::$sc->get('entity_manager');
        $metadatas = $entityManager->getMetadataFactory()->getAllMetadata();
        foreach ($metadatas as $metadata) {
            $entityManager->createQuery('DELETE FROM ' . $metadata->getName())->execute();
        }
    }

    /**
     * @group start
     */
    public function testStartGameThatHasStarted()
    {
        $session = $this->joinGame();
        $session->set('started', true);

        $request = $this->getRequest('/start-game', 'POST', array(), $session);
        $response = parent::$sc->get('framework')->handle($request);

        $this->assertEquals('Game started', $response->getContent());
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
