<?php

namespace App\DataFixtures;

use App\Entity\Attribute;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class AttributeFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        for ($i = 0; $i < 15; ++$i) {
            $attribute = new Attribute();
            $attribute->setName($faker->word());
            $attribute->setCode('attribute_'.$i);
            $manager->persist($attribute);
            $this->addReference('attribute_'.$i, $attribute);
        }

        $manager->flush();
    }
}
