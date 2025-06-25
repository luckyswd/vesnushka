<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CartController extends BaseController
{
    #[Route('/cart', name: 'app_cart')]
    public function cart(): Response
    {
        dd(1);
    }
}
