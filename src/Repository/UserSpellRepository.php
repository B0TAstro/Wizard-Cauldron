<?php

namespace App\Repository;

use App\Entity\UserSpell;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserSpell>
 */
class UserSpellRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserSpell::class);
    }

    public function findSpellIdsUnlockedForUser(int $userId): array
    {
        $rows = $this->createQueryBuilder('us')
            ->select('IDENTITY(us.spell) AS id')
            ->where('us.user = :uid')
            ->setParameter('uid', $userId)
            ->getQuery()->getScalarResult();

        return array_map(fn($r) => (int)$r['id'], $rows);
    }

    public function countUnlockedForUser(int $userId): int
    {
        return (int)$this->createQueryBuilder('us')
            ->select('COUNT(us.id)')
            ->andWhere('us.user = :u')->setParameter('u', $userId)
            ->getQuery()->getSingleScalarResult();
    }
}
