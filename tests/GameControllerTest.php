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
     * @group join
     * @expectedException \LogicException
     * @expectedExceptionMessage Player did not join a room.
     * @expectedExceptionCode 3
     */
    public function testStartGameWithoutJoiningRoom()
    {
        $request = $this->getRequest('/start-game', 'POST', array());
        parent::$sc->get('framework')->handle($request);
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
     * @expectedException \LogicException
     * @expectedExceptionMessage Game is not started.
     * @expectedExceptionCode 1
     */
    public function testStopGameThatHasNotStarted()
    {
        $request = $this->getRequest('/join-game', 'POST');
        parent::$sc->get('framework')->handle($request);

        $request = $this->getRequest('/stop-game', 'POST', array(), $request->getSession());
        parent::$sc->get('framework')->handle($request);
    }

    /**
     * @group stop
     */
    public function testStopGameThatHasStarted()
    {
        $request = $this->getRequest('/join-game', 'POST');
        parent::$sc->get('framework')->handle($request);

        $request = $this->getRequest('/start-game', 'POST', array(), $request->getSession());
        parent::$sc->get('framework')->handle($request);

        $request = $this->getRequest('/stop-game', 'POST', array(), $request->getSession());
        $response = parent::$sc->get('framework')->handle($request);

        $this->assertEquals('Game stopped', $response->getContent());
    }

    /**
     * @group restart
     */
    public function testRestartGameThatHasNotStarted()
    {
        $request = $this->getRequest('/join-game', 'POST');
        parent::$sc->get('framework')->handle($request);

        $request = $this->getRequest('/restart-game', 'POST', array(), $request->getSession());
        $response = parent::$sc->get('framework')->handle($request);

        $this->assertEquals('Game re-started', $response->getContent());
    }

    /**
     * @group restart
     */
    public function testRestartGameThatHasStarted()
    {
        $request = $this->getRequest('/join-game', 'POST');
        parent::$sc->get('framework')->handle($request);

        $request = $this->getRequest('/start-game', 'POST', array(), $request->getSession());
        parent::$sc->get('framework')->handle($request);

        $request = $this->getRequest('/restart-game', 'POST', array(), $request->getSession());
        $response = parent::$sc->get('framework')->handle($request);

        $this->assertEquals('Game re-started', $response->getContent());
    }

}
