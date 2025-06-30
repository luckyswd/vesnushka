<?php

namespace App\Controller;

use App\Handler\Cart\CartAddHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CartController extends BaseController
{
    #[Route('/cart', name: 'app_cart')]
    public function cart(): Response
    {
        dd(1);
    }

    #[Route('/api/cart/add', name: 'api_cart_add', methods: ['POST'])]
    public function add(CartAddHandler $cartAddHandler): JsonResponse
    {
        try {
            return $cartAddHandler->handler();
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
