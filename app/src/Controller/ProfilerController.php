<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProfilerController
{
    #[Route('/profile', name: 'app_profile')]
    public function profiler(): Response {
        dd(1);
    }
}