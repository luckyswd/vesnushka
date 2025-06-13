<?php

namespace App\EventListener\Doctrine;

use App\Entity\Category;
use App\Entity\Item;
use App\Service\BreadcrumbsService;
use App\Service\TextService;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\Persistence\Event\LifecycleEventArgs;

readonly class UrlAndBreadcrumbsListener
{
    public function prePersist(object $entity, LifecycleEventArgs $event): void
    {
        $this->updateUrlAndBreadcrumbsIfNeeded($entity);
    }

    public function preUpdate(object $entity, PreUpdateEventArgs $event): void
    {
        if ($entity instanceof Category) {
            if ($event->hasChangedField('name') || $event->hasChangedField('url')) {
                $this->updateUrlAndBreadcrumbsIfNeeded($entity);
            }
        }

        if ($entity instanceof Item) {
            if ($event->hasChangedField('name') || $event->hasChangedField('url')) {
                $this->updateUrlAndBreadcrumbsIfNeeded($entity);
            }
        }
    }

    private function updateUrlAndBreadcrumbsIfNeeded(object $entity): void
    {
        if ($entity instanceof Category) {
            $parent = $entity->getParent();

            if ($parent) {
                $url = $parent->getUrl().'/'.TextService::transliterate($entity->getName());
            } else {
                $url = Category::URL_CATALOG;
            }

            $entity->setUrl($url);

            $breadcrumbs = BreadcrumbsService::generateBreadcrumbs($entity);

            $entity->setBreadcrumbs($breadcrumbs);
        }

        if ($entity instanceof Item) {
            $categories = $entity->getCategories();

            $category = $this->getDeepestCategory($categories);

            $categoryUrl = $category->getUrl();
            $slug = TextService::transliterate($entity->getName());

            $url = rtrim($categoryUrl, '/').'/'.$slug;

            $entity->setUrl($url);

            $breadcrumbs = BreadcrumbsService::generateBreadcrumbs($entity);

            $entity->setBreadcrumbs($breadcrumbs);
        }
    }

    private function getDeepestCategory(iterable $categories): Category
    {
        $deepestCategory = null;
        $maxDepth = -1;

        foreach ($categories as $category) {
            $depth = $this->calculateCategoryDepth($category);

            if ($depth > $maxDepth) {
                $maxDepth = $depth;
                $deepestCategory = $category;
            }
        }

        return $deepestCategory;
    }

    private function calculateCategoryDepth(Category $category): int
    {
        $depth = 0;

        while (null !== $category->getParent()) {
            ++$depth;
            $category = $category->getParent();
        }

        return $depth;
    }
}
