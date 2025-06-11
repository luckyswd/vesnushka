<?php

namespace App\Repository;

use App\Entity\Category;
use App\Enum\CategoryPublishStateEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Category>
 */
class CategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }

    public function findVisibleCategoryByUrl(string $url): ?Category
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.url = :url')
            ->andWhere('c.publishState = :ACTIVE')
            ->setParameter('url', $url)
            ->setParameter('ACTIVE', CategoryPublishStateEnum::ACTIVE->value)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
