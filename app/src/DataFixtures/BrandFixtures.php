<?php

namespace App\DataFixtures;

use App\Entity\Brand;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class BrandFixtures extends Fixture implements FixtureGroupInterface
{
    public function load(ObjectManager $manager): void
    {
        $brands = [
            'Laneige',
            'Innisfree',
            'Etude',
            'Missha',
            'Sulwhasoo',
            'The Face Shop',
            'Nature Republic',
            'Holika Holika',
            'Skinfood',
            'Dr. Jart+',
            'COSRX',
            'Banila Co',
            'Tony Moly',
            'Mamonde',
            'IOPE',
            'Hera',
            'Klairs',
            'Belif',
            'Too Cool For School',
            'Pyunkang Yul',
        ];

        foreach ($brands as $key => $brandName) {
            $brand = new Brand();
            $brand->setName($brandName);
            $brand->setUrl('url_'.$key);

            $manager->persist($brand);
        }

        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['brand'];
    }
}
