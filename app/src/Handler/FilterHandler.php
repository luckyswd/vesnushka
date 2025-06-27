<?php

namespace App\Handler;

use App\Entity\Brand;
use App\Entity\Category;
use App\Repository\BrandRepository;
use App\Repository\ItemRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class FilterHandler
{
    public function __construct(
        private ItemRepository $itemRepository,
        private Environment $twig,
        private RequestStack $requestStack,
        private BrandRepository $brandRepository,
    ) {
    }

    public function renderCatalog(Category|Brand|string $entity): Response
    {
        $request = $this->requestStack->getCurrentRequest();
        $page = max(1, (int) $request->get('page', 1));
        $sort = $request->get('sort', 'popular');
        $limit = Category::LISTING_LIMIT;
        $offset = ($page - 1) * $limit;

        $brandList = $this->brandRepository->findBrands();
        if ($entity instanceof Category) {
            $subCategories = $this->itemRepository->getSubCategoriesByCategory($entity, 2);
            $allItems = $this->itemRepository->findItemsByCatalog($entity, $sort);
        } elseif ($entity instanceof Brand) {
            $allItems = $this->itemRepository->findItemsByBrand($entity, $sort);
        } else {
            $allItems = $this->itemRepository->findItemsByCatalog(category: null, sort: $sort, search: $entity);
        }

        $clientMinPrice = $request->get('min_price') ? (float) $request->get('min_price') : null;
        $clientMaxPrice = $request->get('max_price') ? (float) $request->get('max_price') : null;

        if (!$entity instanceof Brand) {
            $brandFilterList = $this->getFilterListBrand($request->get('brand'));
        }

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

            $price = $item['price'];

            // --- Фильтр по цене ---
            $passesPriceFilter = true;
            if (null !== $clientMinPrice && $price < $clientMinPrice) {
                $passesPriceFilter = false;
            }
            if (null !== $clientMaxPrice && $price > $clientMaxPrice) {
                $passesPriceFilter = false;
            }

            // --- Фильтр по бренду ---
            $passesBrandFilter = true;
            if (!$entity instanceof Brand) {
                if (!empty($brandFilterList) && !in_array($brandName, $brandFilterList, true)) {
                    $passesBrandFilter = false;
                }
            }

            // --- Фильтр по атрибутам ---
            $passesAttributeFilter = true;
            if (!empty($attributeFilterList)) {
                foreach ($attributeFilterList as $attrType => $allowedValues) {
                    if (!isset($attrData[$attrType]) || !in_array($attrData[$attrType], $allowedValues, true)) {
                        $passesAttributeFilter = false;
                        break;
                    }
                }
            }

            // --- Бренд: если проходит атрибуты + цену, то учитываем ---
            if ($passesAttributeFilter && $passesPriceFilter) {
                if ($brandName) {
                    $brandStats[$brandGuid]['name'] = $brandName;
                    $brandStats[$brandGuid]['count'] = ($brandStats[$brandGuid]['count'] ?? 0) + 1;
                }
            }

            // --- Атрибуты: считаем статистику независимо ---
            foreach ($attrData as $attrName => $attrValue) {
                // временно исключаем текущий атрибут из фильтра
                $passesOtherAttrs = true;

                if (!empty($attributeFilterList)) {
                    foreach ($attributeFilterList as $filterName => $allowedValues) {
                        if ($filterName === $attrName) {
                            continue;
                        }
                        if (!isset($attrData[$filterName]) || !in_array($attrData[$filterName], $allowedValues, true)) {
                            $passesOtherAttrs = false;
                            break;
                        }
                    }
                }

                if ($passesOtherAttrs && $passesBrandFilter && $passesPriceFilter) {
                    $attributeStats[$attrName][$attrValue] = ($attributeStats[$attrName][$attrValue] ?? 0) + 1;
                }
            }

            // --- Основная фильтрация (для отображения) ---
            if (!$passesAttributeFilter || !$passesBrandFilter || !$passesPriceFilter) {
                continue;
            }

            // --- Минимальная/максимальная цена, если не задана пользователем ---
            if (null === $clientMinPrice && (null === $defaultMinPrice || $price < $defaultMinPrice)) {
                $defaultMinPrice = $price;
            }
            if (null === $clientMaxPrice && (null === $defaultMaxPrice || $price > $defaultMaxPrice)) {
                $defaultMaxPrice = $price;
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

        if ($entity instanceof Brand) {
            $brandStats = [];
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
                    'subCategories' => $subCategories ?? [],
                    'brands' => $brandStats,
                    'attributes' => $attributeStats,
                    'brandFilterList' => $brandFilterList ?? [],
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

        return new Response($this->twig->render('template/front/catalog/catalog-item.html.twig', [
            'name' => is_string($entity) ? 'Поиск' :$entity->getName(),
            'breadcrumbs' => $this->getBreadcrumbs($entity),
            'items' => $items,
            'itemsCount' => $this->formatItemsFound($matchedCount),
            'chips' => $selectedChips,
            'limit' => $limit,
            'filters' => [
                'subCategories' => $subCategories ?? [],
                'brands' => $brandStats,
                'attributes' => $attributeStats,
                'brandFilterList' => $brandFilterList ?? [],
                'attributeFilterList' => $attributeFilterList,
                'minPrice' => $clientMinPrice,
                'maxPrice' => $clientMaxPrice,
            ],
            'popularItems' => $this->itemRepository->findPopularItems(),
        ]));
    }

    private function getBreadcrumbs(Category|Brand|string $entity): array
    {
        if ($entity instanceof Category) {
            return $entity->getBreadcrumbs();
        }

        if ($entity instanceof Brand) {
            return [
                [
                    'link' => '/',
                    'text' => 'Главная',
                ],
                [
                    'link' => '/brands',
                    'text' => 'Каталог брендов',
                ],
                [
                    'link' => $entity->getUrl(),
                    'text' => $entity->getName(),
                ],
            ];
        }

        return [
            [
                'link' => '/',
                'text' => 'Главная',
            ],
            [
                'link' => '/catalog',
                'text' => 'Каталог',
            ],
            [
                'link' => '/search',
                'text' => 'Поиск',
            ],
        ];
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

        if (null !== $clientMinPrice || null !== $clientMaxPrice) {
            $minPrice = $clientMinPrice ?? $defaultMinPrice;
            $maxPrice = $clientMaxPrice ?? $defaultMaxPrice;

            $priceLabel = 'Цена:';
            if (null !== $minPrice) {
                $priceLabel .= ' от ' . $minPrice;
            }
            if (null !== $maxPrice) {
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
