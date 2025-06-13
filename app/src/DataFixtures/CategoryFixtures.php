<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Enum\CategoryPublishStateEnum;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class CategoryFixtures extends Fixture implements FixtureGroupInterface
{
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

        $rootCategory = new Category();
        $rootCategory->setName('Каталог')
            ->setPublishState(CategoryPublishStateEnum::ACTIVE);

        $manager->persist($rootCategory);
        $manager->flush();

        $categories = [$rootCategory];

        foreach ($categoriesNames as $index => $name) {
            $category = new Category();
            $category->setName($name);
            $category->setPublishState(CategoryPublishStateEnum::ACTIVE);

            $parentCategory = $categories[array_rand($categories)];
            $category->setParent($parentCategory);

            $manager->persist($category);
            $categories[] = $category;
        }

        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['category'];
    }
}
