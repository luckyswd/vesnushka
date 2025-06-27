<?php

namespace App\Handler;

use App\Repository\BrandRepository;
use App\Repository\CategoryRepository;
use App\Repository\ItemRepository;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class HomeHandler
{
    public function __construct(
        private ItemRepository $itemRepository,
        private CategoryRepository $categoryRepository,
        private BrandRepository $brandRepository,
        private Environment $twig,
    )
    {}

    public function handle(): Response
    {
        return new Response($this->twig->render('template/front/home/index.html.twig', [
            'popularItems' => $this->itemRepository->findPopularItems(),
            'popularCategories' => $this->categoryRepository->findPopularCategories(),
            'popularBrands' => $this->brandRepository->findPopularBrands(),
        ]));
    }
}
