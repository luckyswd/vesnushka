<?php

namespace App\Service;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

readonly class UserService
{
    public function __construct(
        private TokenStorageInterface $tokenStorage,
    ) {
    }

    public function getUser(): ?User
    {
        $token = $this->tokenStorage->getToken();
        $user = null;

        if ($token && is_object($token->getUser()) && $token->getUser() instanceof User) {
            /** @var User $user */
            $user = $token->getUser();
        }

        return $user;
    }
}
