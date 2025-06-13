<?php

namespace App\DataFixtures;

use App\Entity\Attribute;
use App\Entity\Brand;
use App\Entity\Category;
use App\Entity\Item;
use App\Entity\ItemAttribute;
use App\Entity\ItemPrice;
use App\Enum\CurrencyEnum;
use App\Enum\ItemPublishStateEnum;
use App\Enum\PriceTypeEnum;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class ItemFixtures extends Fixture implements FixtureGroupInterface
{
    public function load(ObjectManager $manager): void
    {
        $brands = $manager->getRepository(Brand::class)->findAll();
        $categories = $manager->getRepository(Category::class)->findAll();
        $attributes = $manager->getRepository(Attribute::class)->findAll();

        $countBrands = count($brands);
        $countCategories = count($categories);
        $countAttributes = count($attributes);

        for ($i = 0; $i < 500; ++$i) {
            $item = new Item();
            $item->setName('test_'.$i.rand(0, 9999999));
            $item->setSku('test_'.$i.rand(0, 9999999));
            $item->setPublishState(ItemPublishStateEnum::ACTIVE);

            // Assign a brand
            $item->setBrand($brands[rand(0, $countBrands - 1)]);

            // Assign multiple categories (1 to 3 randomly)
            $numCategories = rand(1, 3);
            $addedCategories = [];
            for ($j = 0; $j < $numCategories; ++$j) {
                do {
                    $categoryIndex = rand(0, $countCategories - 1);
                } while (in_array($categoryIndex, $addedCategories));

                $item->addCategory($categories[$categoryIndex]);
                $addedCategories[] = $categoryIndex;
            }

            foreach (PriceTypeEnum::cases() as $priceType) {
                $itemPrice = new ItemPrice();
                $itemPrice->setItem($item)
                    ->setPrice(rand(0, 100000))
                    ->setCurrency(CurrencyEnum::BYN)
                    ->setPriceType($priceType);

                $manager->persist($itemPrice);
            }

            $manager->persist($item);
        }

        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['items'];
    }
}
