<?php

namespace App\DataFixtures;

use App\Entity\Etat;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class EtatFixtures extends Fixture
{
    private $etatData = ['Créée', 'Ouverte', 'Clôturée', 'Activité en cours', 'Passée', 'Annulée'];

    public function load(ObjectManager $manager): void
    {
        $i = 0;

        foreach ($this->etatData as $etatName) {
            $etat = new Etat();
            $etat->setLibelle($etatName);
            $manager->persist($etat);
            $this->addReference(('etat' . $i), $etat);
            $i += 1;
        }

        $manager->flush();
    }
}
