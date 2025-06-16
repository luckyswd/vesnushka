<?php

namespace App\Handler;

use App\Entity\Category;
use App\Entity\Item;
use App\Enum\CurrencyEnum;
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
        $limit = Category::LISTING_LIMIT;
        $offset = ($page - 1) * $limit;

        // Загрузка данных
        $subCategories = $this->itemRepository->getSubCategoriesByCategory($category, 2);
        $brandList = $this->brandRepository->findBrands();
        $allItems = $this->itemRepository->findItemsByCategory($category);

        // Фильтры из запроса
        $priceFilter = $request->get('price', []);
        $attributeFilter = $request->get('attributes', []);

        $items = [];
        $matchedCount = 0;
        $brandStats = [];
        $attributeStats = [];

        foreach ($allItems as $item) {
            $priceData = json_decode($item['price'], true);
            $attrData = json_decode($item['attributes'], true);

            // --- Фильтрация по цене ---
            if (!empty($priceFilter)) {
                $price = isset($priceData[CurrencyEnum::BYN->value]) ? $priceData[CurrencyEnum::BYN->value] / 100 : null;
                $min = $priceFilter['min'] ?? null;
                $max = $priceFilter['max'] ?? null;

                if (!is_numeric($price) || (isset($min) && $price < $min) || (isset($max) && $price > $max)) {
                    continue;
                }
            }

            // --- Фильтрация по атрибутам ---
            $matched = true;
            foreach ($attributeFilter as $attr => $value) {
                if (!isset($attrData[$attr]) || $attrData[$attr] !== $value) {
                    $matched = false;
                    break;
                }
            }

            if (!$matched) {
                continue;
            }

            // Совпадающий товар
            ++$matchedCount;

            // --- Сбор статистики по брендам ---
            $brandGuid = $item['brand_guid'];
            $brandStats[$brandGuid]['name'] = $brandList[$brandGuid]['name'];
            $brandStats[$brandGuid]['count'] = ($brandStats[$brandGuid]['count'] ?? 0) + 1;

            // --- Сбор статистики по атрибутам ---
            foreach ($attrData as $name => $value) {
                $attributeStats[$name][$value] = ($attributeStats[$name][$value] ?? 0) + 1;
            }

            // Пропускаем до offset
            if ($matchedCount <= $offset) {
                continue;
            }

            // Заполняем только текущую страницу
            if (count($items) < $limit) {
                $items[] = [
                    'guid' => $item['guid'],
                    'name' => $item['name'],
                    'sku' => $item['sku'],
                    'url' => $item['url'],
                    'price' => $priceData,
                    'brand' => $brandStats[$brandGuid]['name'] ?? 'Unknown',
                ];
            }
        }

        return $this->twig->render('template/front/catalog/catalog.html.twig', [
            'category' => $category,
            'breadcrumbs' => $category->getBreadcrumbs(),
            'items' => $items,
            'itemsCount' => $this->formatItemsFound($matchedCount),
            'currentPage' => $page,
            'totalPages' => ceil($matchedCount / $limit),
            'filters' => [
                'subCategories' => $subCategories,
                'brands' => $brandStats,
                'attributes' => $attributeStats,
            ],
        ]);
    }

    private function formatItemsFound(int $number): string
    {
        $mod10 = $number % 10;
        $mod100 = $number % 100;

        if (1 === $mod10 && 11 !== $mod100) {
            $word = 'товар';
        } elseif (in_array($mod10, [2, 3, 4]) && !in_array($mod100, [12, 13, 14])) {
            $word = 'товара';
        } else {
            $word = 'товаров';
        }

        return 'Найдено ' . $number . ' ' . $word;
    }
}
