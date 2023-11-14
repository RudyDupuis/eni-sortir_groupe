<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Participant;
use Faker\Factory;
use phpDocumentor\Reflection\Types\This;


class ParticipantFixtures extends Fixture implements DependentFixtureInterface

{
    private $campusFixtures;

    public function __construct(CampusFixtures $campusFixtures)
    {
        $this->campusFixtures = $campusFixtures;
    }
    public function load(ObjectManager $manager)
    {
        $faker = Factory::create('fr_FR');
        for ($i = 0; $i < 5; $i++){
            $campusReferences[] = $this->campusFixtures->getReference('campus' . $i);

        }


        for ($i = 0; $i < 10; $i++) {
            $participant = new Participant();
            $participant->setMail($faker->email);
            $participant->setMotPasse('12345678');
            $participant->setPseudo($faker->userName);
            $participant->setNom($faker->lastName);
            $participant->setPrenom($faker->firstName);
            $participant->setTelephone($faker->serviceNumber);
            $participant->setAdministrateur($i == 0 ? true : false);
            $participant->setActif(true);
            $participant->setCampus($faker->randomElement($campusReferences));


            $manager->persist($participant);
            $this->addReference(('participant' . $i), $participant);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [CampusFixtures::class];
    }
}
