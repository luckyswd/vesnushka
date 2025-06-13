<?php

namespace App\DataFixtures;

use App\Entity\Brand;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class BrandFixtures extends Fixture implements FixtureGroupInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        for ($i = 0; $i < 20; ++$i) {
            $brand = new Brand();
            $brand->setName($faker->company);
            $brand->setUrl($faker->slug);

            $manager->persist($brand);
        }

        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['brand'];
    }
}
