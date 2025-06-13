<?php

namespace App\Handler;

use App\Entity\Category;
use App\Entity\Item;
use App\Repository\BrandRepository;
use App\Repository\CategoryRepository;
use App\Repository\ItemRepository;
use App\Service\CatalogService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Twig\Environment;

readonly class CatalogHandler
{
    public function __construct(
        private CategoryRepository $categoryRepository,
        private ItemRepository $itemRepository,
        private Environment $twig,
        private RequestStack $requestStack,
        private BrandRepository $brandRepository,
    ) {
    }

    public function __invoke(string $path): Response
    {
        // 0. Если url catalog,
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
        $request = $this->requestStack->getCurrentRequest();
        $page = max(1, (int) $request->get('page', 1));

        $subCategories = $this->itemRepository->getSubCategoriesByCategory($category, 2);
        $brands = $this->brandRepository->findBrands();

        $items = $this->itemRepository->findItemsByCategory(category: $category);

        $attributes = [];

        /** @var Item $item */
        foreach ($items as $item) {
            $brandGuid = $item['brand_guid'];
            $itemGuid = $item['guid'];
            $brand = $brands[$brandGuid];

            if (!isset($brands[$brandGuid])) {
                $brands[$brandGuid] = [
                    'name' => $brand['name'],
                    'count' => 0,
                ];
            }

            ++$brands[$brandGuid]['count'];

//            $itemAttrs = $itemsAttributes[$itemGuid];

//            foreach ($itemAttrs as $attr) {
//                $attributeName = $attr['name'];
//                $attributeValue = $attr['value'];
//
//                if (!isset($attributes[$attributeName])) {
//                    $attributes[$attributeName] = [];
//                }
//
//                if (!isset($attributes[$attributeName][$attributeValue])) {
//                    $attributes[$attributeName][$attributeValue] = 0;
//                }
//
//                ++$attributes[$attributeName][$attributeValue];
//            }
        }

        return $this->twig->render('template/front/catalog/catalog.html.twig', [
            'category' => $category,
            'breadcrumbs' => $category->getBreadcrumbs(),
            'subCategories' => $subCategories,
            'items' => $items,
            'brands' => $brands,
            'attributes' => $attributes,
            'itemsCount' => count($items),
        ]);
    }
}
