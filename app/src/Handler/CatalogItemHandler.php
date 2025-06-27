<?php

namespace App\Handler;

use App\Entity\Category;
use App\Entity\Item;
use App\Repository\CategoryRepository;
use App\Repository\ItemRepository;
use App\Service\CatalogService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Twig\Environment;

readonly class CatalogItemHandler
{
    public function __construct(
        private CategoryRepository $categoryRepository,
        private ItemRepository $itemRepository,
        private Environment $twig,
        private FilterHandler $filterHandler,
    ) {
    }

    public function handle(string $path): Response
    {
        $url = CatalogService::getCatalogUrl(path: $path);

        // 1. Ищем товар
        $item = $this->itemRepository->findVisibleItemByUrl($url);

        if ($item) {
            return $this->renderItem(item: $item);
        }

        // 2. Ищем категорию
        $category = $this->categoryRepository->findVisibleCategoryByUrl($url);

        if ($category) {
            return $this->filterHandler->renderCatalog($category);
        }

        // 3. Пытаемся найти ближайшую категорию, постепенно убирая сегменты пути справа
        $redirectUrl = $this->findNearestCategoryRedirectUrl($path);

        if (null !== $redirectUrl) {
            return new RedirectResponse($redirectUrl, 301);
        }

        throw new NotFoundHttpException();
    }

    private function findNearestCategoryRedirectUrl(string $path): ?string
    {
        $pathParts = explode('/', trim($path, '/'));
        $pathsToTry = [];

        for ($i = count($pathParts); $i >= 0; --$i) {
            $reducedPath = implode('/', array_slice($pathParts, 0, $i));

            $pathsToTry[] = empty($reducedPath) ? Category::URL_CATALOG : CatalogService::getCatalogUrl(path: $reducedPath);
        }

        foreach ($pathsToTry as $url) {
            $category = $this->categoryRepository->findVisibleCategoryByUrl($url);

            if ($category) {
                return $category->getUrl();
            }
        }

        return null;
    }

    private function renderItem(Item $item): Response
    {
        return new Response($this->twig->render('template/front/item/item.html.twig', [
            'item' => $item,
            'similarItems' => $this->itemRepository->findSimilarItems(
                category: $item->getCategories()->last(),
                excludeItemGuid: $item->getGuid()),
        ]));
    }
}
