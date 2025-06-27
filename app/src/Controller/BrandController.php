<?php

namespace App\Controller;

use App\Handler\BrandsHandler;
use App\Handler\HomeHandler;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class BrandController extends BaseController
{
    #[Route('/brands', name: 'app_brand')]
    public function brands(BrandsHandler $brandsHandler): Response {
        try {
            return $brandsHandler->getBrands();
        } catch (\Throwable $e) {
            throw $e;
        }
    }
    #[Route('/brands/{path}', name: 'app_brand_path')]
    public function brand(
        string $path,
    ): Response {
        try {
            return $catalogItemHandler->handle($path);
        } catch (\Throwable $e) {
            throw $e;
        }
    }

}