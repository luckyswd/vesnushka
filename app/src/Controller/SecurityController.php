<?php

namespace App\Controller;

use App\Handler\UserRegisterHandler;
use App\Handler\VerifyCodeHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Attribute\Route;

class SecurityController extends BaseController
{
    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function login(): never
    {
        throw new \LogicException('This should be handled by the authenticator.');
    }

    #[Route('/api/register', name: 'api_user_register', methods: ['POST'])]
    public function register(UserRegisterHandler $userRegisterHandler): JsonResponse
    {
        try {
            $user = $userRegisterHandler->handle();
        } catch (HttpException $e) {
            return $this->error($e->getMessage(), $e->getStatusCode());
        } catch (\Throwable $e) {
            return $this->error(getenv('ERROR_MESSAGE'));
        }

        return $this->success(
            [
                'message' => 'На указанный e-mail отправлен код подтверждения. Проверьте почту.',
                'userGuid' => $user->getGuid(),
            ],
            Response::HTTP_CREATED
        );
    }

    #[Route('/api/verify-code', name: 'api_user_verify-code', methods: ['POST'])]
    public function verifyCode(VerifyCodeHandler $verifyCodeHandler): JsonResponse
    {
        try {
            $verifyCodeHandler->handle();
        } catch (HttpException $e) {
            return $this->error($e->getMessage(), $e->getStatusCode());
        } catch (\Throwable $e) {
            return $this->error($_ENV['ERROR_MESSAGE']);
        }

        return $this->success(
            [
                'message' => 'Вы успешно вошли в систему.',
            ],
        );
    }
}
