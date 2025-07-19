<?php

namespace App\Service;

use App\Entity\Cart;
use App\Enum\PaymentStatusEnum;
use App\Repository\CartRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Uid\Uuid;

readonly class CartService
{
    public function __construct(
        private EntityManagerInterface $em,
        private CartRepository $cartRepository,
        private RequestStack $requestStack,
        private UserService $userService,
    ) {
    }

    /**
     * Найти или создать корзину для текущего пользователя или гостя.
     */
    public function findOrCreateCart(): Cart
    {
        $request = $this->requestStack->getCurrentRequest();
        $sessionToken = $request?->cookies->get('cart_token');
        $user = $this->userService->getUser();

        $cart = null;
        $cookie = null;

        if ($user) {
            $cart = $this->cartRepository->findOneBy(['user' => $user, 'active' => true]);
        }

        if (!$cart && $sessionToken) {
            $cart = $this->cartRepository->findOneBy(['sessionToken' => $sessionToken, 'active' => true]);
        }

        // если корзина нашлась по токену и теперь есть пользователь — привяжем
        if ($cart && $user && !$cart->getUser()) {
            $cart->setUser($user);
            $cart->setSessionToken(null);
            $this->em->flush();
        }

        // если корзины нет — создаём
        if (!$cart) {
            if (!$sessionToken) {
                $sessionToken = Uuid::v4()->toRfc4122();
            }

            $cart = new Cart();
            $cart->setPaymentStatus(PaymentStatusEnum::NEW);
            $cart->setTotalAmount('0');
            $cart->setActive(true);

            if ($user) {
                $cart->setUser($user);
            } else {
                $cart->setSessionToken($sessionToken);
            }

            $this->em->persist($cart);
            $this->em->flush();

            if (!$user) {
                $cookie = Cookie::create(
                    'cart_token',
                    $sessionToken,
                    (new \DateTime())->modify('+30 days')
                );
            }
        }

        // если надо установить cookie — кладём в Request attributes
        if ($cookie) {
            $request->attributes->set('_set_cart_cookie', $cookie);
        }

        return $cart;
    }
}
