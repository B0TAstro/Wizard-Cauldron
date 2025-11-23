<?php

namespace App\Repository;

use App\Entity\TradeTicket;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class TradeTicketRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TradeTicket::class);
    }

    public function findOneWaitingExcept(User $exclude): ?TradeTicket
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.status = :st')
            ->andWhere('t.user != :me')
            ->setParameter('st', 'waiting')
            ->setParameter('me', $exclude)
            ->orderBy('t.createdAt', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
