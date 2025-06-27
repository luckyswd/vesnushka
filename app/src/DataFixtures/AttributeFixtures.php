<?php

namespace App\DataFixtures;

use App\Entity\Attribute;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class AttributeFixtures extends Fixture  implements FixtureGroupInterface
{
    public function load(ObjectManager $manager): void
    {
        $attributes = [
            'Тип кожи' => 'skin_type',
            'Назначение' => 'purpose',
            'Форма выпуска' => 'product_form',
            'Объем' => 'volume',
            'Страна производства' => 'country_of_origin',
            'Активные компоненты' => 'active_ingredients',
            'SPF защита' => 'spf',
            'Подходит для возраста' => 'age_range',
            'Эффект' => 'effect',
            'Цвет' => 'color',
            'Тип продукта' => 'product_type',
            'Тип упаковки' => 'packaging_type',
        ];

        $i = 0;

        foreach ($attributes as $name => $code) {
            $attribute = new Attribute();
            $attribute->setName($name);
            $attribute->setCode($code);

            $manager->persist($attribute);

            $this->addReference('attribute_' . $i, $attribute);
            ++$i;
        }

        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['attr'];
    }
}
