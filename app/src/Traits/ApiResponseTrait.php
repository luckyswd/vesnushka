<?php

namespace App\Traits;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AttributeLoader;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

trait ApiResponseTrait
{
    protected array $meta = [];

    private NormalizerInterface $serializer;

    public function __construct()
    {
        $this->serializer = new Serializer([
            new DateTimeNormalizer(['datetime_format' => 'Y-m-d']),
            new ObjectNormalizer(
                new ClassMetadataFactory(new AttributeLoader())
            ),
        ]);
    }

    protected function success(
        array|object $data = [],
        int $statusCode = Response::HTTP_OK,
        ?array $groups = null,
    ): JsonResponse {
        if ($groups) {
            $context = ['groups' => $groups];
            $data = $this->serializer->normalize($data, null, $context);
        }

        return new JsonResponse(
            [
                'data' => empty($data) ? ['success'] : $data,
                'meta' => $this->meta,
            ],
            $statusCode,
            [],
            false
        );
    }

    protected function error(
        string $message,
        int $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR,
    ): JsonResponse {
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
