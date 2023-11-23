<?php

namespace App\Repository;

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
    public function findFiltered($searchTerm = null): array
    {
        $queryBuilder = $this->createQueryBuilder('s');

        if ($searchTerm) {
            $queryBuilder
                ->andWhere('s.nom LIKE :searchTerm')
                ->setParameter('searchTerm', "%" . $searchTerm . "%");
        }

        return $queryBuilder->getQuery()->getResult();
    }

    public function rechercheParNom(string $nom): ?Campus
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.nom = :nom')
            ->setParameter('nom', $nom)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
