<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

class ProjectRepository extends EntityRepository {

    public function findAllOrderedByName() {
        return $this->getEntityManager()
                        ->createQuery(
                                'SELECT p FROM AppBundle:Project p ORDER BY p.title ASC'
                        )
                        ->getResult();
    }

    public function findAllFromCount($from, $count) {
        return $this->getEntityManager()
                        ->createQuery(
                                'SELECT p FROM AppBundle:Project p ORDER BY p.title ASC'
                        )->setMaxResults($count)
                        ->setFirstResult($from)
                        ->getResult();
    }

    public function count() {
        $qb = $this
                ->createQueryBuilder('t');
        return $qb
                        ->select('count(t.id)')
                        ->getQuery()
                        ->useQueryCache(true)
                        ->useResultCache(true, 3600)
                        ->getSingleScalarResult();
    }

}
