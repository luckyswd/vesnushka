<?php

namespace App\Controller;

use App\Enum\DeliveryMethodEnum;
use App\Handler\Cart\CartHandler;
use App\Handler\Cart\CartItemHandler;
use App\Repository\CartRepository;
use App\Repository\ItemRepository;
use Doctrine\ORM\EntityManagerInterface;
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


    #[Route('/api/cart/{guid}', name: 'api_cart_update', methods: ['PATCH'])]
    public function updateCart(string $guid, Request $request, CartRepository $cartRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $cart = $cartRepository->findOneBy(['guid' => $guid]);

        if (!$cart) {
            return $this->success([
                'message' => 'Корзина не найдена',
                'status' => Response::HTTP_NOT_FOUND,
            ], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        $deliveryCity = $data['deliveryCity'] ?? null;
        $deliveryAddress = $data['deliveryAddress'] ?? null;
        $deliveryMethod = $data['deliveryMethod'] ?? null;

        if ($deliveryCity) {
            $cart->setDeliveryCity($deliveryCity);
        }

        if ($deliveryAddress) {
            $cart->setDeliveryAddress($deliveryAddress);
        }

        if ($deliveryMethod) {
            $enum = DeliveryMethodEnum::tryFrom($deliveryMethod);

            if ($enum !== null) {
                $cart->setDeliveryMethod($enum);
            }
        }

        $entityManager->flush();

        return $this->success(data: $cart, groups: ['json_cart']);
    }
}
