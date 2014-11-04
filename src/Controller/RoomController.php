<?php

namespace Tchess\Controller;

//use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Tchess\Entity\Player;
use Tchess\Entity\Room;
use Tchess\ExceptionCodes;
use Tchess\Helper\Paginator;

class RoomController extends BaseController
{

    /**
     * Show open rooms.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $offset
     * @return string
     */
    public function indexAction(Request $request, $offset)
    {
        $em = $this->framework->getEntityManager();
        $session = $request->getSession();
        $sid = $session->getId();

        $player = $em->getRepository('Tchess\Entity\Player')->findOneBy(array('sid' => $sid));

        $limit = 10;
        $midrange = 3;

        $rooms = $em->getRepository('Tchess\Entity\Room')
                ->findRooms($offset, $limit);

        $paginator = new Paginator(74, $offset , $limit, $midrange);

        return $this->render('rooms.html.twig', array(
            'rooms' => $rooms,
            'player' => $player,
            'paginator' => $paginator
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
            throw new \LogicException('Player did not registered', ExceptionCodes::PLAYER);
        }

        $last_room = $player->getRoom();
        if (empty($last_room) || !$last_room instanceof Room) {
            $room = new Room();
            $room->addPlayer($player);
            $em->persist($room);
            $player->setRoom($room);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('homepage'));
    }

}
