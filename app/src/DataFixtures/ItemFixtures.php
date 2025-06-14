<?php

namespace App\DataFixtures;

use App\Entity\Attribute;
use App\Entity\Brand;
use App\Entity\Category;
use App\Entity\Item;
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

            // Add attributes
            $itemAttributes = [];
            $numAttributes = rand(3, 7); // Случайное количество атрибутов от 3 до 7
            $addedAttributes = [];
            for ($j = 0; $j < $numAttributes; ++$j) {
                do {
                    $attributeIndex = rand(0, $countAttributes - 1);
                } while (in_array($attributeIndex, $addedAttributes));

                $attribute = $attributes[$attributeIndex];
                $itemAttributes[$attribute->getCode()] = $this->generateRandomAttributeValue($attribute->getCode());
                $addedAttributes[] = $attributeIndex;
            }
            $item->setAttributes($itemAttributes);

            $prices = [];
            foreach (PriceTypeEnum::cases() as $priceType) {
                $prices[$priceType->value] = [];
                foreach (CurrencyEnum::cases() as $currency) {
                    $prices[$priceType->value][$currency->value] = rand(0, 1000);
                }
            }
            $item->setPrice($prices);

            $manager->persist($item);
        }

        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['items'];
    }

    private function generateRandomAttributeValue(string $attributeCode): string
    {
        switch ($attributeCode) {
            case 'skin_type':
                return ['Нормальная', 'Сухая', 'Жирная', 'Комбинированная'][array_rand(['Нормальная', 'Сухая', 'Жирная', 'Комбинированная'])];
            case 'purpose':
                return ['Увлажнение', 'Питание', 'Очищение', 'Защита'][array_rand(['Увлажнение', 'Питание', 'Очищение', 'Защита'])];
            case 'product_form':
                return ['Крем', 'Гель', 'Лосьон', 'Маска'][array_rand(['Крем', 'Гель', 'Лосьон', 'Маска'])];
            case 'volume':
                return rand(30, 500).' мл';
            case 'country_of_origin':
                return ['Франция', 'Корея', 'Япония', 'США', 'Германия'][array_rand(['Франция', 'Корея', 'Япония', 'США', 'Германия'])];
            case 'active_ingredients':
                return ['Гиалуроновая кислота', 'Витамин C', 'Ретинол', 'Коллаген'][array_rand(['Гиалуроновая кислота', 'Витамин C', 'Ретинол', 'Коллаген'])];
            case 'spf':
                return 'SPF '.rand(15, 50);
            case 'age_range':
                return rand(18, 60).'+';
            case 'effect':
                return ['Антивозрастной', 'Матирующий', 'Осветляющий', 'Успокаивающий'][array_rand(['Антивозрастной', 'Матирующий', 'Осветляющий', 'Успокаивающий'])];
            case 'color':
                return ['Бесцветный', 'Розовый', 'Бежевый', 'Зеленый'][array_rand(['Бесцветный', 'Розовый', 'Бежевый', 'Зеленый'])];
            case 'product_type':
                return ['Крем для лица', 'Сыворотка', 'Тоник', 'Пенка для умывания'][array_rand(['Крем для лица', 'Сыворотка', 'Тоник', 'Пенка для умывания'])];
            case 'packaging_type':
                return ['Тюбик', 'Флакон', 'Банка', 'Спрей'][array_rand(['Тюбик', 'Флакон', 'Банка', 'Спрей'])];
            default:
                return 'Значение';
        }
    }
}
