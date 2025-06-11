<?php

namespace App\Service;

use App\Entity\Category;
use App\Entity\Item;

class BreadcrumbsService
{
    public static function generateBreadcrumbsString(Item|Category $entity): string
    {
        $breadcrumbs = ['Главная'];

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

            $breadcrumbs[] = $entity->getName();
        }

        return implode(' / ', $breadcrumbs);
    }

    private static function getCategoryBreadcrumbs(Category $category): array
    {
        $breadcrumbs = [];

        $current = $category;

        while ($current !== null) {
            array_unshift($breadcrumbs, $current->getName());
            $current = $current->getParent();
        }

        return $breadcrumbs;
    }
}
