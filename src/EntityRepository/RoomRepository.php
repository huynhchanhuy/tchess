<?php

namespace Tchess\EntityRepository;

use Doctrine\ORM\EntityRepository;
use Tchess\Entity\Room;

class RoomRepository extends EntityRepository
{

    public function findOpenRoom($sid = 0)
    {
        $dql = "SELECT r FROM Tchess\Entity\Room r WHERE (r.white = ?1 AND r.black is NULL) OR (r.black = ?1 AND r.white is NULL)";

        return $this->getEntityManager()->createQuery($dql)
                        ->setParameter(1, $sid)
                        ->setMaxResults(1)
                        ->getSingleResult();
    }

    public function createRoom($sid = 0)
    {
        $room = new Room();
        $room->setWhite($sid);

        $em = $this->getEntityManager();
        $em->persist($room);
        $em->flush();

        return $room;
    }

}