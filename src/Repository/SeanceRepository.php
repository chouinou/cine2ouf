<?php

namespace App\Repository;

use App\Entity\Film;
use App\Entity\Seance;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Seance>
 */
class SeanceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Seance::class);
    }

    /** Les séances à venir d'un film, avec leur salle, de la plus proche à la plus lointaine */
    public function findProchainesPourFilm(Film $film): array
    {
        return $this->createQueryBuilder('s')
            ->join('s.salle', 'sa')
            ->addSelect('sa')
            ->where('s.film = :film')
            ->andWhere('s.date_heur >= :maintenant')
            ->setParameter('film', $film)
            ->setParameter('maintenant', new \DateTimeImmutable())
            ->orderBy('s.date_heur', 'ASC')
            ->getQuery()
            ->getResult();
    }
}