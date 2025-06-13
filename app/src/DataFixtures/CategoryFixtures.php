<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Enum\CategoryPublishStateEnum;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class CategoryFixtures extends Fixture implements FixtureGroupInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        $rootCategory = new Category();
        $rootCategory->setName('Каталог')
            ->setPublishState(CategoryPublishStateEnum::ACTIVE);
        $manager->persist($rootCategory);
        $manager->flush();

        $categories = [$rootCategory];

        for ($i = 0; $i < 30; ++$i) {
            $category = new Category();
            $category->setName($faker->word);
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
