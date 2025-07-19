<?php

namespace App\Handler;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

readonly class UserRegisterHandler
{
    public function __construct(
        private RequestStack $requestStack,
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $hasher,
        private MailerInterface $mailer,
    ) {
    }

    public function handle(): User
    {
        $request = $this->requestStack->getCurrentRequest();
        $data = json_decode($request->getContent(), true);
        $email = $data['email'] ?? null;
        $phone = $data['phone'] ?? null;
        $password = $data['password'] ?? null;
        $firstName = $data['firstName'] ?? null;

        if (!$email || !$password || !$firstName || !$phone) {
            throw new HttpException(400, 'Пожалуйста, заполните все обязательные поля.');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new HttpException(400, 'Указан некорректный e-mail. Проверьте и попробуйте снова.');
        }

        if (!preg_match('/^\+375 \d{2} \d{3}-\d{2}-\d{2}$/', $phone)) {
            throw new HttpException(400, 'Номер должен быть в формате +375 99 999-99-99.');
        }

        if (mb_strlen($password) < 6) {
            throw new HttpException(400, 'Пароль слишком короткий. Он должен содержать минимум 6 символов.');
        }

        $existing = $this->em->getRepository(User::class)->findOneBy(['email' => $email]);

        if ($existing) {
            throw new HttpException(409, 'Этот e-mail уже зарегистрирован. Попробуйте восстановить доступ или используйте другой адрес.');
        }

        $code = random_int(100000, 999999);

        $user = new User();
        $user->setEmail($email);
        $user->setPhone($phone);
        $user->setFirstName($firstName);
        $user->setRoles(['ROLE_USER']);
        $user->setPassword($this->hasher->hashPassword($user, $password));
        $user->setConfirmationCode((string) $code);
        $user->setIsConfirmed(false);

        $this->em->persist($user);
        $this->em->flush();

        $message = (new Email())
            ->from(getenv('MAIL_FROM'))
            ->to($email)
            ->subject('Код подтверждения')
            ->text("Ваш код подтверждения: $code");

        try {
            $this->mailer->send($message);
        } catch (TransportExceptionInterface) {
            $this->em->remove($user);
            $this->em->flush();

            throw new HttpException(503, 'Не удалось отправить письмо с кодом подтверждения. Повторите позже.');
        }

        return $user;
    }
}
