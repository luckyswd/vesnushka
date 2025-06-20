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
}
