<?php

namespace App\Handler\Cart;

use App\Enum\DeliveryMethodEnum;
use App\Service\CartService;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class CartHandler
{
    public function __construct(
        private Environment $twig,
        private CartService $cartService,
    )
    {}

    public function handel(): Response
    {
        $cart = $this->cartService->findOrCreateCart();
        $itemCount = $cart->getCountCartItems();

        $cartItems = $cart->getCartItems()->toArray();

        usort($cartItems, function($a, $b) {
            return $a->getCreatedAt() <=> $b->getCreatedAt();
        });

        return new Response($this->twig->render('template/front/cart/cart.html.twig', [
            'totalAmount' => $cart->getTotalAmount(),
            'cartItems' => $cartItems,
            'itemCount' => $itemCount,
            'itemCountText' => $this->pluralizeItems($itemCount),
            'deliveryCity' => $cart->getDeliveryCity(),
            'deliveryMethods' => DeliveryMethodEnum::cases(),
        ]));
    }

    /**
     * Склонение слова по числу для русского языка
     */
    private function pluralizeItems(int $number): string
    {
        $mod10 = $number % 10;
        $mod100 = $number % 100;

        if ($mod10 == 1 && $mod100 != 11) {
            return 'товар';
        }

        if ($mod10 >= 2 && $mod10 <= 4 && !($mod100 >= 12 && $mod100 <= 14)) {
            return 'товара';
        }

        return 'товаров';
    }
}
