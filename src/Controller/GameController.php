<?php

namespace Tchess\Controller;

//use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\DependencyInjection\ContainerAware;
use Tchess\Entity\Player;
use Tchess\Entity\Room;
use Tchess\GameEvents;
use Tchess\Event\GameEvent;
use Tchess\MoveEvents;
use Tchess\Event\MoveEvent;
use Tchess\Entity\Piece\Move;
use Tchess\Entity\Board;

class GameController extends ContainerAware
{

    /**
     * Start game.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return string
     */
    public function startAction(Request $request)
    {
        $em = $this->container->get('entity_manager');
        $session = $request->getSession();
        $sid = $session->getId();

        $player = $em->getRepository('Tchess\Entity\Player')->findOneBy(array('sid' => $sid));

        if (empty($player) || !$player instanceof Player) {
            throw new \LogicException('Player did not join a room.', 3);
        }

        if ($player->getStarted()) {
            throw new \LogicException('Game has been already started.', 1);
        }

        $player->setStarted(true);
        $em->flush();

        $this->container->get('dispatcher')->dispatch(GameEvents::START, new GameEvent($player, $em));
        return 'Game started';
    }

    /**
     * Stop game.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return string
     */
    public function stopAction(Request $request)
    {
        $em = $this->container->get('entity_manager');
        $session = $request->getSession();
        $sid = $session->getId();

        $player = $em->getRepository('Tchess\Entity\Player')->findOneBy(array('sid' => $sid));

        if (empty($player) || !$player instanceof Player) {
            throw new \LogicException('Player did not join a room.', 3);
        }

        if (!$player->getStarted()) {
            throw new \LogicException('Game is not started.', 1);
        }

        $player->setStarted(false);
        $em->flush();

        $this->container->get('dispatcher')->dispatch(GameEvents::STOP, new GameEvent($player, $em));
        return 'Game stopped';
    }

    /**
     * Restart game.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return string
     */
    public function restartAction(Request $request)
    {
        $em = $this->container->get('entity_manager');
        $session = $request->getSession();
        $sid = $session->getId();

        $player = $em->getRepository('Tchess\Entity\Player')->findOneBy(array('sid' => $sid));

        if (empty($player) || !$player instanceof Player) {
            throw new \LogicException('Player did not join a room.', 3);
        }

        if ($player->getStarted()) {
            $stop_sub_request = Request::create('/stop-game');
            $stop_sub_request->setSession($session);
            $this->container->get('framework')->handle($stop_sub_request, HttpKernelInterface::SUB_REQUEST);

            $start_sub_request = Request::create('/start-game');
            $start_sub_request->setSession($session);
            $this->container->get('framework')->handle($start_sub_request, HttpKernelInterface::SUB_REQUEST);
        } else {
            $sub_request = Request::create('/start-game');
            $sub_request->setSession($session);
            $this->container->get('framework')->handle($sub_request, HttpKernelInterface::SUB_REQUEST);
        }

        $player = $em->getRepository('Tchess\Entity\Player')->findOneBy(array('sid' => $sid));
        if ($player->getStarted()) {

            $this->container->get('dispatcher')->dispatch(GameEvents::RESTART, new GameEvent($player, $em));
            return 'Game re-started';
        } else {
            return 'There is unknown error while re-starting game';
        }
    }

    /**
     * Join game.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return string
     */
    public function joinAction(Request $request)
    {
        $em = $this->container->get('entity_manager');
        $session = $request->getSession();
        $sid = $session->getId();

        $player = $em->getRepository('Tchess\Entity\Player')->findOneBy(array('sid' => $sid));

        if (!empty($player) && $player instanceof Player && !empty($player->getRoom())) {
            throw new \LogicException('Player has already joined a room.', 3);
        }

        if (empty($player)) {
            $player = new Player();
            $player->setSid($sid);
            $player->setStarted(false);
            // Default color for all player, will be updated later.
            $player->setColor('white');
            $em->persist($player);
        }

        $room = $em->getRepository('Tchess\Entity\Room')
                ->findOpenRoom();

        if (empty($room)) {
            $room = new Room();
            $room->addPlayer($player);
            $em->persist($room);
        } else {
            $players = $room->getPlayers();
            if (count($players) == 1 && $players[0]->getColor() == 'white' && $players[0]->getSid() != $player->getSid()) {
                $player->setColor('black');
                $room->addPlayer($player);
            }
        }
        $em->flush();

        return 'User has join room with id: ' . $room->getId();
    }

    /**
     * Move a piece.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return string
     */
    public function moveAction(Request $request)
    {
        $em = $this->container->get('entity_manager');
        $session = $request->getSession();
        $sid = $session->getId();

        $player = $em->getRepository('Tchess\Entity\Player')->findOneBy(array('sid' => $sid));

        if (empty($player) || ($player instanceof Player && empty($player->getRoom()))) {
            throw new \LogicException('Player did not join a room', 3);
        }

        $game = $player->getRoom()->getGame();

        if (empty($game) || ($game instanceof Game && !$game->getStarted())) {
            throw new \LogicException('Opponent Player did not started', 4);
        } else {
            $move = new Move($request->request->get('move'));
            $color = $request->request->get('color');
            if (!in_array($color, array('white', 'black'))) {
                throw new \InvalidArgumentException('Color is invalid. It must be "white" or "black".');
            }
            if (!$this->isValidMove($game->getBoard(), $move, $color)) {
                throw new \LogicException('Your move is not valid', 4);
            } else {
                $this->performMove($game->getBoard(), $move, $color);
            }
        }

        return 'Move has been performed. Waiting for you opponent.';
    }

    /**
     * Check for valid move.
     *
     * @return boolean
     */
    public function isValidMove(Board $board, $move, $color)
    {
        $dispatcher = $this->container->get('dispatcher');
        $event = new MoveEvent($board, $move, $color);
        $dispatcher->dispatch(MoveEvents::CHECH_MOVE, $event);
        return $event->isValidMove();
    }

    /**
     * Actually move.
     */
    public function performMove(Board $board, $move, $color)
    {
        $dispatcher = $this->container->get('dispatcher');
        $event = new MoveEvent($board, $move, $color);
        $dispatcher->dispatch(MoveEvents::MOVE, $event);
    }

}
