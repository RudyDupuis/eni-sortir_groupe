<?php

namespace App\DataFixtures;

use App\Entity\Sortie;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class SortieFixtures extends Fixture implements DependentFixtureInterface
{
    private $campusFixtures;
    private $participantFixtures;

    public function __construct(CampusFixtures $campusFixtures, ParticipantFixtures $participantFixtures)
    {
        $this->campusFixtures = $campusFixtures;
        $this->participantFixtures = $participantFixtures;
    }

    public function load(ObjectManager $manager)
    {
        $faker = Factory::create('fr_FR');

        for ($i = 0; $i < 5; $i++) {
            $campusReferences[] = $this->campusFixtures->getReference('campus' . $i);
        }
        for ($i = 0; $i < 5; $i++) {
            $particiantReferences[] = $this->participantFixtures->getReference('participant' . $i);
        }

        for ($i = 1; $i <= 10; $i++) {
            $sortie = new Sortie();
            $sortie->setNom($faker->sentence);
            $sortie->setDateLimiteInscription($faker->dateTimeBetween('now', '+1 month'));
            $sortie->setDateHeureDebut($faker->dateTimeBetween($sortie->getDateLimiteInscription(), '+2 month'));
            $sortie->setDuree($faker->dateTimeBetween('-10 minutes', '+5 hours'));
            $sortie->setNbInscriptionsMax($faker->numberBetween(5, 50));
            $sortie->setInfosSortie($faker->paragraph);
            $sortie->setEtat($faker->randomElement(['Créée', 'Ouverte', 'Clôturée', 'Activité en cours', 'Passée', 'Annulée']));
            //$sortie->setLieu($faker->randomElement());
            $sortie->setSiteOrganisateur($faker->randomElement($campusReferences));
            $sortie->setOrganisateur($faker->randomElement($particiantReferences));

            $manager->persist($sortie);
        }

        $manager->flush();
    }


    public function getDependencies()
    {
        return [
            LieuFixtures::class,
            ParticipantFixtures::class,
            CampusFixtures::class
        ];
    }
}
