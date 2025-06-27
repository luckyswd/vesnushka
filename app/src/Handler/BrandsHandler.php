<?php

namespace App\Handler;

use App\Entity\Brand;
use App\Repository\BrandRepository;
use App\Repository\ItemRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Twig\Environment;

class BrandsHandler
{
    public function __construct(
        private Environment $twig,
        private BrandRepository $brandRepository,
        private ItemRepository $itemRepository,
        private FilterHandler $filterHandler,
    ) {
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
            ],
        ]));
    }

    public function getBrand(string $url): Response
    {
        $brand = $this->brandRepository->findOneBy(['url' => Brand::DEFAULT_PATH . $url]);

        if (!$brand) {
            throw new NotFoundHttpException();
        }

        return $this->filterHandler->renderCatalog($brand);
    }
}
