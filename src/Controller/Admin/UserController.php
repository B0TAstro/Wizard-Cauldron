<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Entity\UserSpell;
use App\Form\UserType;
use App\Form\Admin\AddCoinsType;
use App\Form\Admin\ToggleAdminType;
use App\Form\Admin\GrantSpellFormType;
use App\Form\Admin\RevokeSpellFormType;
use App\Repository\SpellRepository;
use App\Repository\UserRepository;
use App\Repository\UserSpellRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/admin/users')]
final class UserController extends AbstractController
{
    #[Route('/', name: 'admin_user', methods: ['GET'])]
    public function index(UserRepository $users, UserSpellRepository $userSpells, SpellRepository $spells): Response
    {
        $all = $users->findBy([], ['createdAt' => 'DESC']);
        $totalActive = $spells->countActive();

        $rows = array_map(function (User $u) use ($userSpells, $totalActive) {
            return [
                'id'         => $u->getId(),
                'pseudo'     => $u->getPseudo(),
                'email'      => $u->getEmail(),
                'roles'      => $u->getRoles(),
                'coins'      => $u->getCoins(),
                'lastDaily'  => $u->getLastDailyAt(),
                'createdAt'  => $u->getCreatedAt(),
                'unlocked'   => $userSpells->countUnlockedForUser($u->getId()),
                'total'      => $totalActive,
            ];
        }, $all);

        return $this->render('admin/user/index.html.twig', [
            'users' => $rows,
        ]);
    }

    #[Route('/new', name: 'admin_user_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher
    ): Response {
        $user = new User();
        $user->setCoins(0);

        $form = $this->createForm(UserType::class, $user, [
            'require_password' => true,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plain */
            $plain = (string) $form->get('newPassword')->getData();
            $user->setPassword($hasher->hashPassword($user, $plain));
            $user->setRoles(array_values(array_unique($user->getRoles())));

            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'User created.');
            return $this->redirectToRoute('admin_user');
        }

        return $this->render('admin/user/new.html.twig', [
            'form' => $form,
        ]);
    }


    #[Route('/{id}/edit', name: 'admin_user_edit', methods: ['GET', 'POST'])]
    public function edit(
        User $user,
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher,
        UserSpellRepository $userSpells
    ): Response {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string|null $plain */
            $plain = $form->get('newPassword')->getData();
            if ($plain) {
                $user->setPassword($hasher->hashPassword($user, $plain));
            }
            $user->setRoles($user->getRoles());
            $em->flush();
            $this->addFlash('success', 'User updated.');
            return $this->redirectToRoute('admin_user');
        }
        $qb = fn($er) => $er->createQueryBuilder('s')
            ->andWhere('s.isActive = :a')->setParameter('a', true)
            ->orderBy('s.rarity', 'ASC')->addOrderBy('s.name', 'ASC');
        $grantForm = $this->createForm(GrantSpellFormType::class, null, [
            'action' => $this->generateUrl('admin_user_grant', ['id' => $user->getId()]),
            'method' => 'POST',
            'qb'     => $qb,
        ]);

        $links = $userSpells->findBy(['user' => $user->getId()]);
        $owned = [];
        $revokeForms = [];
        foreach ($links as $link) {
            $s = $link->getSpell();
            if (!$s) {
                continue;
            }
            $owned[] = $s;
            $f = $this->createForm(RevokeSpellFormType::class, ['spellId' => $s->getId()], [
                'action' => $this->generateUrl('admin_user_revoke', [
                    'id'      => $user->getId(),
                    'spellId' => $s->getId(),
                ]),
                'method' => 'POST',
            ]);
            $revokeForms[$s->getId()] = $f->createView();
        }

        return $this->render('admin/user/edit.html.twig', [
            'form'         => $form,
            'userEntity'   => $user,
            'grantForm'    => $grantForm->createView(),
            'ownedSpells'  => $owned,
            'revokeForms'  => $revokeForms,
        ]);
    }

    #[Route('/{id}/add-coins', name: 'admin_user_addcoins', methods: ['POST'])]
    public function addCoins(User $user, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(AddCoinsType::class);
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            $this->addFlash('error', 'Invalid form.');
            return $this->redirectToRoute('admin');
        }

        $amount = (int) $form->get('amount')->getData();

        $amount = max(-1000, min(1000, $amount));
        $user->setCoins(max(0, $user->getCoins() + $amount));

        $em->flush();
        $this->addFlash('success', 'Coins updated.');
        return $this->redirectToRoute('admin');
    }

    #[Route('/{id}/toggle-admin', name: 'admin_user_toggle_admin', methods: ['POST'])]
    public function toggleAdmin(User $user, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(ToggleAdminType::class);
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            $this->addFlash('error', 'Invalid form.');
            return $this->redirectToRoute('admin');
        }

        $roles = $user->getRoles();
        if (in_array('ROLE_ADMIN', $roles, true)) {
            $roles = array_values(array_diff($roles, ['ROLE_ADMIN']));
        } else {
            $roles[] = 'ROLE_ADMIN';
        }
        $user->setRoles($roles);
        $em->flush();

        $this->addFlash('success', 'Roles updated.');
        return $this->redirectToRoute('admin');
    }

    #[Route('/{id}/grant', name: 'admin_user_grant', methods: ['POST'])]
    public function grant(
        User $user,
        Request $request,
        SpellRepository $spells,
        UserSpellRepository $userSpells,
        EntityManagerInterface $em
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $qb = fn($er) => $er->createQueryBuilder('s')
            ->andWhere('s.isActive = :a')->setParameter('a', true)
            ->orderBy('s.rarity', 'ASC')->addOrderBy('s.name', 'ASC');

        $form = $this->createForm(GrantSpellFormType::class, null, ['qb' => $qb]);
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            $this->addFlash('error', 'Invalid form.');
            return $this->redirectToRoute('admin_user_edit', ['id' => $user->getId()]);
        }

        /** @var \App\Entity\Spell $spell */
        $spell = $form->get('spell')->getData();
        if (!$spell) {
            $this->addFlash('error', 'Spell not found.');
            return $this->redirectToRoute('admin_user_edit', ['id' => $user->getId()]);
        }
        if ($userSpells->findOneBy(['user' => $user, 'spell' => $spell])) {
            $this->addFlash('warning', 'Already owned.');
            return $this->redirectToRoute('admin_user_edit', ['id' => $user->getId()]);
        }

        $link = (new UserSpell())
            ->setUser($user)
            ->setSpell($spell)
            ->setObtainedAt(new \DateTimeImmutable());

        $em->persist($link);
        $em->flush();

        $this->addFlash('success', 'Spell granted.');
        return $this->redirectToRoute('admin_user_edit', ['id' => $user->getId()]);
    }

    #[Route('/{id}/revoke/{spellId}', name: 'admin_user_revoke', methods: ['POST'])]
    public function revoke(
        User $user,
        int $spellId,
        Request $request,
        UserSpellRepository $userSpells,
        EntityManagerInterface $em
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $form = $this->createForm(RevokeSpellFormType::class, null);
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            $this->addFlash('error', 'Invalid form.');
            return $this->redirectToRoute('admin_user_edit', ['id' => $user->getId()]);
        }

        $postedId = (int)$form->get('spellId')->getData();
        if ($postedId !== $spellId) {
            $this->addFlash('error', 'Invalid spell id.');
            return $this->redirectToRoute('admin_user_edit', ['id' => $user->getId()]);
        }

        $link = $userSpells->findOneBy(['user' => $user->getId(), 'spell' => $spellId]);
        if (!$link) {
            $this->addFlash('warning', 'Link not found.');
            return $this->redirectToRoute('admin_user_edit', ['id' => $user->getId()]);
        }

        $em->remove($link);
        $em->flush();

        $this->addFlash('success', 'Spell revoked.');
        return $this->redirectToRoute('admin_user_edit', ['id' => $user->getId()]);
    }

    #[Route('/{id}/delete', name: 'admin_user_delete', methods: ['POST'])]
    public function delete(Request $req, User $user, EntityManagerInterface $em): Response
    {
        if (!$this->isCsrfTokenValid('del_user_' . $user->getId(), (string)$req->request->get('_token'))) {
            $this->addFlash('error', 'Invalid CSRF token.');
            return $this->redirectToRoute('admin_user');
        }
        $em->remove($user);
        $em->flush();
        $this->addFlash('success', 'User removed.');
        return $this->redirectToRoute('admin_user');
    }
}
