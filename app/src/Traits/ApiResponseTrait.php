<?php

namespace App\Traits;

use App\Serializer\Serializer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

trait ApiResponseTrait
{
    protected array $meta = [];

    protected function success(
        array|object $data = [],
        int $statusCode = Response::HTTP_OK,
        ?array $groups = null,
    ): JsonResponse {
        if ($groups) {
            $data = Serializer::normalize($data, null, ['groups' => $groups]);
        }

        return new JsonResponse(
            [
                'data' => empty($data) ? ['success' => true] : $data,
                'meta' => $this->meta,
            ],
            $statusCode,
            [],
            false
        );
    }

    protected function error(
        string|array $message,
        int $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR,
    ): JsonResponse {
        if (is_array($message)) {
            $lines = [];

            foreach ($message as $field => $errors) {
                if (is_array($errors)) {
                    foreach ($errors as $err) {
                        $lines[] = "{$field}: {$err}";
                    }
                } else {
                    $lines[] = "{$field}: {$errors}";
                }
            }

            $message = implode("\n", $lines);
        }

        return new JsonResponse(
            ['error' => $message],
            $statusCode,
            [],
            false
        );
    }

    protected function addMeta(
        string $key,
        mixed $value,
    ): void {
        $this->meta[$key] = $value;
    }
}
