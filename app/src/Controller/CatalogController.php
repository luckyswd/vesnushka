<?php

namespace App\Controller;

use App\Handler\CatalogHandler;
use App\Handler\CatalogItemHandler;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CatalogController extends BaseController
{
    #[Route('/catalog', name: 'app_catalog')]
    public function catalog(CatalogHandler $catalogHandler): Response
    {
        try {
            return $catalogHandler->handle();
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    #[Route('/catalog/{path}', name: 'app_catalog_path', requirements: ['path' => '.+'], defaults: ['path' => ''])]
    public function catalogItem(
        string $path,
        CatalogItemHandler $catalogItemHandler,
    ): Response {
        try {
            return $catalogItemHandler->handle($path);
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
