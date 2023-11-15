<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Participant;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ParticipantFixtures extends Fixture implements DependentFixtureInterface

{
    private $campusFixtures;
    private $passwordHasher;

    public function __construct(CampusFixtures $campusFixtures, UserPasswordHasherInterface $passwordHasher)
    {
        $this->campusFixtures = $campusFixtures;
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager)
    {
        $faker = Factory::create('fr_FR');

        for ($i = 0; $i < 5; $i++) {
            $campusReferences[] = $this->campusFixtures->getReference('campus' . $i);
        }


        for ($i = 0; $i < 10; $i++) {
            $participant = new Participant();
            $participant->setMail($faker->email);

            // Hachage du mot de passe
            $hashedPassword = $this->passwordHasher->hashPassword($participant, '12345678');
            $participant->setMotPasse($hashedPassword);

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
