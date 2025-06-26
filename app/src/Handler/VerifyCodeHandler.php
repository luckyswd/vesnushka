<?php

namespace App\Handler;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\HttpException;

readonly class VerifyCodeHandler
{
    public function __construct(
        private RequestStack $requestStack,
        private EntityManagerInterface $em,
        private Security $security,
    ) {
    }

    public function handle(): void
    {
        $request = $this->requestStack->getCurrentRequest();
        $data = json_decode($request->getContent(), true);

        $code = $data['code'] ?? null;
        $guid = $data['userGuid'] ?? null;

        if (!$code || !$guid) {
            throw new HttpException(400, 'Не переданы обязательные параметры.');
        }

        /** @var User|null $user */
        $user = $this->em->getRepository(User::class)->findOneBy(['guid' => $guid]);

        if (!$user) {
            throw new HttpException(404, 'Пользователь не найден.');
        }

        if ($user->getConfirmationCode() !== $code) {
            throw new HttpException(400, 'Неверный код подтверждения.');
        }

        if ($user->isConfirmed()) {
            throw new HttpException(409, 'Пользователь уже подтверждён.');
        }

        $user->setIsConfirmed(true);
        $user->setConfirmationCode(null);
        $this->em->flush();

        $this->security->login($user);
    }
}
