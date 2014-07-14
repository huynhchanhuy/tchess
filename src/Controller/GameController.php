<?php

namespace Tchess\Controller;

//use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Tchess\Entity\Player;
use Tchess\Entity\Room;
use Tchess\GameEvents;
use Tchess\Event\GameEvent;
use Tchess\MoveEvents;
use Tchess\Event\MoveEvent;
use Tchess\Entity\Piece\Move;
use Tchess\ExceptionCodes;
use Symfony\Component\Validator\Constraints\NotBlank;

class GameController extends BaseController
{

    /**
     * Show the board.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return string
     */
    public function indexAction(Request $request)
    {
        $em = $this->container->get('entity_manager');
        $session = $request->getSession();
        $sid = $session->getId();
        $player = $em->getRepository('Tchess\Entity\Player')->findOneBy(array('sid' => $sid));

        if (empty($player) || !$player instanceof Player) {
            return $this->redirect($this->generateUrl('register'));
        }

        $last_room = $player->getRoom();
        if (empty($last_room) || !$last_room instanceof Room) {
            return $this->redirect($this->generateUrl('rooms'));
        }

        return $this->render('homepage.html.twig');
    }

    /**
     * Show register form.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return string
     */
    public function registerAction(Request $request)
    {
        $em = $this->container->get('entity_manager');
        $session = $request->getSession();
        $sid = $session->getId();
        $player = $em->getRepository('Tchess\Entity\Player')->findOneBy(array('sid' => $sid));

        if (!empty($player) && $player instanceof Player) {
            return $this->redirect($this->generateUrl('rooms'));
        }

        $form = $this->getFormFactory()->createBuilder()
            ->add('name', 'text', array(
                'constraints' => new NotBlank(),
            ))
            ->add('auto_join', 'checkbox', array(
                'label'     => 'Automatically join room',
                'required'  => false,
            ))
            ->getForm();

        if ($request->getMethod() == 'POST') {
            $form->bind($request);
            if ($form->isValid()) {
                $data = $form->getData();
                $player = new Player();
                $player->setSid($sid);
                $player->setStarted(false);
                $player->setName($data['name']);
                // Default color for all player, will be updated later.
                $player->setColor('white');
                $em->persist($player);
                $em->flush();

                if ($data['auto_join']) {
                    $sub_request = Request::create('/auto-join-room');
                    $sub_request->setSession($session);
                    return $this->container->get('framework')->handle($sub_request, HttpKernelInterface::SUB_REQUEST);
                }

                return $this->redirect($this->generateUrl('rooms'));
            }
        }

        return $this->render('register.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * Automatically join game.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return string
     */
    public function autoJoinAction(Request $request)
    {
        $em = $this->container->get('entity_manager');
        $session = $request->getSession();
        $sid = $session->getId();

        $player = $em->getRepository('Tchess\Entity\Player')->findOneBy(array('sid' => $sid));

        if (empty($player) || !$player instanceof Player) {
            throw new \LogicException('Player did not registered', ExceptionCodes::PLAYER);
        }

        $last_room = $player->getRoom();
        if (empty($last_room) || !$last_room instanceof Room) {
            $room = $em->getRepository('Tchess\Entity\Room')
                    ->findOpenRoom($player);

            if (empty($room)) {
                $room = new Room();
                $room->addPlayer($player);
                $em->persist($room);
            } else {
                $players = $room->getPlayers();
                $opponent_player = reset($players);
                $player->setColor($opponent_player->getColor() == 'white' ? 'black' : 'white');
                $room->addPlayer($player);
            }
            $player->setRoom($room);
            $em->flush();
        }
        else {
            // If you want to play with other player, please leave room first.
        }

        return $this->redirect($this->generateUrl('homepage'));
    }

    /**
     * Join game.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return string
     */
    public function joinAction(Request $request, $room = null)
    {
        $em = $this->container->get('entity_manager');
        $session = $request->getSession();
        $sid = $session->getId();

        $player = $em->getRepository('Tchess\Entity\Player')->findOneBy(array('sid' => $sid));

        if (empty($player) || !$player instanceof Player) {
            throw new \LogicException('Player did not registered', ExceptionCodes::PLAYER);
        }

        $last_room = $player->getRoom();
        if (!empty($last_room) && $last_room instanceof Room) {
            if ($last_room->getId() != $room) {
                throw new \LogicException('Player already join another room', ExceptionCodes::PLAYER);
            }
            else {
                return $this->redirect($this->generateUrl('homepage'));
            }
        }

        $room = $em->getRepository('Tchess\Entity\Room')->findOneBy(array('id' => $room));
        $players = $room->getPlayers();

        if (count($players) != 1) {
            throw new \LogicException('Room must have only one players to join', ExceptionCodes::PLAYER);
        }

        $opponent_player = reset($players);
        $player->setColor($opponent_player->getColor() == 'white' ? 'black' : 'white');
        $room->addPlayer($player);
        $player->setRoom($room);
        $em->flush();

        return $this->redirect($this->generateUrl('homepage'));
    }

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
            throw new \LogicException('Player did not join a room', ExceptionCodes::PLAYER);
        }

        if ($player->getStarted()) {
            throw new \LogicException('Game has been already started', ExceptionCodes::PLAYER);
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
            throw new \LogicException('Player did not join a room', ExceptionCodes::PLAYER);
        }

        if (!$player->getStarted()) {
            throw new \LogicException('Game is not started', ExceptionCodes::PLAYER);
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
            throw new \LogicException('Player did not join a room', ExceptionCodes::PLAYER);
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
            throw new \Exception('There is unknown error while re-starting game');
        }
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
        $dispatcher = $this->container->get('dispatcher');
        $session = $request->getSession();
        $sid = $session->getId();

        $player = $em->getRepository('Tchess\Entity\Player')->findOneBy(array('sid' => $sid));

        if (empty($player) || ($player instanceof Player && empty($player->getRoom()))) {
            throw new \LogicException('Player did not join a room', ExceptionCodes::PLAYER);
        }

        $game = $player->getRoom()->getGame();

        if (empty($game) || ($game instanceof Game && !$game->getStarted())) {
            throw new \LogicException('Opponent player did not start the game', ExceptionCodes::PLAYER);
        } else {
            $board = $game->getBoard();
            $move = new Move($request->request->get('move'));
            $color = $player->getColor();

            if ($dispatcher->dispatch(MoveEvents::CHECH_MOVE, new MoveEvent($board, $move, $color))->isValidMove()) {
                $dispatcher->dispatch(MoveEvents::MOVE, new MoveEvent($board, $move, $color));
            } else {
                throw new \LogicException('Move is not valid', ExceptionCodes::PLAYER);
            }
        }

        return 'Move has been performed';
    }

}
