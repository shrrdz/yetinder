<?php

namespace App\Repository;

use App\Entity\Yeti;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class YetiRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Yeti::class);
    }

    public function findTopRatedYetis(): array
    {
        return $this->createQueryBuilder('yeti')
            ->orderBy('yeti.rating', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();
    }
}
