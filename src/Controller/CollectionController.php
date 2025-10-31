<?php

namespace App\Controller;

use App\Entity\Spell;
use App\Repository\SpellRepository;
use App\Repository\UserSpellRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
final class CollectionController extends AbstractController
{
    #[Route('/collection', name: 'collection', methods: ['GET'])]
    public function __invoke(
        Request $request,
        SpellRepository $spells,
        UserSpellRepository $userSpells
    ): Response {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        $activeFilter = $request->query->getString('f', 'all'); // 'all' | 'unlocked'
        /** @var Spell[] $all */
        $all = $spells->findBy(['isActive' => true], ['createdAt' => 'DESC']);
        $unlockedIds = $userSpells->findUnlockedSpellIdsForUser($user->getId());

        if ($activeFilter === 'unlocked') {
            $all = array_values(array_filter(
                $all,
                fn(Spell $spellItem): bool => in_array($spellItem->getId(), $unlockedIds, true)
            ));
        }

        return $this->render('collection/index.html.twig', [
            'spells'       => $all,
            'unlockedIds'  => $unlockedIds,
            'activeFilter' => $activeFilter,
        ]);
    }

    #[Route('/collection/{id}/popup', name: 'collection_popup', methods: ['GET'])]
    public function popup(Spell $spell, UserSpellRepository $userSpells): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $this->denyAccessUnlessGranted('ROLE_USER');

        $owned = (int) $userSpells->createQueryBuilder('us')
            ->select('COUNT(us.id)')
            ->where('us.user = :u')
            ->andWhere('us.spell = :s')
            ->setParameter('u', $user)
            ->setParameter('s', $spell)
            ->getQuery()
            ->getSingleScalarResult() > 0;

        if (!$owned) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('collection/_popup.html.twig', [
            'spell' => $spell,
        ]);
    }
}
