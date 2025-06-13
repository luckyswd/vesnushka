<?php

namespace App\Repository;

use App\Entity\ItemAttribute;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ItemAttributeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ItemAttribute::class);
    }

    public function findItemAttributes(): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = <<<SQL
            SELECT 
                ia.item_guid,
                a.name AS attribute_name,
                ia.value AS attribute_value
            FROM item_attribute ia
            JOIN attribute a ON ia.attribute_guid = a.guid
        SQL;

        $rows = $conn->executeQuery(
            $sql,
        )->fetchAllAssociative();

        $result = [];

        foreach ($rows as $row) {
            $itemGuid = $row['item_guid'];
            $attributeName = $row['attribute_name'];
            $attributeValue = $row['attribute_value'];

            if (!isset($result[$itemGuid])) {
                $result[$itemGuid] = [];
            }

            $result[$itemGuid][] = [
                'name' => $attributeName,
                'value' => $attributeValue,
            ];
        }

        return $result;
    }
}
