<?php

namespace Tchess\Controller;

//use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Tchess\Entity\Player;
use Tchess\Entity\Room;
use Tchess\Helper\Paginator;

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
     * Create own room.
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

}
