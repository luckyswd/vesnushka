<?php

namespace App\Repository;

use App\Entity\Category;
use App\Entity\Item;
use App\Enum\CategoryPublishStateEnum;
use App\Enum\ItemPublishStateEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Item>
 *
 * @method Item|null find($id, $lockMode = null, $lockVersion = null)
 * @method Item|null findOneBy(array $criteria, array $orderBy = null)
 * @method Item[]    findAll()
 * @method Item[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Item::class);
    }

    public function findVisibleItemByUrl(string $url): ?Item
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.url = :url')
            ->andWhere('i.publishState IN (:publishStates)')
            ->setParameter('url', $url)
            ->setParameter('publishStates', array_map(fn ($e) => $e->value, ItemPublishStateEnum::getVisibleStates()))
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findItemsByCategory(
        Category $category,
        int $page = 1,
        int $limit = 20,
        ?float $minPrice = null,
        ?float $maxPrice = null,
        ?array $attributeGuids = [],
        ?string $sortField = null,
        ?string $sortDirection = null
    ): array {
        $subCategories = $this->getSubCategoriesByCategory($category);

        // === Base QB
        $baseQb = $this->createBaseItemsQueryBuilder(
            $subCategories,
            $minPrice,
            $maxPrice,
            $attributeGuids
        );

        // === Count
        $countQb = clone $baseQb;
        $countQb->select('COUNT(DISTINCT i.guid)');
        $totalCount = (int) $countQb->getQuery()->getSingleScalarResult();

        // === Guids (for pagination)
        $guidsQb = clone $baseQb;
        $guidsQb
            ->select('DISTINCT i.guid');

        // Apply sorting
//        if (in_array($sortField, ['price', 'name', 'createdAt']) && in_array(strtoupper($sortDirection), ['ASC', 'DESC'])) {
//            $guidsQb->orderBy("i.$sortField", $sortDirection);
//        } else {
//            // Fallback sort
//            $guidsQb->orderBy('i.createdAt', 'DESC');
//        }

        $guidsQb
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        $itemGuids = $guidsQb->getQuery()->getResult();
        $itemGuids = array_column($itemGuids, 'guid');

        if (empty($itemGuids)) {
            // Нет подходящих товаров
            return [
                'totalCount' => $totalCount,
                'items' => [],
                'page' => $page,
                'limit' => $limit,
                'pagesCount' => (int) ceil($totalCount / $limit),
            ];
        }

        // === Items loading
        $itemsQb = $this->createQueryBuilder('i')
            ->leftJoin('i.categories', 'c')
            ->leftJoin('i.brand', 'b')
            ->leftJoin('i.itemAttributes', 'ia')
            ->leftJoin('ia.attribute', 'a')
            ->addSelect('b, ia, a')
            ->where('i.guid IN (:ITEM_GUIDS)')
            ->setParameter('ITEM_GUIDS', $itemGuids);

        $items = $itemsQb->getQuery()->getResult();

        return [
            'totalCount' => $totalCount,
            'items' => $items,
            'page' => $page,
            'limit' => $limit,
            'pagesCount' => (int) ceil($totalCount / $limit),
        ];
    }

    private function createBaseItemsQueryBuilder(
        array $subCategories,
        ?float $minPrice = null,
        ?float $maxPrice = null,
        ?array $attributeGuids = []
    ): QueryBuilder {
        $qb = $this->createQueryBuilder('i')
            ->leftJoin('i.categories', 'c')
            ->leftJoin('i.itemAttributes', 'ia')
            ->leftJoin('ia.attribute', 'a')
            ->where('c IN (:SUB_CATEGORIES)')
            ->andWhere('i.publishState IN (:PUBLISH_STATE)')
            ->setParameter('SUB_CATEGORIES', $subCategories)
            ->setParameter('PUBLISH_STATE', [
                ItemPublishStateEnum::ACTIVE->value,
                ItemPublishStateEnum::OUT_OF_STOCK->value
            ]);

        if ($minPrice !== null) {
            $qb->andWhere('i.price >= :MIN_PRICE')
                ->setParameter('MIN_PRICE', $minPrice);
        }

        if ($maxPrice !== null) {
            $qb->andWhere('i.price <= :MAX_PRICE')
                ->setParameter('MAX_PRICE', $maxPrice);
        }

        if (!empty($attributeGuids)) {
            // Фильтр по атрибутам: только те товары, у которых есть хотя бы один указанный атрибут
            $qb->andWhere('a.guid IN (:ATTRIBUTE_GUIDS)')
                ->setParameter('ATTRIBUTE_GUIDS', $attributeGuids);
        }

        return $qb;
    }

    private function getSubCategoriesByCategory(Category $category): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $categoryGuidsSql = <<<SQL
            WITH RECURSIVE category_tree AS (
                SELECT guid
                FROM category
                WHERE guid = :categoryGuid
                AND publish_state = :CATEGORY_PUBLISH_ACTIVE
        
                UNION ALL
        
                SELECT c.guid
                FROM category c
                INNER JOIN category_tree ct ON c.parent_id = ct.guid
                WHERE c.publish_state = :CATEGORY_PUBLISH_ACTIVE
            )
            SELECT guid
            FROM category_tree
        SQL;

        $categoryGuids = $conn->executeQuery(
            $categoryGuidsSql,
            [
                'categoryGuid' => $category->getGuid(),
                'CATEGORY_PUBLISH_ACTIVE' => CategoryPublishStateEnum::ACTIVE->value,
            ]
        )->fetchFirstColumn();

        return $categoryGuids;
    }
}
