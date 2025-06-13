<?php

namespace App\DataFixtures;

use App\Entity\Brand;
use App\Entity\Category;
use App\Entity\Item;
use App\Enum\ItemPublishStateEnum;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class ItemFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        $brands = $manager->getRepository(Brand::class)->findAll();
        $categories = $manager->getRepository(Category::class)->findAll();

        for ($i = 0; $i < 70; ++$i) {
            $item = new Item();
            $item->setName($faker->name);
            $item->setSku($faker->unique()->name);
            $item->setPublishState(ItemPublishStateEnum::ACTIVE);

            $item->setBrand($brands[array_rand($brands)]);
            $item->addCategory($categories[array_rand($categories)]);

            $manager->persist($item);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            BrandFixtures::class,
            CategoryFixtures::class,
        ];
    }
}
