<?php

namespace App\Repository;

use App\Data\SearchData;
use App\Data\SearchDataRechercher;
use App\Entity\Campus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Campus>
 *
 * @method Campus|null find($id, $lockMode = null, $lockVersion = null)
 * @method Campus|null findOneBy(array $criteria, array $orderBy = null)
 * @method Campus[]    findAll()
 * @method Campus[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CampusRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Campus::class);
    }

    /**
     * Récupère les campus en lien avec une recherche
     * @return Campus[]
     */
    public function findSearch(?SearchDataRechercher $searchDataRechercher = null): array
    {
        $query = $this
            ->createQueryBuilder('s')
            ->select('s');

        if ($searchDataRechercher !== null) {

            // Filtre par nom (champs recherche)
            if (!empty($searchDataRechercher->rechercher)) {
                $query = $query
                    ->andWhere('s.nom LIKE :rechercher')
                    ->setParameter('rechercher', "%{$searchDataRechercher->rechercher}%");
            }
        }

        return $query->getQuery()->getResult();
    }
}