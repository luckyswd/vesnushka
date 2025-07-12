<?php

namespace App\Service;

use App\Entity\Cart;
use App\Entity\User;
use App\Enum\PaymentStatusEnum;
use App\Repository\CartRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Uid\Uuid;

class CartService
{
    private EntityManagerInterface $em;
    private CartRepository $cartRepository;
    private RequestStack $requestStack;
    private TokenStorageInterface $tokenStorage;

    public function __construct(
        EntityManagerInterface $em,
        CartRepository $cartRepository,
        RequestStack $requestStack,
        TokenStorageInterface $tokenStorage
    ) {
        $this->em = $em;
        $this->cartRepository = $cartRepository;
        $this->requestStack = $requestStack;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * Найти или создать корзину для текущего пользователя или гостя
     */
    public function findOrCreateCart(): Cart
    {
        $request = $this->requestStack->getCurrentRequest();
        $sessionToken = $request?->cookies->get('cart_token');

        $token = $this->tokenStorage->getToken();
        $user = null;

        if ($token && is_object($token->getUser()) && $token->getUser() instanceof User) {
            /** @var User $user */
            $user = $token->getUser();
        }

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
