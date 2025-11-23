<?php
namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'home', methods: ['GET'])]
    public function index(
        EntityManagerInterface $em,
        FormFactoryInterface $forms
    ): Response {
        /** @var User|null $user */
        $user = $this->getUser();

        if ($user) {
            $now  = new \DateTimeImmutable('now');
            $last = $user->getLastDailyAt();

            if (!$last || $last->format('Y-m-d') !== $now->format('Y-m-d')) {
                $user->setCoins($user->getCoins() + 1);
                $user->setLastDailyAt($now);
                $em->flush();
                $this->addFlash('daily_added', '1');
            }
        }

        $openForm = $forms->createBuilder()
            ->setAction($this->generateUrl('cauldron_open'))
            ->setMethod('POST')
            ->getForm();

        return $this->render('root/index.html.twig', [
            'openForm' => $openForm,
        ]);
    }
}