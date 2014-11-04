<?php

namespace Tchess\EntityRepository;

use Doctrine\ORM\EntityRepository;
use Tchess\Helper\DoctrineHelp;

class RoomRepository extends EntityRepository
{

    public function findRooms($offset = 0, $limit = 10)
    {

        $query = $this->getEntityManager()->createQueryBuilder()
                        ->select('r')
                        ->from("Tchess\Entity\Room", 'r')
                        ->leftJoin('r.players', 'p')
                        ->addGroupBy('r.id')
                        ->having('COUNT(DISTINCT p.id) = 1 OR COUNT(DISTINCT p.id) = 2')
                        ->getQuery();

        return DoctrineHelp::paginate($query, $offset, $limit);
    }

}
