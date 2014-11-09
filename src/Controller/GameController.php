<?php

namespace Tchess\Controller;

//use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Tchess\Entity\Player;
use Tchess\Entity\Room;
use Tchess\GameEvents;
use Tchess\Event\GameEvent;
use Tchess\MoveEvents;
use Tchess\Event\MoveEvent;
use Tchess\Entity\Piece\Move;
use Tchess\ExceptionCodes;
use Symfony\Component\Validator\Constraints\NotBlank;
use Tchess\Entity\Game;

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
        $em = $this->framework->getEntityManager();
        $session = $request->getSession();
        $sid = $session->getId();
        $player = $em->getRepository('Tchess\Entity\Player')->findOneBy(array('sid' => $sid));
        $variables = array();

        if (empty($player) || !$player instanceof Player) {
            return $this->redirect($this->generateUrl('register'));
        }

        $room = $player->getRoom();
        if (empty($room) || !$room instanceof Room) {
            return $this->redirect($this->generateUrl('rooms'));
        }

        $variables['started'] = $player->getStarted();
        $variables['color'] = $player->getColor();
        $game = $player->getRoom()->getGame();

        if (empty($game) || !$game instanceof Game || !$game->getStarted()) {
            $variables['start_position'] = 'start';
            $variables['highlights'] = '';
        }
        else {
            $variables['start_position'] = $game->getState();
            $variables['highlights'] = trim($game->getHighlights());
        }

        return $this->render('homepage.html.twig', $variables);
    }

    /**
     * Show the board.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $room Room id
     * @return string
     */
    public function watchAction(Request $request, $room)
    {
        $em = $this->framework->getEntityManager();
        $room = $em->getRepository('Tchess\Entity\Room')->findOneBy(array('id' => $room));

        if (empty($room) || !$room instanceof Room) {
            return $this->redirect($this->generateUrl('rooms'));
        }

        $variables = array();
        $game = $room->getGame();

        if (empty($game) || !$game instanceof Game || !$game->getStarted()) {
            $variables['start_position'] = 'start';
            $variables['highlights'] = '';
        }
        else {
            $variables['start_position'] = $game->getState();
            $variables['highlights'] = trim($game->getHighlights());
        }

        return $this->render('watch.html.twig', $variables);
    }

    /**
     * Practice.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return string
     */
    public function practiceAction(Request $request)
    {
        $variables = array(
            'logged_in' => $this->isLoggedIn($request)
        );
        return $this->render('practice.html.twig', $variables);
    }

    /**
     * Show register form.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return string
     */
    public function registerAction(Request $request)
    {
        $em = $this->framework->getEntityManager();
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
                ->getForm();

        if ($request->getMethod() == 'POST') {
            $form->bind($request);
            if ($form->isValid()) {
                $data = $form->getData();
                $player = new Player();
                $player->setSid($sid);
                $player->setName($data['name']);
                // Default color for all player, will be updated later.
                $player->setColor('white');
                $em->persist($player);
                $em->flush();

                return $this->redirect($this->generateUrl('rooms'));
            }
        }

        $variables = array(
            'form' => $form->createView(),
        );
        return $this->render('register.html.twig', $variables);
    }

    /**
     * Logout.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return string
     */
    public function logoutAction(Request $request)
    {
        $session = $request->getSession();
        $session->invalidate();
        return $this->redirect($this->generateUrl('register'));
    }

    /**
     * Join room.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return string
     */
    public function joinAction(Request $request, $room = null)
    {
        $em = $this->framework->getEntityManager();
        $session = $request->getSession();
        $sid = $session->getId();

        $player = $em->getRepository('Tchess\Entity\Player')->findOneBy(array('sid' => $sid));

        if (empty($player) || !$player instanceof Player) {
            return $this->redirect($this->generateUrl('register'));
        }

        $joined_room = $player->getRoom();
        if (!empty($joined_room) && $joined_room instanceof Room) {
            return $this->redirect($this->generateUrl('homepage'));
        }

        $room = $em->getRepository('Tchess\Entity\Room')->findOneBy(array('id' => $room));
        $players = $room->getPlayers();

        if (count($players) != 1) {
            return $this->redirect($this->generateUrl('rooms'));
        }

        $opponent_player = current(reset($players));
        $player->setColor($opponent_player->getColor() == 'white' ? 'black' : 'white');
        $room->addPlayer($player);
        $player->setRoom($room);
        $em->flush();

        return $this->redirect($this->generateUrl('homepage'));
    }

    /**
     * Leave room.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return string
     */
    public function leaveAction(Request $request)
    {
        $em = $this->framework->getEntityManager();
        $session = $request->getSession();
        $sid = $session->getId();

        $player = $em->getRepository('Tchess\Entity\Player')->findOneBy(array('sid' => $sid));

        if (empty($player) || !$player instanceof Player) {
            return $this->redirect($this->generateUrl('register'));
        }

        $room = $player->getRoom();
        if (!empty($room) && $room instanceof Room) {
            $room->removePlayer($player);
            $player->setRoom(null);
            $em->flush();

            $this->framework->getEventDispatcher()->dispatch(GameEvents::LEAVE, new GameEvent($player, $em));
        }

        return $this->redirect($this->generateUrl('rooms'));
    }

    /**
     * Create room.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return string
     */
    public function createAction(Request $request)
    {
        $em = $this->framework->getEntityManager();
        $session = $request->getSession();
        $sid = $session->getId();

        $player = $em->getRepository('Tchess\Entity\Player')->findOneBy(array('sid' => $sid));

        if (empty($player) || !$player instanceof Player) {
            return $this->redirect($this->generateUrl('register'));
        }

        $joined_room = $player->getRoom();
        if (empty($joined_room) || !$joined_room instanceof Room) {
            $room = new Room();
            $room->addPlayer($player);
            $em->persist($room);
            $player->setRoom($room);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('homepage'));
    }

    /**
     * Move a piece.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return string
     */
    public function moveAction(Request $request)
    {
        $em = $this->framework->getEntityManager();
        $dispatcher = $this->framework->getEventDispatcher();
        $session = $request->getSession();
        $sid = $session->getId();

        $player = $em->getRepository('Tchess\Entity\Player')->findOneBy(array('sid' => $sid));

        if (empty($player) || ($player instanceof Player && empty($player->getRoom()))) {
            throw new \LogicException('Player did not join a room', ExceptionCodes::PLAYER);
        }

        $game = $player->getRoom()->getGame();

        if (empty($game) || !$game instanceof Game || !$game->getStarted()) {
            return json_encode(array(
                'code' => 500,
                'message' => 'Both players did not start the game'
            ));
        }
        else if ($game->getBoard()->getActiveColor() != $player->getColor()) {
            return json_encode(array(
                'code' => 500,
                'message' => 'This is not your turn'
            ));
        }
        else {
            $serializer = $this->framework->getSerializer();
            $game->loadGame($serializer);
            $board = $game->getBoard();
            $color = $player->getColor();
            $move = new Move($color, $request->request->get('move'));

            if ($dispatcher->dispatch(MoveEvents::CHECK_MOVE, new MoveEvent($board, $move, $color))->isValidMove()) {
                $board->movePiece($move);
                $moveEvent = new MoveEvent($board, $move, $color);

                $dispatcher->dispatch(MoveEvents::MOVE, $moveEvent);

                $board->setActiveColor($color == 'white' ? 'black' : 'white');
                $game->saveGame($serializer);
                $game->addHighlight($move->getSource(), $move->getTarget(), $color);
                $em->flush();

                $this->framework->getMoveManager()->addMove($move);
            } else {
                return json_encode(array(
                    'code' => 500,
                    'message' => 'Move is not valid'
                ));
            }
        }

        return json_encode(array(
            'code' => 200,
            'message' => 'Move has been performed',
            'color' => $color,
            'turn' => $game->getBoard()->getActiveColor()
        ));
    }

}
