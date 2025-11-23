<?php
namespace App\Controller;

use App\Repository\SpellRepository;
use App\Repository\UserSpellRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'home', methods: ['GET'])]
    public function __invoke(
        SpellRepository $spells,
        UserSpellRepository $userSpells
    ): Response {
        $user = $this->getUser();
        return $this->render('root/index.html.twig', [
            'isLogged' => (bool)$user,
        ]);
    }
}