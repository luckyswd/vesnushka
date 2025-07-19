<?php

namespace App\Handler\Cart;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\Item;
use App\Repository\CartItemRepository;
use App\Service\CartService;
use App\Traits\ApiResponseTrait;
use Doctrine\ORM\EntityManagerInterface;

class CartItemHandler
{
    use ApiResponseTrait;

    public function __construct(
        private readonly CartService $cartService,
        private readonly CartItemRepository $cartItemRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function handle(Item $item, int $qty, bool $isAdd, bool $isRemove): Cart
    {
        if ($qty < 1) {
            $qty = 1;
        } elseif ($qty > 255) {
            $qty = 255;
        }

        $cart = $this->cartService->findOrCreateCart();
        $cartItem = $this->cartItemRepository->findOneBy(['cart' => $cart, 'item' => $item]);

        if ($isRemove) {
            if ($cartItem) {
                $cart->removeCartItem($cartItem);
                $this->entityManager->remove($cartItem);
                $this->entityManager->flush();
            }
        } else {
            if ($cartItem) {
                if ($isAdd) {
                    $cartItem->setQty($cartItem->getQty() + 1);
                } else {
                    $cartItem->setQty($qty);
                }
            } else {
                $cartItem = new CartItem();
                $cartItem->setCart($cart);
                $cartItem->setItem($item);
                $cartItem->setPrice($item->getDefaultPrice());
                $cartItem->setQty($qty);

                $cart->addCartItem($cartItem);
                $this->entityManager->persist($cartItem);
            }
        }

        $this->entityManager->flush();

        // Пересчёт суммы
        $total = 0;

        foreach ($cart->getCartItems() as $ci) {
            $total += $ci->getQty() * (float) $ci->getItem()->getDefaultPrice();
        }

        $cart->setTotalAmount(number_format($total, 2, '.', ''));

        $this->entityManager->flush();

        return $cart;
    }
}
