<?php

namespace App\Handler;

use App\Entity\Category;
use App\Entity\Item;
use App\Enum\CategoryPublishStateEnum;
use App\Repository\CategoryRepository;
use App\Repository\ItemRepository;
use App\Service\CatalogService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
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

    public function __invoke(string $path): Response
    {
        if (empty($path)) {
            $category = $this->categoryRepository->findOneBy(['url' => Category::URL_CATALOG]);

            if ($category) {
                return new Response($this->renderCategory(category: $category));
            }
        }

        $url = CatalogService::getCatalogUrl(path: $path);

        // 1. Ищем товар
        $item = $this->itemRepository->findVisibleItemByUrl($url);

        if ($item) {
            return new Response($this->renderItem(item: $item));
        }

        // 2. Ищем категорию
        $category = $this->categoryRepository->findVisibleCategoryByUrl($url);

        if ($category) {
            return new Response($this->renderCategory(category: $category));
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

    private function renderItem(Item $item): string
    {
        return $this->twig->render('template/front/catalog/item.html.twig', [
            'item' => $item,
        ]);
    }

    private function renderCategory(Category $category): string
    {
        $subCategories = $category->getChildren()->filter(
            fn (Category $child) => CategoryPublishStateEnum::ACTIVE === $child->getPublishState()
        );

        $items = $this->itemRepository->findByCategoryAndSubCategoriesWithBrands($category, $subCategories);

        $brands = [];

        /** @var Item $item */
        foreach ($items as $item) {
            $brand = $item->getBrand();
            if (!in_array($brand, $brands, true)) {
                $brands[] = $brand;
            }
        }

        return $this->twig->render('template/front/catalog/catalog.html.twig', [
            'category' => $category,
            'breadcrumbs' => $category->getBreadcrumbs(),
            'subCategories' => $subCategories,
            'items' => $items,
            'brands' => $brands,
        ]);
    }
}
