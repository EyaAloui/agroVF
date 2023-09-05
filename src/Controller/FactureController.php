<?php

namespace App\Controller;

use App\Entity\Facture;
use App\Form\FactureType;
use App\Repository\FactureRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Dompdf\Dompdf;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Comptabilite;


#[Route('/facture')]
class FactureController extends AbstractController
{
    #[Route('/', name: 'app_facture_index', methods: ['GET'])]
    public function index(FactureRepository $factureRepository): Response
    {
        $types= $factureRepository -> getType();
        $html = $this -> renderView ('facture/index.html.twig',[
            'facture' => $factureRepository,
            'types' => $types,
        ]);
        return $this->render('facture/index.html.twig', [
            'factures' => $factureRepository->findAll(),
        ]);
    }

    #[Route('/pdf', name: 'app_facture_pdf')]
    public function generatePdf(FactureRepository $factureRepository)
    {
        $factures = $factureRepository->findAll([]);
        if (empty($factures)) {
            $this->addFlash('error', 'Aucune facture à imprimer.');

            return $this->redirectToRoute('app_facture_index');
        }

        $html = $this->renderView('facture/pdf.html.twig', [
            'factures' => $factures,
           
        ]);

        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $response = new Response($dompdf->output());
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', 'attachment;filename=facture.pdf');
        return $response;
    }

    public function searchFacture(Request $request, FactureRepository $factureRepository)
{
    $searchTerm = $request->query->get('q');
    $factures = $factureRepository->search($searchTerm);

    $jsonData = [];
    foreach ($factures as $facture) {
        $jsonData[] = [
            'id' => $facture->getId(),
            'date' => $facture->getDate()->format('Y-m-d'),
            'montant' => $facture->getMontant(),
            'type' => $facture->getType(),
        ];
    }

    return new JsonResponse($jsonData);
}
    #[Route('/recherche_ajax', name: 'recherche_ajax')]

    public function rechercheAjax(Request $request, UserRepository $sr): JsonResponse
    {
        $requestString = $request->query->get('searchValue');
        $resultats = $sr->findUserByNsc($requestString);
        return $this->json($resultats);
    }



    

    #[Route('/new', name: 'app_facture_new', methods: ['GET', 'POST'])]
    public function new(Request $request, FactureRepository $factureRepository): Response
    {
        $facture = new Facture();
        $form = $this->createForm(FactureType::class, $facture);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $factureRepository->save($facture, true);

            return $this->redirectToRoute('app_facture_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('facture/new.html.twig', [
            'facture' => $facture,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_facture_show', methods: ['GET'])]
    public function show(Facture $facture): Response
    {
        return $this->render('facture/show.html.twig', [
            'facture' => $facture,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_facture_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Facture $facture, FactureRepository $factureRepository): Response
    {
        $form = $this->createForm(FactureType::class, $facture);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $factureRepository->save($facture, true);

            return $this->redirectToRoute('app_facture_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('facture/edit.html.twig', [
            'facture' => $facture,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_facture_delete', methods: ['POST'])]
    public function delete(Request $request, Facture $facture, FactureRepository $factureRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$facture->getId(), $request->request->get('_token'))) {
            $factureRepository->remove($facture, true);
        }

        return $this->redirectToRoute('app_facture_index', [], Response::HTTP_SEE_OTHER);
    }

    public function submitForm(Request $request)
    {
        $form = $this->createForm(FactureType::class);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $facture = $form->getData();
            // Do something with the form data
        }
    
        // Render the form template
        return $this->render('_form.html.twig', [
            'form' => $form->createView(),
        ]);
    }

//pour mobile

    #[Route('/mobile/all', name: 'app_facture_index', methods: ['POST'])]
    public function showFacture(FactureRepository $factureRepository): JsonResponse
    {
        $factures = $factureRepository->findAll();
    
        $facturesData = array_map(function($facture) {
            $comptabilite = $facture->getComptabilite();
    
            return [
                'id' => $facture->getId(),
                'type' => $facture->getType(),
                'montantTotale' => $facture->getMontantTotale(),
                'dateFacture'=> $facture->getDateFacture()->format('Y-m-d'),
                'comptabilite' => [
                    'id' => $comptabilite->getId(),
                    'dateComptabilite' => $comptabilite->getDateComptabilite()->format('Y-m-d'),
                    'valeur' => $comptabilite->getValeur(),
                ],
            ];
        }, $factures);
    
        return new JsonResponse($facturesData);
    }
  
#[Route('/mobile/add', name: 'app_facture_add', methods: ['POST'])]
public function add(Request $request, EntityManagerInterface $entityManager): JsonResponse
{
    // Get values from the request
    $type = $request->get('type');
    $montantTotale = $request->get('montantTotale');
    $comptabiliteId = $request->get('comptabiliteId');
    $dateFacture = $request->get('dateFacture');


    // Find the Comptabilite by its ID
    $comptabilite = $entityManager->getRepository(Comptabilite::class)->find($comptabiliteId);

    // Create a new Facture instance
    $facture = new Facture();
    $facture->setType($type);
    $facture->setMontantTotale($montantTotale);
    $facture->setComptabilite($comptabilite);
    $facture->setDateFacture(new \DateTimeImmutable($dateFacture));

    // Persist the new Facture in the database
    $entityManager->persist($facture);
    $entityManager->flush();

    // Return a success JSON response
    return new JsonResponse([
        'success' => true,
        'message' => 'Facture ajoutée avec succès.'
    ]);
}
#[Route('/mobile/edit/{id}', name: 'app_facture_edit', methods: ['POST'])]
public function editmobile(Request $request, Facture $facture): Response
{
    $type = $request->get('type');
    $montantTotale = $request->get('montantTotale');
    $comptabiliteId = $request->get('comptabiliteId');
    $dateFacture = $request->get('dateFacture');

    $entityManager = $this->getDoctrine()->getManager();

    $facture->setType($type);
    $facture->setMontantTotale($montantTotale);
    $facture->setDateFacture(new \DateTimeImmutable($dateFacture));


    // Update the Comptabilite for the Facture if provided
    if ($comptabiliteId) {
        $comptabilite = $entityManager->getRepository(Comptabilite::class)->find($comptabiliteId);
        if (!$comptabilite) {
            throw $this->createNotFoundException('Comptabilite with ID '.$comptabiliteId.' not found.');
        }
        $facture->setComptabilite($comptabilite);
    }

    $entityManager->flush();

    return new JsonResponse(['message' => 'Facture updated successfully.']);
}

#[Route('/mobile/delete/{id}', name: 'app_facture_delete', methods: ['DELETE'])]
public function deletemobile(Request $request, Facture $facture): Response
{
    $entityManager = $this->getDoctrine()->getManager();
    $entityManager->remove($facture);
    $entityManager->flush();

    return new JsonResponse(['message' => 'Facture deleted successfully.']);
}

#[Route('/mobile/allComptabilite', name: 'app_comptabilite_index', methods: ['POST'])]
public function getComptabilite(): JsonResponse
{
    $comptabilites = $this->getDoctrine()->getRepository(Comptabilite::class)->findAll();

    $comptabilitesArray = array_map(function (Comptabilite $comptabilite) {
        return [
            'id' => $comptabilite->getId(),
            'date_comptabilite' => $comptabilite->getDateComptabilite()->format('Y-m-d'),
            'valeur' => $comptabilite->getValeur(),
        ];
    }, $comptabilites);

    return $this->json($comptabilitesArray, Response::HTTP_OK);
}



}
