<?php

namespace App\Repository;

use App\Entity\Salle;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Salle>
 */
class SalleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Salle::class);
    }

    /** Le nombre total de places du cinéma */
    public function getCapaciteTotale(): int
    {
        return (int) $this->createQueryBuilder('s')
            ->select('SUM(s.capacite)')
            ->getQuery()
            ->getSingleScalarResult();
    }
}