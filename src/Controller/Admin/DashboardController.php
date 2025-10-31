<?php

namespace App\Controller\Admin;

use App\Form\Admin\AddCoinsType;
use App\Form\Admin\ToggleAdminType;
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
        SpellRepository $spells,
        UserSpellRepository $userSpells
    ): Response {
        $totalUsers   = $users->count([]);
        $totalSpells  = $spells->count([]);
        $activeSpells = $spells->count(['isActive' => true]);
        $allUsers    = $users->findBy([], ['createdAt' => 'DESC']);
        $totalActive = $spells->countActive();

        $userRows = array_map(function ($u) use ($userSpells, $totalActive) {
            return [
                'id'       => $u->getId(),
                'pseudo'   => $u->getPseudo(),
                'email'    => $u->getEmail(),
                'roles'    => $u->getRoles(),
                'coins'    => $u->getCoins(),
                'unlocked' => $userSpells->countUnlockedForUser($u->getId()),
                'total'    => $totalActive,
            ];
        }, $allUsers);

        $recentSpells = $spells->findBy(['isActive' => true], ['createdAt' => 'DESC'], 20);

        $toggleForms = [];
        $coinsForms  = [];
        foreach ($allUsers as $u) {
            $toggleForms[$u->getId()] = $this->createForm(
                ToggleAdminType::class,
                null,
                [
                    'action' => $this->generateUrl('admin_user_toggle_admin', ['id' => $u->getId()]),
                    'method' => 'POST',
                ]
            )->createView();

            $coinsForms[$u->getId()] = $this->createForm(
                AddCoinsType::class,
                null,
                [
                    'action' => $this->generateUrl('admin_user_addcoins', ['id' => $u->getId()]),
                    'method' => 'POST',
                ]
            )->createView();
        }

        return $this->render('admin/dashboard.html.twig', [
            'users'        => $userRows,
            'spells'       => $recentSpells,
            'totalUsers'   => $totalUsers,
            'totalSpells'  => $totalSpells,
            'activeSpells' => $activeSpells,
            'toggleForms'  => $toggleForms,
            'coinsForms'   => $coinsForms,
        ]);
    }
}