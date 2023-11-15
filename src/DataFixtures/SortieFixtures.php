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
    private $lieuFixtures;
    private $etatFixtures;

    public function __construct(CampusFixtures $campusFixtures, ParticipantFixtures $participantFixtures, LieuFixtures $lieuFixtures, EtatFixtures $etatFixtures)
    {
        $this->campusFixtures = $campusFixtures;
        $this->participantFixtures = $participantFixtures;
        $this->lieuFixtures = $lieuFixtures;
        $this->etatFixtures = $etatFixtures;
    }

    public function load(ObjectManager $manager)
    {
        $faker = Factory::create('fr_FR');

        for ($i = 0; $i < 5; $i++) {
            $campusReferences[] = $this->campusFixtures->getReference('campus' . $i);
        }
        for ($i = 0; $i < 10; $i++) {
            $particiantReferences[] = $this->participantFixtures->getReference('participant' . $i);
        }
        for ($i = 0; $i < 10; $i++) {
            $lieuReferences[] = $this->lieuFixtures->getReference('lieu' . $i);
        }
        for ($i = 0; $i < 6; $i++) {
            $etatReferences[] = $this->etatFixtures->getReference('etat' . $i);
        }

        for ($i = 1; $i <= 10; $i++) {
            $sortie = new Sortie();
            $sortie->setNom($faker->text(40));
            $sortie->setDateLimiteInscription($faker->dateTimeBetween('now', '+1 month'));
            $sortie->setDateHeureDebut($faker->dateTimeBetween($sortie->getDateLimiteInscription(), '+2 month'));
            $sortie->setDuree($faker->numberBetween(10, 350));
            $sortie->setNbInscriptionsMax($faker->numberBetween(10, 30));

            if (rand(1, 10) > 7) {
                $sortie->setInfosSortie($faker->paragraph);
            }

            $sortie->setEtat($faker->randomElement($etatReferences));
            $sortie->setLieu($faker->randomElement($lieuReferences));
            $sortie->setSiteOrganisateur($faker->randomElement($campusReferences));
            $sortie->setOrganisateur($faker->randomElement($particiantReferences));

            for ($j = 0; $j < rand(1, 10); $j++) {
                $sortie->addParticipant($faker->randomElement($particiantReferences));
            }

            $manager->persist($sortie);
        }

        $manager->flush();
    }


    public function getDependencies()
    {
        return [
            LieuFixtures::class,
            ParticipantFixtures::class,
            CampusFixtures::class,
            EtatFixtures::class
        ];
    }
}
