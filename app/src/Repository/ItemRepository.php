<?php

namespace App\Repository;

use App\Entity\Category;
use App\Entity\Item;
use App\Enum\ItemPublishStateEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Item>
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

    public function findByCategoryAndSubCategoriesWithBrands(
        Category $category,
        iterable $subCategories,
    ): array {
        $qb = $this->createQueryBuilder('i')
            ->leftJoin('i.categories', 'c')
            ->leftJoin('i.brand', 'b')
            ->addSelect('b')
            ->where('c = :category OR c IN (:subCategories)')
            ->setParameter('category', $category)
            ->setParameter('subCategories', $subCategories);

        return $qb->getQuery()->getResult();
    }
}
