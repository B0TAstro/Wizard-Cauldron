<?php
namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'home', methods: ['GET'])]
    public function __invoke(FormFactoryInterface $forms): Response
    {
        /** @var User|null $user */
        $user = $this->getUser();

        $dailyForm = null;
        $openForm  = null;

        if ($user) {
            $dailyForm = $forms->createBuilder()
                ->setAction($this->generateUrl('daily_claim'))
                ->setMethod('POST')
                ->getForm();

            $openForm = $forms->createBuilder()
                ->setAction($this->generateUrl('cauldron_open'))
                ->setMethod('POST')
                ->getForm();
        }

        return $this->render('root/index.html.twig', [
            'dailyForm' => $dailyForm?->createView(),
            'openForm'  => $openForm?->createView(),
            'user'      => $user,
        ]);
    }
}