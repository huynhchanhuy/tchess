<?php

namespace Tchess\Controller;

//use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class GameController
{

    private $framework;

    public function __construct(HttpKernelInterface $framework)
    {
        $this->framework = $framework;
    }

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
            $this->framework->handle($stop_sub_request, HttpKernelInterface::SUB_REQUEST);

            $start_sub_request = Request::create('/start-game');
            $start_sub_request->setSession($session);
            $this->framework->handle($start_sub_request, HttpKernelInterface::SUB_REQUEST);
        } else {
            $sub_request = Request::create('/start-game');
            $sub_request->setSession($session);
            $this->framework->handle($sub_request, HttpKernelInterface::SUB_REQUEST);
        }

        if ($session->get('started')) {
            return 'Game re-started';
        }
        else {
            return 'There is unknown error while re-starting game';
        }
    }

}
