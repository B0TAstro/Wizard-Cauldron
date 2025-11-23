<?php

namespace App\Controller;

use App\Service\MysteryTradeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
final class TradeController extends AbstractController
{
    #[Route('/trade', name: 'trade', methods: ['GET', 'POST'])]
    public function index(Request $request, MysteryTradeService $svc): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        $joinForm = $this->createFormBuilder()
            ->setAction($this->generateUrl('trade'))
            ->setMethod('POST')
            ->getForm();
        $joinForm->handleRequest($request);

        if ($joinForm->isSubmitted() && $joinForm->isValid()) {
            $state = $svc->join($user);
            if (($state['status'] ?? null) === 'done') {
                $this->addFlash(
                    'success',
                    sprintf(
                        'Trade completed with %s — gave: %s • received: %s',
                        $state['partner'] ?? '???',
                        $state['gave'] ?? '???',
                        $state['received'] ?? '???'
                    )
                );
                return $this->redirectToRoute('trade');
            }
            $this->addFlash('info', 'Waiting for another player…');
            return $this->redirectToRoute('trade');
        }

        $cancelForm = $this->createFormBuilder()
            ->setAction($this->generateUrl('trade_cancel'))
            ->setMethod('POST')
            ->getForm();

        $ticket = $svc->getTicket($user);

        return $this->render('trade/index.html.twig', [
            'joinForm'   => $joinForm,
            'cancelForm' => $cancelForm,
            'ticket'     => $ticket,
        ]);
    }

    #[Route('/trade/cancel', name: 'trade_cancel', methods: ['POST'])]
    public function cancel(MysteryTradeService $svc): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $svc->cancel($user);
        $this->addFlash('info', 'You left the queue.');
        return $this->redirectToRoute('trade');
    }
}
