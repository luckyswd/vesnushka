<?php

namespace App\Handler;

use App\Repository\CategoryRepository;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class CatalogHandler
{
    public function __construct(
        private Environment $twig,
        private CategoryRepository $categoryRepository,
    ) {
    }

    public function handle(): Response
    {
        return new Response($this->twig->render('template/front/catalog/catalog.html.twig', [
            'categories' => $this->categoryRepository->findAllCategories(),
            'breadcrumbs' => [
                [
                    'link' => '/',
                    'text' => 'Главная',
                ],
                [
                    'link' => '/catalog',
                    'text' => 'Каталог',
                ],
            ],
        ]));
    }
}
