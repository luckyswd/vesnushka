<?php

namespace App\Handler;

use App\Repository\BrandRepository;
use App\Repository\ItemRepository;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class BrandsHandler
{
    public function __construct(
        private Environment $twig,
        private BrandRepository $brandRepository,
        private ItemRepository $itemRepository,
    )
    {
    }

    public function getBrands(): Response
    {
        return new Response($this->twig->render('template/front/brands/brands.html.twig', [
            'brands' => $this->brandRepository->findAll(),
            'popularItems' => $this->itemRepository->findPopularItems(),
            'breadcrumbs' => [
                [
                    'link' => '/',
                    'text' => 'Главная',
                ],
                [
                    'link' => '/brands',
                    'text' => 'Каталог брендов',
                ],
            ]
        ]));
    }
}
