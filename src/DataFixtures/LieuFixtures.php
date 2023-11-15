<?php

namespace App\DataFixtures;

use App\Entity\Lieu;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class LieuFixtures extends Fixture implements DependentFixtureInterface

{
    private $villeFixtures;

    public function __construct(VilleFixtures $villeFixtures)
    {
        $this->villeFixtures = $villeFixtures;
    }

    public function load(ObjectManager $manager)
    {
        $faker = Factory::create('fr_FR');

        for ($i = 0; $i < 10; $i++) {
            $villeReferences[] = $this->villeFixtures->getReference('ville' . $i);
        }

        for ($i = 0; $i < 10; $i++) {
            $lieu = new Lieu();
            $lieu->setNom($faker->company);
            $lieu->setRue($faker->streetAddress);
            $lieu->setLatitude($faker->latitude);
            $lieu->setLongitude($faker->longitude);
            $lieu->setVille($faker->randomElement($villeReferences));

            $manager->persist($lieu);
            $this->addReference(('lieu' . $i), $lieu);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [VilleFixtures::class];
    }
}
