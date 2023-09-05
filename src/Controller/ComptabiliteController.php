<?php

namespace App\Controller;

use App\Entity\Comptabilite;
use App\Form\ComptabiliteType;
use App\Repository\ComptabiliteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Dompdf\Dompdf;

#[Route('/comptabilite')]
class ComptabiliteController extends AbstractController
{
    #[Route('/', name: 'app_comptabilite_index', methods: ['GET'])]
    public function index(ComptabiliteRepository $comptabiliteRepository): Response
    {
      
        return $this->render('comptabilite/index.html.twig', [
            'comptabilites' => $comptabiliteRepository->findAll(),
        ]);
    }
    #[Route('/calculer', name: 'app_comptabilite_valeur', methods: ['POST' , 'GET'])]

    

    #[Route('/new', name: 'app_comptabilite_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ComptabiliteRepository $comptabiliteRepository): Response
    {
        $comptabilite = new Comptabilite();
        $form = $this->createForm(ComptabiliteType::class, $comptabilite);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $comptabiliteRepository->save($comptabilite, true);

            return $this->redirectToRoute('app_comptabilite_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('comptabilite/new.html.twig', [
            'comptabilite' => $comptabilite,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_comptabilite_show', methods: ['GET'])]
    public function show(Comptabilite $comptabilite): Response
    {
        return $this->render('comptabilite/show.html.twig', [
            'comptabilite' => $comptabilite,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_comptabilite_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Comptabilite $comptabilite, ComptabiliteRepository $comptabiliteRepository): Response
    {
        $form = $this->createForm(ComptabiliteType::class, $comptabilite);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $comptabiliteRepository->save($comptabilite, true);

            return $this->redirectToRoute('app_comptabilite_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('comptabilite/edit.html.twig', [
            'comptabilite' => $comptabilite,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_comptabilite_delete', methods: ['POST'])]
    public function delete(Request $request, Comptabilite $comptabilite, ComptabiliteRepository $comptabiliteRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$comptabilite->getId(), $request->request->get('_token'))) {
            $comptabiliteRepository->remove($comptabilite, true);
        }

        return $this->redirectToRoute('app_comptabilite_index', [], Response::HTTP_SEE_OTHER);
    }


   


}
