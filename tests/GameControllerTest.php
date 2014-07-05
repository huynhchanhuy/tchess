<?php

/**
 * Test team
 */
class GameControllerTest extends TchessTestBase
{

    public function tearDown()
    {
        $entityManager = static::$sc->get('entity_manager');
        $metadatas = $entityManager->getMetadataFactory()->getAllMetadata();
        foreach ($metadatas as $metadata) {
            $entityManager->createQuery('DELETE FROM ' . $metadata->getName())->execute();
        }

        // Delete doctrine's cache.
        $entityManager->clear();
    }

    /**
     * @group start
     * @expectedException \LogicException
     * @expectedExceptionMessage Game has been already started.
     * @expectedExceptionCode 1
     */
    public function testStartGameThatHasStarted()
    {
        $request = $this->getRequest('/join-game', 'POST');
        parent::$sc->get('framework')->handle($request);

        $request = $this->getRequest('/start-game', 'POST', array(), $request->getSession());
        parent::$sc->get('framework')->handle($request);

        $request = $this->getRequest('/start-game', 'POST', array(), $request->getSession());
        parent::$sc->get('framework')->handle($request);
    }

    /**
     * @group start
     */
    public function testStartGameThatHasNotStarted()
    {
        $request = $this->getRequest('/join-game', 'POST');
        parent::$sc->get('framework')->handle($request);

        $request = $this->getRequest('/start-game', 'POST', array(), $request->getSession());
        $response = parent::$sc->get('framework')->handle($request);

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
