<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use App\Entity\Lieu;
use App\Entity\Participant;
use App\Entity\Sortie;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class SortieFixtures extends Fixture implements DependentFixtureInterface
{
    private $campusFixtures;

    public function __construct(CampusFixtures $campusFixtures)
    {
        $this->campusFixtures = $campusFixtures;
    }

    public function load(ObjectManager $manager)
    {
        $faker = Factory::create('fr_FR');

        for ($i = 0; $i < 5; $i++) {
            $campusReferences[] = $this->campusFixtures->getReference('campus' . $i);
        }

        for ($i = 1; $i <= 10; $i++) {
            $sortie = new Sortie();
            $sortie->setNom($faker->sentence);
            $sortie->setDateLimiteInscription($faker->dateTimeBetween('now', '+1 month'));
            $sortie->setDateHeureDebut($faker->dateTimeBetween($sortie->getDateLimiteInscription(), '+2 month'));
            $sortie->setDuree(new \DateTime("2023-01-01 02:00:00")); // trouvez avec gpt une datetime avec faker !!!!!!!
            $sortie->setNbInscriptionsMax($faker->numberBetween(10, 50));
            $sortie->setInfosSortie($faker->paragraph);
            $sortie->setEtat($faker->randomElement(['Créée', 'Ouverte', 'Clôturée', 'Activité en cours', 'Passée', 'Annulée']));
            $sortie->setLieu($faker->randomElement($lieux)); // attendre les données relationnelles
            $sortie->setSiteOrganisateur($faker->randomElement($campusReferences));
            $sortie->setOrganisateur($manager->getRepository(Participant::class)->find(1));

            $manager->persist($sortie);
        }

        $manager->flush();
    }


    public function getDependencies()
    {
        return [LieuFixtures::class, ParticipantFixtures::class, CampusFixtures::class];
    }
}