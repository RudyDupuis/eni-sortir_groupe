<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Sortie;
use App\Entity\Etat;

class EtatUpdateService
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function mettreAJourEtatsSorties()
    {
        $sorties = $this->entityManager->getRepository(Sortie::class)->findAll();
        $etats = $this->entityManager->getRepository(Etat::class)->findAll();

        foreach ($sorties as $sortie) {
            $etatPrecedent = $sortie->getEtat()->getLibelle();
            $timezone = new \DateTimeZone('Europe/Paris');
            $now = new \DateTime('now', $timezone);

            // Changement d'état en "Clôturée"
            if (
                $etatPrecedent !== 'Créée' &&
                $etatPrecedent !== 'Annulée' &&
                $sortie->getDateLimiteInscription() < $now
            ) {
                $this->changerEtat($sortie, $this->trouverEtatParLibelle($etats, 'Clôturée'));
            }

            // Changement d'état en "Activité en cours"
            if (
                $etatPrecedent !== 'Créée' &&
                $etatPrecedent !== 'Annulée' &&
                $sortie->getDateHeureDebut()->format('Y-m-d H:i:s') < $now->format('Y-m-d H:i:s')
            ) {
                $this->changerEtat($sortie, $this->trouverEtatParLibelle($etats, 'Activité en cours'));
            }

            // Changement d'état en "Passée"
            $dateFin = clone $sortie->getDateHeureDebut();
            $dateFin->modify("+{$sortie->getDuree()} minutes");
            if (
                $etatPrecedent !== 'Créée' &&
                $etatPrecedent !== 'Annulée' &&
                $dateFin->format('Y-m-d H:i:s') < $now->format('Y-m-d H:i:s')
            ) {
                $this->changerEtat($sortie, $this->trouverEtatParLibelle($etats, 'Passée'));
            }

            // Changement d'état en "Historisée"
            $dateUnMoisApres = clone $sortie->getDateHeureDebut();
            $dateUnMoisApres->modify('+1 month');
            if ($dateUnMoisApres < $now) {
                $this->changerEtat($sortie, $this->trouverEtatParLibelle($etats, 'Historisée'));
            }
        }

        $this->entityManager->flush();
    }

    private function changerEtat(Sortie $sortie, Etat $nouvelEtat)
    {
        $sortie->setEtat($nouvelEtat);
        $this->entityManager->persist($sortie);
    }

    private function trouverEtatParLibelle(array $etats, string $libelle): ?Etat
    {
        foreach ($etats as $etat) {
            if ($etat->getLibelle() === $libelle) {
                return $etat;
            }
        }
        return null;
    }
}
