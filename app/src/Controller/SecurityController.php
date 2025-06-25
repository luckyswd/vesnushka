<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class SecurityController extends BaseController
{
    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function login(): never
    {
        throw new \LogicException('This should be handled by the authenticator.');
    }

    #[Route('/api/register', name: 'api_user_register', methods: ['POST'])]
    public function register(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher,
        //  MailerInterface $mailer
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;
        $firstName = $data['firstName'] ?? null;

        if (!$email || !$password || !$firstName) {
            return $this->error('Пожалуйста, заполните все обязательные поля.', Response::HTTP_BAD_REQUEST);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->error('Указан некорректный e-mail. Проверьте и попробуйте снова.', Response::HTTP_BAD_REQUEST);
        }

        if (mb_strlen($password) < 6) {
            return $this->error('Пароль слишком короткий. Он должен содержать минимум 6 символов.', Response::HTTP_BAD_REQUEST);
        }

        $existingUser = $em->getRepository(User::class)->findOneBy(['email' => $email]);

        if ($existingUser) {
            return $this->error('Этот e-mail уже зарегистрирован. Попробуйте восстановить доступ или используйте другой адрес.', Response::HTTP_CONFLICT);
        }

        $code = random_int(100000, 999999);

        $user = new User();
        $user->setEmail($email);
        $user->setRoles(['ROLE_USER']);
        $user->setFirstName($firstName);
        $user->setPassword($hasher->hashPassword($user, $password));
        $user->setConfirmationCode((string) $code);
        $user->setIsConfirmed(false);

        $em->persist($user);
        $em->flush();

        // Отправка кода на email
        //    $emailMessage = (new Email())
        //        ->from('noreply@example.com')
        //        ->to($email)
        //        ->subject('Код подтверждения')
        //        ->text("Ваш код подтверждения: $code");
        //
        //    $mailer->send($emailMessage);

        return $this->success(['message' => 'На указанный e-mail отправлен код подтверждения. Проверьте почту.'], Response::HTTP_CREATED);
    }
}
