<?php

namespace App\Service;

use App\Entity\Category;

class CatalogService
{
    public static function getCatalogUrl(string $path): string
    {
        return Category::URL_CATALOG . '/' . $path;
    }
}
