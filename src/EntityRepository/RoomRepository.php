<?php

namespace Tchess\EntityRepository;

use Doctrine\ORM\EntityRepository;
use Tchess\Entity\Room;
use Tchess\Entity\Player;

class RoomRepository extends EntityRepository
{

    public function findOpenRoom(Player $player)
    {
//        $dql = "SELECT r, COUNT(r.players) AS num_player FROM Tchess\Entity\Room r WHERE num_player < 2";
//
//        return $this->getEntityManager()->createQuery($dql)
//                        ->setParameter(1, $sid)
//                        ->setMaxResults(1)
//                        ->getSingleResult();
        return $this->getEntityManager()->createQueryBuilder()
                        ->select('r')
                        ->from("Tchess\Entity\Room", 'r')
                        ->leftJoin('r.players', 'p')
                        ->where('p.id != :pid')->setParameters(array('pid' => $player->getId()))
                        ->addGroupBy('r.id')
                        ->having('COUNT(DISTINCT p.id) = 1')
                        ->setMaxResults(1)
                        ->getQuery()
                        ->getOneOrNullResult();
    }

    public function findOpenRooms(Player $player)
    {
        return $this->getEntityManager()->createQueryBuilder()
                        ->select('r')
                        ->from("Tchess\Entity\Room", 'r')
                        ->leftJoin('r.players', 'p')
                        ->where('p.id != :pid')->setParameters(array('pid' => $player->getId()))
                        ->addGroupBy('r.id')
                        ->having('COUNT(DISTINCT p.id) = 1')
                        ->getQuery()
                        ->getResult();
    }

}
