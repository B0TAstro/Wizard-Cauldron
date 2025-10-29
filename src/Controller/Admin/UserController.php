<?php
namespace App\Controller\Admin;

use App\Entity\User;
use App\Repository\SpellRepository;
use App\Repository\UserRepository;
use App\Repository\UserSpellRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/users')]
final class UserController extends AbstractController
{
    #[Route('/', name: 'admin_user_index', methods: ['GET'])]
    public function index(
        UserRepository $users,
        UserSpellRepository $userSpells,
        SpellRepository $spells
    ): Response {
        $all = $users->findBy([], ['createdAt' => 'DESC']);
        $totalActive = $spells->countActive();

        $rows = array_map(function(User $u) use ($userSpells, $totalActive) {
            return [
                'user' => $u,
                'unlocked' => $userSpells->countUnlockedForUser($u->getId()),
                'total' => $totalActive,
            ];
        }, $all);

        return $this->render('admin/user/index.html.twig', ['rows' => $rows]);
    }

    #[Route('/{id}/delete', name: 'admin_user_delete', methods: ['POST'])]
    public function delete(Request $req, User $user, EntityManagerInterface $em): Response
    {
        if (!$this->isCsrfTokenValid('del_user_'.$user->getId(), (string)$req->request->get('_token'))) {
            $this->addFlash('error', 'Invalid CSRF token.');
            return $this->redirectToRoute('admin_user_index');
        }
        $em->remove($user);
        $em->flush();
        $this->addFlash('success', 'User removed.');
        return $this->redirectToRoute('admin_user_index');
    }
}
