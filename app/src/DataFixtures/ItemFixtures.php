<?php

namespace App\DataFixtures;

use App\Entity\Attribute;
use App\Entity\Brand;
use App\Entity\Category;
use App\Entity\Item;
use App\Entity\ItemAttribute;
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
        $attributes = $manager->getRepository(Attribute::class)->findAll();

        for ($i = 0; $i < 70; ++$i) {
            $item = new Item();
            $item->setName($faker->sentence(3));
            $item->setSku($faker->unique()->ean13());
            $item->setUrl($faker->slug());
            $item->setPublishState($faker->randomElement(ItemPublishStateEnum::cases()));
            $item->setBreadcrumbs([$faker->word() => $faker->url(), $faker->word() => $faker->url()]);

            // Связываем с случайным брендом
            $brand = $faker->randomElement($brands);
            $item->setBrand($brand);

            // Связываем со случайными категориями
            $numCategories = $faker->numberBetween(1, 3);
            $randomCategories = $faker->randomElements($categories, $numCategories);

            foreach ($randomCategories as $category) {
                $item->addCategory($category);
            }

            $itemAttribute = new ItemAttribute();
            $itemAttribute->setItem($item)
                ->setAttribute($faker->randomElement($attributes))
                ->setValue($faker->sentence(2));

            $manager->persist($itemAttribute);
            $manager->persist($item);
            $this->addReference('item_'.$i, $item);
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
