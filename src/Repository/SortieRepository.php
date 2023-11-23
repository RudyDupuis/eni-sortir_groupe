<?php

namespace App\Repository;

use App\Data\SearchData;
use App\Entity\Sortie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Security;


/**
 * @extends ServiceEntityRepository<Sortie>
 *
 * @method Sortie|null find($id, $lockMode = null, $lockVersion = null)
 * @method Sortie|null findOneBy(array $criteria, array $orderBy = null)
 * @method Sortie[]    findAll()
 * @method Sortie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SortieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, Security $security)
    {
        parent::__construct($registry, Sortie::class);
        $this->security = $security;
    }

    /**
     * Récupère les sorties en lien avec une recherche
     * @return Sortie[]
     */
    public function findSearch(?SearchData $searchData = null, $user = null): array
    {
        $query = $this
            ->createQueryBuilder('s')
            ->select('s')
            ->leftJoin('s.etat', 'etat')
            ->andWhere('etat.libelle != :etatHistorisee')
            ->setParameter('etatHistorisee', 'historisee');

        if ($searchData !== null) {

            // Filtre par nom (champs recherche)
            if (!empty($searchData->rechercher)) {
                $query = $query
                    ->andWhere('s.nom LIKE :rechercher')
                    ->setParameter('rechercher', "%{$searchData->rechercher}%");
            }

            // Filtrer par campus (champs campus)
            if (!empty($searchData->campus)) {
                $query = $query
                    ->join('s.organisateur', 'organisateur')
                    ->join('organisateur.campus', 'campus')
                    ->andWhere('campus.nom = :campus')
                    ->setParameter('campus', $searchData->campus);
            }

            // Filtrer les sorties en fonction de la plage de dates
            if ($searchData->dateDebut !== null) {
                $query = $query
                    ->andWhere('s.dateHeureDebut >= :dateDebut')
                    ->setParameter('dateDebut', $searchData->dateDebut);
            }

            if ($searchData->dateFin !== null) {
                $query = $query
                    ->andWhere('s.dateHeureDebut <= :dateFin')
                    ->setParameter('dateFin', $searchData->dateFin);
            }

            // Filtrer Sorties dont je suis l'organisateur/trice
            if (!empty($searchData->utilite1) && $user !== null) {
                $query = $query
                    ->andWhere('s.organisateur = :organisateur')
                    ->setParameter('organisateur', $user);
            }


            // Filtrer Sorties auxquelles je suis inscrit/e
            if (!empty($searchData->utilite2) && $user !== null) {
                $query = $query
                    ->join('s.participants', 'participant')
                    ->andWhere('participant = :user')
                    ->setParameter('user', $user);
            }

            // Filtrer les sorties auxquelles l'utilisateur ne participe pas
            if (!empty($searchData->utilite3) && $user !== null) {
                $subquery = $this->createQueryBuilder('sub')
                    ->select('sub.id')
                    ->join('sub.participants', 'subParticipant')
                    ->where('subParticipant.id = :userId');

                $query = $query
                    ->andWhere($query->expr()->notIn('s.id', $subquery->getDQL()))
                    ->setParameter('userId', $user->getId())
                    ->andWhere('etat.libelle = :etatOuverte')
                    ->setParameter('etatOuverte', 'ouverte');
            }

            // Filtrer les sorties en fonction de leur état
            if (!empty($searchData->utilite4)) {
                $query = $query
                    ->andWhere('etat.libelle = :etatPassee')
                    ->setParameter('etatPassee', 'passee');
            }
        }

        return $query->getQuery()->getResult();
    }
}
