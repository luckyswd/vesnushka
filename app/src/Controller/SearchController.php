<?php

namespace App\Controller;

use App\Handler\FilterHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

class SearchController extends AbstractController
{
    #[Route('/search', name: 'app_search', methods: ['GET'])]
    public function brands(Request $request, FilterHandler $filterHandler): Response
    {
        $query = $request->query->get('search');

        if (!$query) {
            throw new NotFoundHttpException();
        }

        try {
            return $filterHandler->renderCatalog($query);
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
