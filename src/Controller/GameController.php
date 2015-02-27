<?php

namespace Tchess\Controller;

//use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Tchess\Entity\Player;
use Tchess\Entity\Room;
use Tchess\MoveEvents;
use Tchess\Event\MoveEvent;
use Tchess\Entity\Piece\Move;
use Symfony\Component\Validator\Constraints\NotBlank;
use Tchess\Entity\Game;
use Tchess\Message\Message;
use Symfony\Component\HttpKernel\HttpKernelInterface;

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
        $em = $this->getEntityManager();
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

        $variables['base_url'] = $request->getHttpHost();
        $variables['room_id'] = $room->getId();
        $variables['color'] = $player->getColor();
        $game = $room->getGame();

        if (empty($game) || !$game instanceof Game) {
            $variables['start_position'] = 'start';
            $variables['highlights'] = '';
        }
        else {
            $variables['start_position'] = $game->getState();
            $variables['highlights'] = trim($game->getHighlights());
        }

        foreach ($room->getPlayers() as $roomPlayer) {
            $variables['players'][$roomPlayer->getColor()] = $roomPlayer->getName();
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
        $em = $this->getEntityManager();
        $room = $em->getRepository('Tchess\Entity\Room')->findOneBy(array('id' => $room));

        if (empty($room) || !$room instanceof Room) {
            return $this->redirect($this->generateUrl('rooms'));
        }

        $variables = array(
            'room_id' => $room->getId(),
            'base_url' => $request->getHttpHost(),
        );
        $game = $room->getGame();

        if (empty($game) || !$game instanceof Game) {
            $variables['start_position'] = 'start';
            $variables['highlights'] = '';
        }
        else {
            $variables['start_position'] = $game->getState();
            $variables['highlights'] = trim($game->getHighlights());
        }

        foreach ($room->getPlayers() as $roomPlayer) {
            $variables['players'][$roomPlayer->getColor()] = $roomPlayer->getName();
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
        $em = $this->getEntityManager();
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

        $sub_request = Request::create('/leave-room');
        $sub_request->setSession($session);
        $this->container->get('kernel')->handle($sub_request, HttpKernelInterface::SUB_REQUEST);

        $session->invalidate();
        return $this->redirect($this->generateUrl('register'));
    }

    /**
     * Move a piece.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return string
     */
    public function moveAction(Request $request)
    {
        $em = $this->getEntityManager();
        $dispatcher = $this->container->get('dispatcher');
        $validator = $this->container->get('validator');
        $session = $request->getSession();
        $sid = $session->getId();

        $player = $em->getRepository('Tchess\Entity\Player')->findOneBy(array('sid' => $sid));

        if (empty($player) || !($player instanceof Player)) {
            return json_encode(array(
                'code' => 500,
                'message' => 'Player did not register'
            ));
        }

        $room = $player->getRoom();
        if (empty($room) || !($room instanceof Room)) {
            return json_encode(array(
                'code' => 500,
                'message' => 'Player did not join a room'
            ));
        }

        $game = $room->getGame();
        if (empty($game) || !$game instanceof Game) {
            return json_encode(array(
                'code' => 500,
                'message' => 'There is not enough players in the room'
            ));
        }

        $serializer = $this->container->get('serializer');
        $board = $serializer->deserialize($game->getState(), 'Tchess\Entity\Board', 'fen');
        $color = $player->getColor();

        if ($board->getActiveColor() != $color) {
            return json_encode(array(
                'code' => 500,
                'message' => 'This is not your turn'
            ));
        }

        $move = new Move($board, $color, $request->request->get('move'));

        $errors = $validator->validate($move);
        if (count($errors) == 0) {
            $board->movePiece($move->getSource(), $move->getTarget());

            $moveEvent = new MoveEvent($room->getId(), $move);
            $dispatcher->dispatch(MoveEvents::MOVE, $moveEvent);

            $state = $serializer->serialize($board, 'fen');
            $game->setState($state);
            $game->addHighlight($move->getSource(), $move->getTarget(), $color);
            $em->flush();

            $message = new Message($room->getId(), 'move', array(
                'source' => $move->getSource(),
                'target' => $move->getTarget(),
                'color' => $move->getColor(),
                'castling' => $move->getCastling(),
            ));
            $this->container->get('message_manager')->addMessage($message);

            return json_encode(array(
                'code' => 200,
                'message' => 'Move has been performed',
                'color' => $color,
            ));
        } else {
            return json_encode(array(
                'code' => 500,
                'message' => (string) $errors,
            ));
        }
    }

}
