<?php

namespace App\Repository;

use App\Entity\Restaurant;
use App\Entity\Step;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Step>
 */
class StepRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Step::class);
    }

    public function countByRestaurant(Restaurant $restaurant): int
    {
        return $this->createQueryBuilder('s')
            ->join('s.restaurants', 'r')
            ->where('r = :restaurant')
            ->setParameter('restaurant', $restaurant)
            ->select('COUNT(s.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
