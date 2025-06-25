<?php

namespace App\Controller;

use App\Traits\ApiResponseTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class BaseController extends AbstractController
{
    use ApiResponseTrait;
}
