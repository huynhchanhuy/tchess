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
     * @expectedExceptionMessage Player did not join a room
     * @expectedExceptionCode \Tchess\ExceptionCodes::PLAYER
     */
    public function testStartGameWithoutJoiningRoom()
    {
        $request = $this->getRequest('/start-game', 'POST', array());
        parent::$sc->get('framework')->handle($request);
    }

    /**
     * @group start
     * @expectedException \LogicException
     * @expectedExceptionMessage Game has been already started
     * @expectedExceptionCode \Tchess\ExceptionCodes::PLAYER
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
     * @expectedExceptionMessage Player did not join a room
     * @expectedExceptionCode \Tchess\ExceptionCodes::PLAYER
     */
    public function testStopGameWithoutJoiningRoom()
    {
        $request = $this->getRequest('/stop-game', 'POST', array());
        parent::$sc->get('framework')->handle($request);
    }

    /**
     * @group stop
     * @expectedException \LogicException
     * @expectedExceptionMessage Game is not started
     * @expectedExceptionCode \Tchess\ExceptionCodes::PLAYER
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
     * @expectedException \LogicException
     * @expectedExceptionMessage Player did not join a room
     * @expectedExceptionCode \Tchess\ExceptionCodes::PLAYER
     */
    public function testRestartGameWithoutJoiningRoom()
    {
        $request = $this->getRequest('/restart-game', 'POST', array());
        parent::$sc->get('framework')->handle($request);
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

    /**
     * @group join
     */
    public function testJoinGameOnce()
    {
        $request = $this->getRequest('/join-game', 'POST', array());
        $response = parent::$sc->get('framework')->handle($request);

        $this->assertEquals('Player has been joined a room', $response->getContent());
    }

    /**
     * @group join
     * @expectedException \LogicException
     * @expectedExceptionMessage Player has already joined a room
     * @expectedExceptionCode \Tchess\ExceptionCodes::PLAYER
     */
    public function testJoinGameTwice()
    {
        $request = $this->getRequest('/join-game', 'POST', array());
        parent::$sc->get('framework')->handle($request);

        $request = $this->getRequest('/join-game', 'POST', array(), $request->getSession());
        parent::$sc->get('framework')->handle($request);
    }

    /**
     * @group move
     * @expectedException \LogicException
     * @expectedExceptionMessage Player did not join a room
     * @expectedExceptionCode \Tchess\ExceptionCodes::PLAYER
     */
    public function testMovePieceWithoutJoiningRoom()
    {
        $request = $this->getRequest('/piece-move', 'POST', array(
            'move' => 'a2 a3'
        ));
        parent::$sc->get('framework')->handle($request);
    }

    /**
     * @group move
     * @expectedException \LogicException
     * @expectedExceptionMessage Opponent player did not start the game
     * @expectedExceptionCode \Tchess\ExceptionCodes::PLAYER
     */
    public function testWaitingForOpponent()
    {
        $request = $this->getRequest('/join-game', 'POST');
        parent::$sc->get('framework')->handle($request);

        $request = $this->getRequest('/start-game', 'POST', array(), $request->getSession());
        parent::$sc->get('framework')->handle($request);

        $request = $this->getRequest('/piece-move', 'POST', array(
            'move' => 'a2 a3'
        ), $request->getSession());
        $response = parent::$sc->get('framework')->handle($request);
    }

    /**
     * @group move
     * @expectedException \LogicException
     * @expectedExceptionMessage Move is not valid
     * @expectedExceptionCode \Tchess\ExceptionCodes::PLAYER
     */
    public function testInvalidMove()
    {
        $request_opponent = $this->getRequest('/join-game', 'POST');
        parent::$sc->get('framework')->handle($request_opponent);

        $request_opponent = $this->getRequest('/start-game', 'POST', array(), $request_opponent->getSession());
        parent::$sc->get('framework')->handle($request_opponent);

        $request = $this->getRequest('/join-game', 'POST');
        parent::$sc->get('framework')->handle($request);

        $request = $this->getRequest('/start-game', 'POST', array(), $request->getSession());
        parent::$sc->get('framework')->handle($request);

        $request = $this->getRequest('/piece-move', 'POST', array(
            'move' => 'b2 b7'
        ), $request->getSession());
        $response = parent::$sc->get('framework')->handle($request);
    }

}
