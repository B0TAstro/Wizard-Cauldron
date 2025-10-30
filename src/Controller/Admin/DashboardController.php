<?php

namespace App\Controller\Admin;

use App\Repository\SpellRepository;
use App\Repository\UserRepository;
use App\Repository\UserSpellRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
final class DashboardController extends AbstractController
{
    #[Route('', name: 'admin', methods: ['GET'])]
    public function __invoke(
        UserRepository $users,
        UserSpellRepository $userSpells,
        SpellRepository $spells
    ): Response {
        $allUsers = $users->findBy([], ['createdAt' => 'DESC']);
        $totalActive = $spells->countActive();

        $userRows = array_map(function($u) use ($userSpells, $totalActive) {
            return [
                'id'       => $u->getId(),
                'pseudo'   => $u->getPseudo(),
                'email'    => $u->getEmail(),
                'roles'    => implode(', ', $u->getRoles()),
                'coins'    => $u->getCoins(),
                'unlocked' => $userSpells->countUnlockedForUser($u->getId()),
                'total'    => $totalActive,
            ];
        }, $allUsers);

        $recentSpells = $spells->findBy(['isActive' => true], ['createdAt' => 'DESC'], 20);

        return $this->render('admin/dashboard.html.twig', [
            'users'  => $userRows,
            'spells' => $recentSpells,
        ]);
    }
}