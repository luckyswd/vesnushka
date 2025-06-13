<?php

namespace Handler;

use App\Handler\CatalogHandler;
use App\Repository\CategoryRepository;
use App\Repository\ItemRepository;
use Base\BaseTest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CatalogHandlerTest extends BaseTest
{
    private CategoryRepository $categoryRepository;
    private ItemRepository $itemRepository;
    private CatalogHandler $catalogHandler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->categoryRepository = $this->container->get(CategoryRepository::class);
        $this->itemRepository = $this->container->get(ItemRepository::class);
        $this->catalogHandler = $this->container->get(CatalogHandler::class);
    }

    public function testAllCategoriesAndItemsDoNotCause500Error(): void
    {
        $categories = $this->categoryRepository->findAll();
        $items = $this->itemRepository->findAll();

        // Проверяем все категории
        foreach ($categories as $category) {
            $this->checkEntityUrl($category);
        }

        // Проверяем все товары
        foreach ($items as $item) {
            $this->checkEntityUrl($item);
        }
    }

    private function checkEntityUrl($entity): void
    {
        $url = $entity->getUrl();
        $path = str_replace('/catalog/', '', $url);

        try {
            $response = ($this->catalogHandler)($path);

            $this->assertInstanceOf(Response::class, $response);
            $this->assertNotEquals(500, $response->getStatusCode(), "500 error for URL: $url");
        } catch (NotFoundHttpException $e) {
            $this->addToAssertionCount(1);
        } catch (\Throwable $e) {
            $this->fail("Unexpected exception for URL $url: " . $e->getMessage());
        }
    }
}
