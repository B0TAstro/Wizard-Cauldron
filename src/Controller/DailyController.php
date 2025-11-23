<?php

namespace App\Controller;

use App\Entity\User;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DailyController extends AbstractController
{
    #[Route('/daily/claim', name: 'daily_claim', methods: ['POST'])]
    public function claim(Request $request, EntityManagerInterface $em): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $this->denyAccessUnlessGranted('ROLE_USER');

        $now = new DateTimeImmutable('now');
        $last = $user->getLastDaily();
        if ($last && $last->format('Y-m-d') === $now->format('Y-m-d')) {
            $this->addFlash('warning', 'Tu as déjà réclamé la récompense quotidienne aujourd’hui.');
            return $this->redirectToRoute('home');
        }

        // +1 coin
        $user->setCoins($user->getCoins() + 1);
        $user->setLastDaily($now);
        $em->flush();

        $this->addFlash('success', 'Daily claim réussi ! +1 coin.');
        return $this->redirectToRoute('home');
    }
}
