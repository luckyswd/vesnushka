<?php

namespace App\Handler\Cart;

use App\Entity\CartItem;
use App\Repository\CartItemRepository;
use App\Repository\ItemRepository;
use App\Service\CartService;
use App\Traits\ApiResponseTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

class CartAddHandler
{
    use ApiResponseTrait;

    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly CartService $cartService,
        private readonly CartItemRepository $cartItemRepository,
        private readonly ItemRepository $itemRepository,
        private readonly EntityManagerInterface $entityManager
    )
    {}

    public function handler(): JsonResponse
    {
        $data = json_decode($this->requestStack->getCurrentRequest()->getContent(), true);
        $sku = $data['sku'] ?? null;
        $qty = (int)($data['qty'] ?? 1);

        if ($qty < 1) {
            $qty = 1;
        }

        if (!$sku) {
            return $this->success([
                'message' => 'Не передан SKU товара',
                'status' => Response::HTTP_BAD_REQUEST,
            ], Response::HTTP_BAD_REQUEST);
        }

        $item = $this->itemRepository->findOneBy(['sku' => $sku]);

        if (!$item) {
            return $this->success([
                'message' => 'Товар не найден',
                'status' => Response::HTTP_NOT_FOUND,
            ], Response::HTTP_NOT_FOUND);
        }

        ['cart' => $cart, 'cookie' => $cookie] = $this->cartService->findOrCreateCart();

        $cartItem = $this->cartItemRepository->findOneBy(['cart' => $cart, 'item' => $item]);

        if ($cartItem) {
            $cartItem->setQty($cartItem->getQty() + $qty);
        } else {
            $cartItem = new CartItem();
            $cartItem->setCart($cart);
            $cartItem->setItem($item);
            $cartItem->setQty($qty);
            $this->entityManager->persist($cartItem);
        }

        $this->entityManager->flush();

        $total = 0;

        foreach ($cart->getCartItems() as $ci) {
            $total += $ci->getQty() * (float)$ci->getItem()->getDefaultPrice();
        }

        $cart->setTotalAmount(number_format($total, 2, '.', ''));

        $this->entityManager->flush();

        $response = $this->success([
            'success' => true,
            'message' => 'Товар успешно добавлен в корзину'
        ]);

        if ($cookie) {
            $response->headers->setCookie($cookie);
        }

        return $response;
    }
}
