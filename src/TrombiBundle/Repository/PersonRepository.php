<?php

namespace TrombiBundle\Repository;

/**
 * PersonRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class PersonRepository extends \Doctrine\ORM\EntityRepository
{
    public function searchByNameAndCategory($input, $category)
    {
        $qb = $this->createQueryBuilder('p')
            ->where('p.firstname LIKE :input')
            ->orWhere('p.lastname LIKE :input')
            ->setParameter('input', '%'.$input.'%')
             ->andWhere('p.category = :category')
            ->setParameter('category', $category);
        return $qb->getQuery()->getResult();

    }

    public function searchByName($input)
    {
        $qb = $this->createQueryBuilder('p')
            ->select('p.firstname, p.lastname')
            ->where('p.firstname LIKE :input')
            ->orWhere('p.lastname LIKE :input')
            ->setParameter('input', '%'.$input.'%');
        return $qb->getQuery()->getResult();

    }
}
