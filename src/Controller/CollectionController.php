<?php

namespace App\Controller;

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
        $unlockedIds = $userSpells->findSpellIdsUnlockedForUser($user->getId());
        $rarity = strtolower((string)$request->query->get('rarity', ''));
        $criteria = ['isActive' => true];
        if (in_array($rarity, ['common','rare','epic','legendary'], true)) {
            $criteria['rarity'] = $rarity;
        }

        $all = $spells->findBy($criteria, ['rarity' => 'ASC', 'name' => 'ASC']);

        return $this->render('user/collection.html.twig', [
            'spells'      => $all,
            'unlockedIds' => $unlockedIds,
            'activeRarity'=> $rarity,
        ]);
    }
}

?>