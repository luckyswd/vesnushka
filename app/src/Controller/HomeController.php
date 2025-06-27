<?php

namespace App\Controller;

use App\Handler\HomeHandler;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends BaseController
{
    #[Route('/', name: 'app_home')]
    public function index(HomeHandler $homeHandler): Response
    {
        try {
            return $homeHandler->handle();
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
