<?php

namespace App\Handler;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

readonly class ResetPasswordHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private EntityManagerInterface $em,
        private MailerInterface $mailer,
    ) {
    }

    public function handle(string $token): void
    {
        /** @var User|null $user */
        $user = $this->userRepository->findOneBy(['resetPasswordToken' => $token]);

        if (!$user || $user->getResetTokenExpiresAt() < new \DateTime()) {
            throw new BadRequestHttpException('Ссылка недействительна или устарела.');
        }

        $newPasswordPlain = bin2hex(random_bytes(4));
        $hashedPassword = password_hash($newPasswordPlain, PASSWORD_DEFAULT);

        $user->setPassword($hashedPassword);
        $user->setResetPasswordToken(null);
        $user->setResetTokenExpiresAt(null);

        $this->em->flush();

        $emailMessage = (new Email())
            ->from(getenv('MAIL_FROM'))
            ->to($user->getEmail())
            ->subject('Ваш новый пароль')
            ->text("Ваш новый пароль: $newPasswordPlain");

        $this->mailer->send($emailMessage);
    }
}
