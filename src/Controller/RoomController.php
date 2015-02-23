<?php

namespace Tchess\Controller;

//use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Tchess\Helper\Paginator;
use Tchess\RoomEvents;
use Tchess\Event\RoomEvent;
use Tchess\Entity\Player;
use Tchess\Entity\Room;
use Tchess\Entity\Game;

class RoomController extends BaseController
{

    /**
     * Show open rooms.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $page
     * @return string
     */
    public function indexAction(Request $request, $page = 1)
    {
        $em = $this->framework->getEntityManager();
        $session = $request->getSession();
        $sid = $session->getId();

        $player = $em->getRepository('Tchess\Entity\Player')->findOneBy(array('sid' => $sid));

        $limit = 10;
        $midrange = 3;
        $offset = $limit * ($page - 1);

        $rooms = $em->getRepository('Tchess\Entity\Room')
                ->findRooms($offset, $limit);

        $paginator = new Paginator(count($rooms), $page , $limit, $midrange);

        return $this->render('rooms.html.twig', array(
            'rooms' => $rooms,
            'player' => $player,
            'paginator' => $paginator,
            'logged_in' => $this->isLoggedIn($request)
        ));
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
        if (empty($room) || !$room instanceof Room) {
            // Room does not exist.
            return $this->redirect($this->generateUrl('rooms'));
        }

        $players = $room->getPlayers();

        if (count($players) != 1) {
            return $this->redirect($this->generateUrl('rooms'));
        }

        $opponent_player = current(reset($players));
        $player->setColor($opponent_player->getColor() == 'white' ? 'black' : 'white');
        $room->addPlayer($player);
        $player->setRoom($room);
        $em->flush();

        $event = new RoomEvent($room, $player);
        $this->framework->getEventDispatcher()->dispatch(RoomEvents::JOIN, $event);

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
            $game = $room->getGame();
            if ($game instanceof Game) {
                $em->remove($room->getGame());
            }

            $room->removePlayer($player);
            $player->setRoom(null);
            $em->flush();

            $event = new RoomEvent($room, $player);
            $this->framework->getEventDispatcher()->dispatch(RoomEvents::LEAVE, $event);
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

            $event = new RoomEvent($room, $player);
            $this->framework->getEventDispatcher()->dispatch(RoomEvents::CREATE, $event);
        }

        return $this->redirect($this->generateUrl('homepage'));
    }

}
