<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Spell;
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

    public function countUnlockedForUser(int $userId): int
    {
        return (int) $this->createQueryBuilder('us')
            ->select('COUNT(us.id)')
            ->innerJoin('us.spell', 's')
            ->where('us.user = :u')
            ->andWhere('s.isActive = true')
            ->setParameter('u', $userId)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findUnlockedSpellIdsForUser(int $userId): array
    {
        $rows = $this->createQueryBuilder('us')
            ->select('IDENTITY(us.spell) AS id')
            ->where('us.user = :u')
            ->setParameter('u', $userId)
            ->getQuery()
            ->getScalarResult();

        return array_map(fn(array $r) => (int) $r['id'], $rows);
    }

    public function hasUserSpell(User $user, Spell $spell): bool
    {
        return (int)$this->createQueryBuilder('us')
            ->select('COUNT(us.id)')
            ->where('us.user = :u')->andWhere('us.spell = :s')
            ->setParameter('u', $user)->setParameter('s', $spell)
            ->getQuery()->getSingleScalarResult() > 0;
    }

    public function findSpellIdsForUser(int $userId): array
    {
        $rows = $this->createQueryBuilder('us')
            ->select('IDENTITY(us.spell) AS sid')
            ->where('us.user = :u')
            ->setParameter('u', $userId)
            ->getQuery()->getScalarResult();

        return array_map(fn($r) => (int)$r['sid'], $rows);
    }

    public function findOwnedSpellIds(\App\Entity\User $user): array
    {
        $rows = $this->createQueryBuilder('us')
            ->select('IDENTITY(us.spell) AS id')
            ->andWhere('us.user = :u')->setParameter('u', $user)
            ->getQuery()->getScalarResult();

        return array_map(fn($r) => (int)$r['id'], $rows);
    }

    public function existsForUser(\App\Entity\User $user, \App\Entity\Spell $spell): bool
    {
        $cnt = (int)$this->createQueryBuilder('us')
            ->select('COUNT(us.id)')
            ->andWhere('us.user = :u')->setParameter('u', $user)
            ->andWhere('us.spell = :s')->setParameter('s', $spell)
            ->getQuery()->getSingleScalarResult();

        return $cnt > 0;
    }
}
