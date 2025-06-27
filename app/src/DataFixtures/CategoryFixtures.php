<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\File;
use App\Enum\CategoryPublishStateEnum;
use App\Repository\CategoryRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class CategoryFixtures extends Fixture implements FixtureGroupInterface
{
    private ParameterBagInterface $parameterBag;

    public function __construct(
        ParameterBagInterface $parameterBag,
        private CategoryRepository $categoryRepository,
    )
    {
        $this->parameterBag = $parameterBag;
    }

    public function load(ObjectManager $manager): void
    {
        $categoriesNames = [
            'Уход за лицом',
            'Очищение',
            'Тонизирование',
            'Увлажнение',
            'Сыворотки и эссенции',
            'Кремы',
            'Маски для лица',
            'Патчи для глаз',
            'Солнцезащитные средства',
            'Макияж',
            'База под макияж',
            'Тональные средства',
            'Помады и блески',
            'Средства для волос',
            'Аксессуары',
        ];

        $rootCategory = $this->categoryRepository->findOneBy(['name' => 'Каталог']);

        if (!$rootCategory) {
            $rootCategory = new Category();
            $rootCategory->setName('Каталог')
                ->setPublishState(CategoryPublishStateEnum::ACTIVE);

            $manager->persist($rootCategory);
            $manager->flush();
        }

        $categories = [$rootCategory];

        foreach ($categoriesNames as $name) {
            $category = new Category();
            $category->setName($name . ' ' . random_int(0, 99999999));
            $category->setPublishState(CategoryPublishStateEnum::ACTIVE);

            $parentCategory = $categories[array_rand($categories)];
            $category->setParent($parentCategory);
            $category->setImage($this->createMockFile($manager));
            $category->setIsPopular((bool) random_int(0, 1));

            $manager->persist($category);
            $categories[] = $category;
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
        return ['category'];
    }
}
