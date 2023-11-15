<?php

namespace App\DataFixtures;

use App\Entity\Ville;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class VilleFixtures extends Fixture
{

    private $villes = [
        ['nom' => 'Paris', 'codePostal' => '75000'],
        ['nom' => 'Marseille', 'codePostal' => '13000'],
        ['nom' => 'Lyon', 'codePostal' => '69000'],
        ['nom' => 'Toulouse', 'codePostal' => '31000'],
        ['nom' => 'Nice', 'codePostal' => '06000'],
        ['nom' => 'Nantes', 'codePostal' => '44000'],
        ['nom' => 'Montpellier', 'codePostal' => '34000'],
        ['nom' => 'Strasbourg', 'codePostal' => '67000'],
        ['nom' => 'Bordeaux', 'codePostal' => '33000'],
        ['nom' => 'Lille', 'codePostal' => '59000'],
    ];

    public function load(ObjectManager $manager): void
    {
        foreach ($this->villes as $index => $villeData) {
            $ville = new Ville();
            $ville->setNom($villeData['nom']);
            $ville->setCodePostal($villeData['codePostal']);
            $manager->persist($ville);
            $this->addReference('ville' . $index, $ville);
        }

        $manager->flush();
    }
}
