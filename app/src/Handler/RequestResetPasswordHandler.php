<?php

namespace App\Handler;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

readonly class RequestResetPasswordHandler
{
    public function __construct(
        private RequestStack $requestStack,
        private UserRepository $userRepository,
        private EntityManagerInterface $em,
        private MailerInterface $mailer,
        private UrlGeneratorInterface $urlGenerator,
        private TokenGeneratorInterface $tokenGenerator,
    ) {
    }

    public function handle(): void
    {
        $request = $this->requestStack->getCurrentRequest();

        if (!$request) {
            throw new BadRequestHttpException('Невозможно получить текущий запрос.');
        }

        $data = $request->toArray();
        $email = $data['email'] ?? null;

        if (!$email) {
            throw new BadRequestHttpException('Email обязателен.');
        }

        /** @var User|null $user */
        $user = $this->userRepository->findOneBy(['email' => $email]);

        if (!$user) {
            throw new NotFoundHttpException('Пользователь не найден.');
        }

        $token = $this->tokenGenerator->generateToken();
        $user->setResetPasswordToken($token);
        $user->setResetTokenExpiresAt(new \DateTime('+1 hour'));
        $this->em->flush();

        $resetUrl = $this->urlGenerator->generate(
            'api_reset_password',
            ['token' => $token],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $emailMessage = (new Email())
            ->from(getenv('MAIL_FROM'))
            ->to($user->getEmail())
            ->subject('Сброс пароля')
            ->text("Перейдите по ссылке для сброса пароля: $resetUrl");

        $this->mailer->send($emailMessage);
    }
}
