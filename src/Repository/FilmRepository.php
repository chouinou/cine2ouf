<?php

namespace App\Repository;

use App\Entity\Film;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Film>
 */
class FilmRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Film::class);
    }

    /** Les films qui ont au moins une séance à venir, avec leur réalisateur */
    public function findALaffiche(): array
    {
        return $this->createQueryBuilder('f')
            ->join('f.realisateur', 'r')
            ->addSelect('r')
            ->join('f.seances', 's')
            ->where('s.date_heur >= :maintenant')
            ->setParameter('maintenant', new \DateTimeImmutable())
            ->distinct()
            ->orderBy('f.titre', 'ASC')
            ->getQuery()
            ->getResult();
    }
}