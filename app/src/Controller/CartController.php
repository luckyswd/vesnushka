<?php

namespace App\Controller;

use App\Handler\Cart\CartHandler;
use App\Handler\Cart\CartItemHandler;
use App\Repository\ItemRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CartController extends BaseController
{
    #[Route('/cart', name: 'app_cart')]
    public function cart(CartHandler $cartHandler): Response
    {
        return $cartHandler->handel();
    }

    #[Route('/api/cart/items/{sku}', name: 'api_cart_items_update', methods: ['POST'])]
    public function updateCartItem(
        string $sku,
        Request $request,
        CartItemHandler $cartItemHandler,
        ItemRepository $itemRepository,
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $qty = (int) ($data['qty'] ?? 1);
        $isAdd = (bool) ($data['add'] ?? false);
        $isRemove = (bool) ($data['remove'] ?? false);

        if (!$sku) {
            return $this->success([
                'message' => 'Не передан SKU товара',
                'status' => Response::HTTP_BAD_REQUEST,
            ], Response::HTTP_BAD_REQUEST);
        }

        $item = $itemRepository->findOneBy(['sku' => $sku]);

        if (!$item) {
            return $this->success([
                'message' => 'Товар не найден',
                'status' => Response::HTTP_NOT_FOUND,
            ], Response::HTTP_NOT_FOUND);
        }

        $cart = $cartItemHandler->handle(item: $item, qty: $qty, isAdd: $isAdd, isRemove: $isRemove);

        return $this->success(data: $cart, groups: ['json_cart']);
    }
}
