<?php

namespace App\Repository;

use App\Entity\Spell;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Spell>
 */
class SpellRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Spell::class);
    }

    //    /**
    //     * @return Spell[] Returns an array of Spell objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('s.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Spell
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
    public function countActive(): int
    {
        return (int)$this->createQueryBuilder('s')
            ->select('COUNT(s.id)')
            ->andWhere('s.isActive = :a')->setParameter('a', true)
            ->getQuery()->getSingleScalarResult();
    }

    /** @return Spell[] */
    public function findAllSorted(string $field, string $dir): array
    {
        $dir = strtolower($dir) === 'desc' ? 'DESC' : 'ASC';
        $allowed = ['name','slug','description','rarity','active'];

        if (!in_array($field, $allowed, true)) {
            $field = 'name';
        }

        $qb = $this->createQueryBuilder('s');

        switch ($field) {
            case 'rarity':
                $orderCase = "CASE 
                    WHEN s.rarity = 'common' THEN 1
                    WHEN s.rarity = 'rare' THEN 2
                    WHEN s.rarity = 'epic' THEN 3
                    WHEN s.rarity = 'legendary' THEN 4
                    ELSE 5 END";
                $qb->addSelect($orderCase.' AS HIDDEN rarityOrder')
                   ->addOrderBy('rarityOrder', $dir);
                break;

            case 'active':
                $qb->addOrderBy('s.isActive', $dir);
                break;

            case 'slug':
                $qb->addOrderBy('s.slug', $dir);
                break;

            case 'description':
                $qb->addOrderBy('s.description', $dir);
                break;

            case 'name':
            default:
                $qb->addOrderBy('s.name', $dir);
                break;
        }
        if ($field !== 'name') {
            $qb->addOrderBy('s.name', 'ASC');
        }
        return $qb->getQuery()->getResult();
    }
}
