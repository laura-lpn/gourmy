<?php

namespace App\Repository;

use App\Entity\Restaurant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Restaurant>
 */
class RestaurantRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Restaurant::class);
    }

    public function findRandomByCriteria(string $town, ?array $cuisineNames, int $limit): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $params = ['town' => $town, 'limit' => $limit];
        $types = [\PDO::PARAM_STR, \PDO::PARAM_INT];

        $sql = '
        SELECT r.id, RANDOM() as rand
        FROM restaurant r
        LEFT JOIN restaurant_type_restaurant rtr ON r.id = rtr.restaurant_id
        LEFT JOIN type_restaurant t ON t.id = rtr.type_restaurant_id
        WHERE LOWER(r.city) = LOWER(:town)
          AND r.is_valided = TRUE';

        if (!empty($cuisineNames)) {
            $placeholders = [];
            foreach ($cuisineNames as $i => $cuisine) {
                $placeholder = ':cuisine' . $i;
                $placeholders[] = $placeholder;
                $params['cuisine' . $i] = $cuisine;
                $types[] = \PDO::PARAM_STR;
            }
            $sql .= ' AND t.name IN (' . implode(', ', $placeholders) . ')';
        }

        $sql .= ' ORDER BY rand LIMIT :limit';

        $stmt = $conn->prepare($sql);
        $result = $stmt->executeQuery($params);

        $ids = array_column($result->fetchAllAssociative(), 'id');

        if (empty($ids)) {
            return [];
        }

        return $this->createQueryBuilder('r')
            ->addSelect('t')
            ->leftJoin('r.types', 't')
            ->where('r.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult();
    }

    public function searchByName(string $term): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('LOWER(r.name) LIKE LOWER(:term)')
            ->setParameter('term', '%' . $term . '%')
            ->andWhere('r.isValided = true')
            ->orderBy('r.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
