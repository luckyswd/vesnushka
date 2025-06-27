<?php

namespace App\Repository;

use App\Entity\Brand;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class BrandRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Brand::class);
    }

    public function findBrands(): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = <<<SQL
        SELECT 
            b.guid,
            b.name
            FROM brand b
        SQL;

        $rows = $conn->executeQuery(
            $sql,
        )->fetchAllAssociative();

        $result = [];

        foreach ($rows as $row) {
            $guid = $row['guid'];
            unset($row['guid']);

            $row['count'] = 0;
            $result[$guid] = $row;
        }

        return $result;
    }

    public function findPopularBrands(?int $limit = 6): array
    {
        $qb = $this->createQueryBuilder('b')
            ->leftJoin('b.image', 'image')
            ->addSelect('image')
            ->andWhere('b.isPopular = :IS_POPULAR')
            ->setParameter('IS_POPULAR', true)
            ->orderBy('b.name', 'ASC');

        if (null !== $limit) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }

    public function findAllBrands(): array
    {
        $qb = $this->createQueryBuilder('b')
            ->leftJoin('b.image', 'image')
            ->addSelect('image')
            ->orderBy('b.isPopular', 'ASC')
            ->setMaxResults(100);

        return $qb->getQuery()->getResult();
    }
}
