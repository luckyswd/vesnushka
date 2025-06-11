<?php

namespace App\Controller;

use App\Handler\CatalogHandler;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CatalogController extends BaseController
{
    #[Route('/catalog/{path}', name: 'app_catalog', requirements: ['path' => '.+'], defaults: ['path' => ''])]
    public function show(string $path, CatalogHandler $catalogHandler): Response
    {
        try {
            $result = $catalogHandler($path);

            return new Response($result);
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
