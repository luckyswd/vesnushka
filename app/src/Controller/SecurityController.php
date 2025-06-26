<?php

namespace App\Controller;

use App\Handler\RequestResetPasswordHandler;
use App\Handler\ResetPasswordHandler;
use App\Handler\UserRegisterHandler;
use App\Handler\VerifyCodeHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
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
            return $this->error(getenv('ERROR_MESSAGE'));
        }

        return $this->success(
            [
                'message' => 'Вы успешно вошли в систему.',
            ],
        );
    }

    #[Route('/api/request-reset-password', name: 'api_request_reset_password', methods: ['POST'])]
    public function requestResetPassword(RequestResetPasswordHandler $handler): JsonResponse
    {
        try {
            $handler->handle();
        } catch (HttpException $e) {
            return $this->error($e->getMessage(), $e->getStatusCode());
        } catch (\Throwable $e) {
            return $this->error(getenv('ERROR_MESSAGE'));
        }

        return $this->success(['message' => 'Ссылка для сброса пароля отправлена на почту.']);
    }

    #[Route('/api/reset-password/{token}', name: 'api_reset_password', methods: ['GET'])]
    public function resetPassword(string $token, ResetPasswordHandler $handler): RedirectResponse
    {
        try {
            $handler->handle($token);
            $message = 'Пароль отправлен на почту';
            $type = 'success';
        } catch (\Throwable $e) {
            $message = $e instanceof HttpException ? $e->getMessage() : $_ENV['ERROR_MESSAGE'];
            $type = 'error';
        }

        return new RedirectResponse('/?msg=' . urlencode($message) . '&type=' . $type);
    }
}
