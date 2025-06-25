<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class SecurityController extends BaseController
{
    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function login(): never
    {
        throw new \LogicException('This should be handled by the authenticator.');
    }

    #[Route('/api/test', name: 'api_test', methods: ['GET'])]
    public function createAdmin(EntityManagerInterface $em, UserPasswordHasherInterface $hasher): JsonResponse
    {
        $user = new User();
        $user->setEmail('admin@example.com');
        $user->setRoles(['ROLE_ADMIN']);

        $hashedPassword = $hasher->hashPassword($user, 'admin');
        $user->setPassword($hashedPassword);

        $em->persist($user);
        $em->flush();

        return new JsonResponse(['status' => 'admin created']);
    }
}
