<?php

namespace Tchess\Controller;

//use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
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

}
