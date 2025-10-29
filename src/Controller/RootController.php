<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class RootController extends AbstractController
{
    #[Route('/', name: 'root', methods: ['GET'])]
    public function __invoke(): Response
    {
        // Si admin -> dashboard
        if ($this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('admin_dashboard');
        }

        // Si connecté -> petite landing simple
        if ($this->getUser()) {
            return $this->render('root/index.html.twig');
        }

        // Sinon (invité) -> invite à Login/Register
        return $this->render('root/index.html.twig');
    }
}
