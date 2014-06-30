<?php

namespace Tchess\Controller;

//use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\DependencyInjection\ContainerAware;

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
        $session = $request->getSession();
        if ($session->get('started')) {
            throw new \LogicException('Game already started, try to stop game first.', 1);
        }

        $session->set('started', true);
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
        $session = $request->getSession();
        $sid = $session->getId();

        $room = $this->container->get('entity_manager')->getRepository('Tchess\Entity\Room')
                ->findOpenRoom($sid);

        if (empty($room)) {
            $room = $this->container->get('entity_manager')->getRepository('Tchess\Entity\Room')
                    ->createRoom($sid);
        }

        return 'User has join room with id: ' . $room->getId();
    }

}
