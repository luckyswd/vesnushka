<?php

namespace App\DataFixtures;

use App\Entity\Brand;
use App\Entity\File;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class BrandFixtures extends Fixture implements FixtureGroupInterface
{
    private ParameterBagInterface $parameterBag;

    public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->parameterBag = $parameterBag;
    }

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
            $brand->setUrl('url_' . $key . random_int(1,9999999));
            $brand->setImage($this->createMockFile($manager));
            $brand->setIsPopular((bool) random_int(0, 1));

            $manager->persist($brand);
        }

        $manager->flush();
    }

    private function createMockFile(ObjectManager $manager): File
    {
        $projectDir = $this->parameterBag->get('kernel.project_dir');
        $mockImagePath = '/uploads/item/moock.png';
        $fullPath = $projectDir . '/public' . $mockImagePath;

        $file = new File();
        $file->setFilename('moock.png');
        $file->setOriginalFilename('moock.png');
        $file->setMimeType('image/png');
        $file->setSize(filesize($fullPath));
        $file->setPath($mockImagePath);

        $manager->persist($file);

        return $file;
    }

    public static function getGroups(): array
    {
        return ['brand'];
    }
}
