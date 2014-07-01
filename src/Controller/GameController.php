<?php

namespace Tchess\Controller;

//use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\DependencyInjection\ContainerAware;

use Tchess\Entity\Player;
use Tchess\Entity\Room;

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

        if (!empty($player) && $player->getStarted()) {
            throw new \LogicException('Game has been already started.', 1);
        }

        if (empty($player)) {
            throw new \LogicException('Player did not join a room.', 3);
        }

        $player->setStarted(true);
        $em->flush();
        return 'Game started';
    }

    /**
     * Stop game.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function stopAction(Request $request)
    {
        $session = $request->getSession();
        if (!$session->get('started')) {
            throw new \LogicException('Game is not started, try to start game first.', 2);
        }

        $session->set('started', false);
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
        $session = $request->getSession();

        if ($session->get('started')) {
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

        if ($session->get('started')) {
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

        if (!empty($player) && !empty($player->getRoom())) {
            return 'Player has join a room.';
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
        }
        else {
            $players = $room->getPlayers();
            if (count($players) == 1 && $players[0]->getColor() == 'white' && $players[0]->getSid() != $player->getSid()) {
                $player->setColor('black');
                $room->addPlayer($player);
            }
        }
        $em->flush();

        return 'User has join room with id: ' . $room->getId();
    }

}
