<?php

namespace App\Repository;

use App\Entity\Category;
use App\Entity\Item;
use App\Enum\CategoryPublishStateEnum;
use App\Enum\CurrencyEnum;
use App\Enum\ItemPublishStateEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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
        string $sort,
        string $currency = CurrencyEnum::BYN->value,
    ): \Traversable {
        $conn = $this->getEntityManager()->getConnection();
        $subCategories = $this->getSubCategoriesByCategory($category);

        $allowedCurrencies = array_column(CurrencyEnum::cases(), 'value');

        if (!in_array($currency, $allowedCurrencies, true)) {
            throw new \InvalidArgumentException("Недопустимая валюта: $currency");
        }

        $jsonPathCurrency = "'$currency'";

        $sortOuter = match ($sort) {
            'cheap' => 'CAST(price AS NUMERIC) ASC',
            'expensive' => 'CAST(price AS NUMERIC) DESC',
            default, => 'rank DESC',
        };

        $sql = <<<SQL
                SELECT *
                FROM (
                    SELECT
                        i.guid,
                        i.brand_guid,
                        i.name,
                        i.sku,
                        i.url,
                        i.breadcrumbs,
                        i.stock,
                        i.attributes,
                        i.price->'retail'->$jsonPathCurrency AS price,
                        i.rank,
                        f.path AS main_image_path,
                        ROW_NUMBER() OVER (
                            PARTITION BY i.guid
                        ) as row_num
                    FROM item i
                        INNER JOIN item_category ic ON i.guid = ic.item_guid
                        INNER JOIN category c ON c.guid = ic.category_guid
                        LEFT JOIN file f ON i.main_image_guid = f.guid
                    WHERE c.guid IN (:CATEGORY_GUIDS)
                      AND i.publish_state IN (:ITEM_PUBLISH_ACTIVE, :ITEM_PUBLISH_OUT_OF_STOCK)
                ) sub
                WHERE sub.row_num = 1
                ORDER BY $sortOuter
            SQL;

        $stmt = $conn->executeQuery(
            $sql,
            [
                'CATEGORY_GUIDS' => $subCategories,
                'ITEM_PUBLISH_ACTIVE' => ItemPublishStateEnum::ACTIVE->value,
                'ITEM_PUBLISH_OUT_OF_STOCK' => ItemPublishStateEnum::OUT_OF_STOCK->value,
            ],
            [
                'CATEGORY_GUIDS' => \Doctrine\DBAL\Connection::PARAM_STR_ARRAY,
                'ITEM_PUBLISH_ACTIVE' => \PDO::PARAM_STR,
                'ITEM_PUBLISH_OUT_OF_STOCK' => \PDO::PARAM_STR,
            ],
        );

        while ($row = $stmt->fetchAssociative()) {
            yield $row;
        }
    }

    public function getSubCategoriesByCategory(Category $category, ?int $level = null): array
    {
        static $categorySubCategoriesCache = [];

        $categoryGuid = $category->getGuid();

        if (!isset($categorySubCategoriesCache[$categoryGuid])) {
            $conn = $this->getEntityManager()->getConnection();

            $categoryGuidsSql = <<<SQL
                WITH RECURSIVE category_tree AS (
                    SELECT 
                        guid,
                        name,
                        url,
                        1 AS level
                    FROM category
                    WHERE guid = :categoryGuid
                    AND publish_state = :CATEGORY_PUBLISH_ACTIVE
            
                    UNION ALL
            
                    SELECT 
                        c.guid,
                        c.name,
                        c.url,
                        ct.level + 1
                    FROM category c
                    INNER JOIN category_tree ct ON c.parent_id = ct.guid
                    WHERE c.publish_state = :CATEGORY_PUBLISH_ACTIVE
                )
                SELECT guid, name, url, level
                FROM category_tree
                ORDER BY level ASC
            SQL;

            $categoryGuidsWithLevels = $conn->executeQuery(
                $categoryGuidsSql,
                [
                    'categoryGuid' => $categoryGuid,
                    'CATEGORY_PUBLISH_ACTIVE' => CategoryPublishStateEnum::ACTIVE->value,
                ]
            )->fetchAllAssociative();

            $structuredCategories = [];

            foreach ($categoryGuidsWithLevels as $row) {
                $structuredCategories[$row['level']][] = [
                    'guid' => $row['guid'],
                    'name' => $row['name'],
                    'url' => $row['url'],
                ];
            }

            $categorySubCategoriesCache[$categoryGuid] = $structuredCategories;
        }

        $structuredCategories = $categorySubCategoriesCache[$categoryGuid];

        if (null !== $level) {
            return $structuredCategories[$level] ?? [];
        }

        $allGuids = [];

        foreach ($structuredCategories as $levelCategories) {
            foreach ($levelCategories as $categoryRow) {
                $allGuids[] = $categoryRow['guid'];
            }
        }

        return $allGuids;
    }
}
