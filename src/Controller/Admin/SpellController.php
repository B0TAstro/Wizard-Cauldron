<?php

namespace App\Controller\Admin;

use App\Entity\Spell;
use App\Form\SpellType;
use App\Repository\SpellRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/spells')]
final class SpellController extends AbstractController
{
    #[Route('/', name: 'admin_spell', methods: ['GET'])]
    public function index(SpellRepository $repo): Response
    {
        return $this->render('admin/spells/index.html.twig', [
            'spells' => $repo->findBy([], ['createdAt' => 'DESC']),
        ]);
    }

    #[Route('/new', name: 'admin_spell_new', methods: ['GET','POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $spell = new Spell();
        $form = $this->createForm(SpellType::class, $spell);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($spell);
            $em->flush();
            $this->addFlash('success', 'Sort créé.');
            return $this->redirectToRoute('admin_spell_index');
        }

        return $this->render('admin/spells/new.html.twig', [
            'spell' => $spell,
            'form'  => $form,
        ]);
    }

    #[Route('/{id}', name: 'admin_spell_show', methods: ['GET'])]
    public function show(Spell $spell): Response
    {
        return $this->render('admin/spells/show.html.twig', [
            'spell' => $spell,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_spell_edit', methods: ['GET','POST'])]
    public function edit(Request $request, Spell $spell, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(SpellType::class, $spell);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Sort modifié.');
            return $this->redirectToRoute('admin_spell');
        }

        return $this->render('admin/spells/edit.html.twig', [
            'spell' => $spell,
            'form'  => $form,
        ]);
    }

    #[Route('/{id}', name: 'admin_spell_delete', methods: ['POST'])]
    public function delete(Request $request, Spell $spell, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$spell->getId(), $request->request->get('_token'))) {
            $em->remove($spell);
            $em->flush();
            $this->addFlash('success', 'Sort supprimé.');
        }
        return $this->redirectToRoute('admin_spell_index');
    }
}