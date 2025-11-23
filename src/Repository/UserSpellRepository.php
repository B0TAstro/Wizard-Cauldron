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

    public function findUnlockedSpellIdsForUser(int $userId): array
    {
        $rows = $this->createQueryBuilder('us')
            ->select('DISTINCT IDENTITY(us.spell) AS sid')
            ->where('us.user = :u')->setParameter('u', $userId)
            ->getQuery()->getScalarResult();

        return array_map(fn($r) => (int)$r['sid'], $rows);
    }

    public function hasUserSpell(User $user, Spell $spell): bool
    {
        return (int)$this->createQueryBuilder('us')
            ->select('COUNT(us.id)')
            ->where('us.user = :u')->andWhere('us.spell = :s')
            ->setParameter('u', $user)->setParameter('s', $spell)
            ->getQuery()->getSingleScalarResult() > 0;
    }
}
