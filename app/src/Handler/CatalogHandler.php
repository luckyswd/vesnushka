<?php

namespace App\Handler;

use App\Entity\Category;
use App\Entity\Item;
use App\Repository\BrandRepository;
use App\Repository\CategoryRepository;
use App\Repository\ItemRepository;
use App\Service\CatalogService;
use Symfony\Component\HttpFoundation\JsonResponse;
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
                return $this->renderCategory(category: $category);
            }
        }

        $url = CatalogService::getCatalogUrl(path: $path);

        // 1. Ищем товар
        $item = $this->itemRepository->findVisibleItemByUrl($url);

        if ($item) {
            return $this->renderItem(item: $item);
        }

        // 2. Ищем категорию
        $category = $this->categoryRepository->findVisibleCategoryByUrl($url);

        if ($category) {
            return $this->renderCategory(category: $category);
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
        return new Response($this->twig->render('template/front/catalog/item.html.twig', [
            'item' => $item,
        ]));
    }

    private function renderCategory(Category $category): Response
    {
        $request = $this->requestStack->getCurrentRequest();
        $page = max(1, (int) $request->get('page', 1));
        $sort = $request->get('sort', 'popular');
        $limit = Category::LISTING_LIMIT;
        $offset = ($page - 1) * $limit;

        $subCategories = $this->itemRepository->getSubCategoriesByCategory($category, 2);
        $brandList = $this->brandRepository->findBrands();
        $allItems = $this->itemRepository->findItemsByCategory($category, $sort);

        // --- Получаем фильтры ---
        $clientMinPrice = $request->get('min_price') ? (float)$request->get('min_price') : null;
        $clientMaxPrice = $request->get('max_price') ? (float)$request->get('max_price') : null;

        $brandFilterList = $this->getFilterListBrand($request->get('brand'));
        $attributeFilterList = $this->getFilterListAttribute($request->get('attribute'));

        $items = [];
        $matchedCount = 0;
        $brandStats = [];
        $attributeStats = [];

        $defaultMinPrice = null;
        $defaultMaxPrice = null;

        foreach ($allItems as $item) {
            $attrData = json_decode($item['attributes'], true);
            $brandGuid = $item['brand_guid'];
            $brandName = $brandList[$brandGuid]['name'] ?? null;

            $price = $item['price'] / 100;

            // --- Проверка фильтра по атрибутам ---
            $passesAttributeFilter = true;
            if (!empty($attributeFilterList)) {
                foreach ($attributeFilterList as $attrType => $allowedValues) {
                    if (!isset($attrData[$attrType]) || !in_array($attrData[$attrType], $allowedValues, true)) {
                        $passesAttributeFilter = false;
                        break;
                    }
                }
            }

            // --- Проверка фильтра по брендам ---
            $passesBrandFilter = true;
            if (!empty($brandFilterList) && !in_array($brandName, $brandFilterList, true)) {
                $passesBrandFilter = false;
            }

            // --- Проверка фильтра по цене ---
            $passesPriceFilter = true;
            if ($clientMinPrice !== null && $price < (float)$clientMinPrice) {
                $passesPriceFilter = false;
            }
            if ($clientMaxPrice !== null && $price > (float)$clientMaxPrice) {
                $passesPriceFilter = false;
            }

            // --- Статистика по брендам — если проходят атрибуты и цену ---
            if ($passesAttributeFilter && $passesPriceFilter) {
                if ($brandName) {
                    $brandStats[$brandGuid]['name'] = $brandName;
                    $brandStats[$brandGuid]['count'] = ($brandStats[$brandGuid]['count'] ?? 0) + 1;
                }
            }

            // --- Применяем все фильтры (для отображения и остальных расчётов) ---
            if (!$passesAttributeFilter || !$passesBrandFilter || !$passesPriceFilter) {
                continue;
            }

            // --- min/max только если клиент их не задал ---
            if ($clientMinPrice === null && ($defaultMinPrice === null || $price < $defaultMinPrice)) {
                $defaultMinPrice = $price;
            }
            if ($clientMaxPrice === null && ($defaultMaxPrice === null || $price > $defaultMaxPrice)) {
                $defaultMaxPrice = $price;
            }

            foreach ($attrData as $name => $value) {
                $attributeStats[$name][$value] = ($attributeStats[$name][$value] ?? 0) + 1;
            }

            ++$matchedCount;

            if ($matchedCount <= $offset) {
                continue;
            }

            if (count($items) < $limit) {
                $items[] = [
                    'guid' => $item['guid'],
                    'name' => $item['name'],
                    'sku' => $item['sku'],
                    'url' => $item['url'],
                    'price' => $price,
                    'brand' => $brandName,
                    'main_image_path' => $item['main_image_path'],
                    'stock' => $item['stock'],
                ];
            }
        }

        // --- Сортировка фильтров по алфавиту ---
        foreach ($attributeStats as &$values) {
            ksort($values, SORT_STRING | SORT_FLAG_CASE);
        }
        unset($values);

        ksort($brandStats, SORT_STRING | SORT_FLAG_CASE);
        ksort($attributeStats, SORT_STRING | SORT_FLAG_CASE);

        // --- Собираем выбранные чипсы ---
        $selectedChips = $this->getSelectedChips(
            brandFilterList: $brandFilterList ?? [],
            attributeFilterList: $attributeFilterList ?? [],
            clientMinPrice: $clientMinPrice ?? null,
            clientMaxPrice: $clientMaxPrice ?? null,
            defaultMinPrice: $defaultMinPrice,
            defaultMaxPrice: $defaultMaxPrice,
        );

        if ($request->isXmlHttpRequest()) {
            $itemsHtml = $this->twig->render('template/front/components/item-card.html.twig', [
                'items' => $items,
            ]);

            $filtersHtml = $this->twig->render('template/front/components/filters.html.twig', [
                'filters' => [
                    'subCategories' => $subCategories,
                    'brands' => $brandStats,
                    'attributes' => $attributeStats,
                    'brandFilterList' => $brandFilterList,
                    'attributeFilterList' => $attributeFilterList,
                    'minPrice' => $clientMinPrice,
                    'maxPrice' => $clientMaxPrice,
                ],
            ]);

            $chipsHtml = $this->twig->render('template/front/components/chips.html.twig', [
                'chips' => $selectedChips,
            ]);

            return new JsonResponse([
                'items' => $itemsHtml,
                'itemsCount' => $this->formatItemsFound($matchedCount),
                'filters' => $filtersHtml,
                'chips' => $chipsHtml,
            ]);
        }

        return new Response($this->twig->render('template/front/catalog/catalog.html.twig', [
            'category' => $category,
            'breadcrumbs' => $category->getBreadcrumbs(),
            'items' => $items,
            'itemsCount' => $this->formatItemsFound($matchedCount),
            'chips' => $selectedChips,
            'filters' => [
                'subCategories' => $subCategories,
                'brands' => $brandStats,
                'attributes' => $attributeStats,
                'brandFilterList' => $brandFilterList,
                'attributeFilterList' => $attributeFilterList,
                'minPrice' => $clientMinPrice,
                'maxPrice' => $clientMaxPrice,
            ],
        ]));
    }
    private function getSelectedChips(
        array $brandFilterList,
        array $attributeFilterList,
        ?float $clientMinPrice = null,
        ?float $clientMaxPrice = null,
        ?float $defaultMinPrice = null,
        ?float $defaultMaxPrice = null,
    ): array {
        $chips = [];

        foreach ($brandFilterList as $brand) {
            $chips[] = [
                'label' => 'Бренд: ' . $brand,
                'type' => 'brand',
                'value' => $brand,
            ];
        }

        foreach ($attributeFilterList as $attributeName => $values) {
            foreach ($values as $value) {
                $chips[] = [
                    'label' => $attributeName . ': ' . $value,
                    'type' => 'attribute',
                    'value' => $attributeName . '_' . $value,
                ];
            }
        }

        if ($clientMinPrice !== null || $clientMaxPrice !== null) {
            $minPrice = $clientMinPrice ?? $defaultMinPrice;
            $maxPrice = $clientMaxPrice ?? $defaultMaxPrice;

            $priceLabel = 'Цена:';
            if ($minPrice !== null) {
                $priceLabel .= ' от ' . $minPrice;
            }
            if ($maxPrice !== null) {
                $priceLabel .= ' до ' . $maxPrice;
            }

            $chips[] = [
                'label' => $priceLabel,
                'type' => 'price',
                'value' => 'price',
            ];
        }

        return $chips;
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

    private function getFilterListBrand(?string $filter): ?array
    {
        if (!$filter) {
            return null;
        }

        return explode(',', $filter);
    }

    private function getFilterListAttribute(?string $filter): ?array
    {
        if (!$filter) {
            return null;
        }

        $attributes = explode(',', $filter);

        $result = [];

        foreach ($attributes as $attribute) {
            $data = explode('_', $attribute);

            $result[$data[0]][] = $data[1];
        }

        return $result;
    }
}
