<?php

namespace App\Controller;

use App\Entity\Spell;
use App\Entity\User;
use App\Entity\UserSpell;
use App\Repository\SpellRepository;
use App\Repository\UserSpellRepository;
use App\Service\RngService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CauldronController extends AbstractController
{
    public function __construct(
        private readonly RequestStack $stack,
    ) {}

    #[Route('/cauldron/open', name: 'cauldron_open', methods: ['POST'])]
    public function open(
        SpellRepository $spells,
        UserSpellRepository $userSpells,
        RngService $rng,
        EntityManagerInterface $em
    ): Response {
        /** @var User $user */
        $user = $this->getUser();
        $this->denyAccessUnlessGranted('ROLE_USER');

        if ($user->getCoins() < 1) {
            $this->addFlash('warning', 'Pas assez de coins.');
            return $this->redirectToRoute('home');
        }

        $poolByRarity = [
            'legendary' => $spells->findBy(['isActive' => true, 'rarity' => 'legendary']),
            'epic'      => $spells->findBy(['isActive' => true, 'rarity' => 'epic']),
            'rare'      => $spells->findBy(['isActive' => true, 'rarity' => 'rare']),
            'common'    => $spells->findBy(['isActive' => true, 'rarity' => 'common']),
        ];

        $weights = [
            'legendary' => 5,
            'epic'      => 10,
            'rare'      => 25,
            'common'    => 60,
        ];

        /** @var Spell $drop */
        $drop = $rng->pick($poolByRarity, $weights);
        if (!$drop) {
            $this->addFlash('danger', 'Aucun sort disponible.');
            return $this->redirectToRoute('home');
        }
        $user->setCoins($user->getCoins() - 1);

        $existing = $userSpells->findOneBy(['user' => $user, 'spell' => $drop]);
        $isNew = false;
        if (!$existing) {
            $link = new UserSpell();
            $link->setUser($user)->setSpell($drop);
            $em->persist($link);
            $isNew = true;
        }

        $em->flush();

        $this->stack->getSession()->set('drop', [
            'id'    => $drop->getId(),
            'name'  => $drop->getName(),
            'desc'  => $drop->getDescription(),
            'img'   => $drop->getImageUrl(),
            'rar'   => $drop->getRarity(),
            'isNew' => $isNew,
        ]);

        return $this->redirectToRoute('cauldron_result');
    }

    #[Route('/cauldron/result', name: 'cauldron_result', methods: ['GET'])]
    public function result(SpellRepository $spells): Response
    {
        $bag = $this->stack->getSession();
        $data = $bag->get('drop');
        if (!$data) {
            return $this->redirectToRoute('home');
        }

        $spell = $spells->find($data['id']);

        $bag->remove('drop');

        return $this->render('cauldron/result.html.twig', [
            'spell' => $spell,
            'isNew' => (bool)($data['isNew'] ?? false),
        ]);
    }
}
