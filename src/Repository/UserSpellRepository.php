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

    public function findUnlockedSpellIdsForUser(int $userId): array
    {
        $qb = $this->createQueryBuilder('us')
            ->select('IDENTITY(us.spell) AS spell_id')
            ->andWhere('us.user = :u')->setParameter('u', $userId);

        $rows = $qb->getQuery()->getArrayResult();
        return array_map(static fn(array $r) => (int)$r['spell_id'], $rows);
    }

    public function countUnlockedForUser(int $userId): int
    {
        return (int)$this->createQueryBuilder('us')
            ->select('COUNT(us.id)')
            ->andWhere('us.user = :u')->setParameter('u', $userId)
            ->getQuery()->getSingleScalarResult();
    }
}
