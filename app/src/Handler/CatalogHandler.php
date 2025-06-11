<?php

namespace App\Handler;

use App\Entity\Category;
use App\Entity\Item;
use App\Repository\CategoryRepository;
use App\Repository\ItemRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Twig\Environment;

readonly class CatalogHandler
{
    public function __construct(
        private CategoryRepository $categoryRepository,
        private ItemRepository $itemRepository,
        private Environment $twig,
    ) {
    }

    public function __invoke(string $path): string
    {
        $url = Category::URL_CATALOG.$path;

        $item = $this->itemRepository->findVisibleItemByUrl($url);

        if ($item) {
            return $this->renderItem(item: $item);
        }

        $category = $this->categoryRepository->findVisibleCategoryByUrl($url);

        if ($category) {
            return $this->renderCategory(category: $category);
        }

        throw new NotFoundHttpException();
    }

    private function renderItem(Item $item): string
    {
        return $this->twig->render('front/catalog/item.html.twig', [
            'item' => $item,
        ]);
    }

    private function renderCategory(Category $category): string
    {
        return $this->twig->render('front/catalog/catalog.html.twig', [
            'category' => $category,
        ]);
    }
}
