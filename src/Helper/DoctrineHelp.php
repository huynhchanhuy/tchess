<?php

namespace Tchess\Helper;

use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\ORM\Query;

class DoctrineHelp
{

    static public function paginate(Query $query, &$offset = 0, &$limit = 10)
    {

        $paginator = new Paginator($query);

        $paginator
                ->getQuery()
                ->setFirstResult($offset)
                ->setMaxResults($limit)
        ;

        return $paginator;
    }

}
