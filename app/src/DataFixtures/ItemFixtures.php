<?php

namespace App\DataFixtures;

use App\Entity\Attribute;
use App\Entity\Brand;
use App\Entity\Category;
use App\Entity\Item;
use App\Entity\ItemAttribute;
use App\Enum\ItemPublishStateEnum;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class ItemFixtures extends Fixture implements FixtureGroupInterface
{
    private const BATCH_SIZE = 100; // Reduced batch size

    public function load(ObjectManager $manager): void
    {
        $brands = $manager->getRepository(Brand::class)->findAll();
        $categories = $manager->getRepository(Category::class)->findAll();
        $attributes = $manager->getRepository(Attribute::class)->findAll();

        $countBrands = count($brands);
        $countCategories = count($categories);
        $countAttributes = count($attributes);

        for ($i = 0; $i < 1000; ++$i) {
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

            // Assign multiple attributes (2 to 5 randomly)
            $numAttributes = rand(2, 5);
            $addedAttributes = [];
            for ($k = 0; $k < $numAttributes; ++$k) {
                do {
                    $attributeIndex = rand(0, $countAttributes - 1);
                } while (in_array($attributeIndex, $addedAttributes));

                $itemAttribute = new ItemAttribute();
                $itemAttribute->setItem($item)
                    ->setAttribute($attributes[$attributeIndex])
                    ->setValue('test_attr_'.$i.rand(0, 9999999).'_'.$k);

                $manager->persist($itemAttribute);
                $addedAttributes[] = $attributeIndex;
            }

            $manager->persist($item);

            if (0 === $i % self::BATCH_SIZE && $i > 0) {
                $manager->flush();
                $manager->clear();

                // Clear the arrays holding Brands, Categories and Attributes
                unset($brands, $categories, $attributes);

                // Re-fetch repositories and counts
                $brands = $manager->getRepository(Brand::class)->findAll();
                $categories = $manager->getRepository(Category::class)->findAll();
                $attributes = $manager->getRepository(Attribute::class)->findAll();

                $countBrands = count($brands);
                $countCategories = count($categories);
                $countAttributes = count($attributes);

                echo "Inserted $i items\n";
            }
        }

        $manager->flush();
        $manager->clear(); // Clear at the end too
    }

    public static function getGroups(): array
    {
        return ['items'];
    }
}
