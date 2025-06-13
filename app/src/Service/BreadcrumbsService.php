<?php

namespace App\Service;

use App\Entity\Category;
use App\Entity\Item;

class BreadcrumbsService
{
    public static function generateBreadcrumbs(Item|Category $entity): array
    {
        $breadcrumbs = [
            [
                'link' => '/',
                'text' => 'Главная',
            ],
        ];

        if ($entity instanceof Category) {
            $breadcrumbs = array_merge($breadcrumbs, self::getCategoryBreadcrumbs($entity));
        }

        if ($entity instanceof Item) {
            $categories = $entity->getCategories();

            if (!$categories->isEmpty()) {
                /** @var Category $category */
                $category = $categories->first();

                $breadcrumbs = array_merge($breadcrumbs, self::getCategoryBreadcrumbs($category));
            }

            $breadcrumbs[] = [
                'link' => null,
                'text' => $entity->getName(),
            ];
        }

        return $breadcrumbs;
    }

    private static function getCategoryBreadcrumbs(Category $category): array
    {
        $breadcrumbs = [];

        $current = $category;

        while ($current !== null) {
            array_unshift($breadcrumbs, [
                'link' => $current->getUrl(),
                'text' => $current->getName(),
            ]);
            $current = $current->getParent();
        }

        return $breadcrumbs;
    }
}
