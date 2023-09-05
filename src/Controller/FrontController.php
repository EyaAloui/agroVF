<?php

namespace App\Controller;

use App\Repository\CategorieRepository;
use App\Repository\EvenementRepository;
use App\Repository\ProduitRepository;
use App\Entity\Evenement;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Endroid\QrCode\Builder\BuilderInterface;



class FrontController extends AbstractController
{
    #[Route('/frontAG', name: 'app_front')]
    public function index(EvenementRepository $repository,ProduitRepository $produitRepository,CategorieRepository $categorieRepository,BuilderInterface $customQrCodeBuilder): Response
    {
        $listqr= $this->listQr($produitRepository->findAll(), $customQrCodeBuilder);

        return $this->render('front/index.html.twig', [
            'controller_name' => 'FrontController',
            'produits' => $produitRepository->findAll(),
            'categories' => $categorieRepository->findAll(),'listqr'=>$listqr,
            'evenements' => $repository->findAll(),
        ]);
    }

    #[Route('/produits', name: 'app_frontProduit')]
    public function showProducts(ProduitRepository $produitRepository,CategorieRepository $categorieRepository)
    {
        return $this->render('front/produits.html.twig', [
            'produits' => $produitRepository->findAll(),
            'categories' => $categorieRepository->findAll()
        ]);
    }

    #[Route('/produits/selected', name: 'app_frontProduitSelected', methods: ['GET'])]
    public function categoryProducts(BuilderInterface $customQrCodeBuilder,Request $request,CategorieRepository $categorieRepository,ProduitRepository $produitRepository)
    {
        $categories = $categorieRepository->findAll();
        $produits = $produitRepository->findAll();

        $categorieId = $request->query->get('categorie');
        if ($categorieId) {
            $produits = $produitRepository->findBy(['categorie' => $categorieId]);
        }
        return $this->render('front/index.html.twig', [
            'produits' => $produits,
            'categories' => $categories

        ]);
    }

    #[Route("/front/event_affiche", name: "app_front_event_affiche")]
    public function eventAffiche(): Response
    {
        $evenements = $this->getDoctrine()->getRepository(Evenement::class)->findAll();
        return $this->render('front/event_affiche.html.twig', [
            'evenements' => $evenements,
        ]);
    }


    public function listQr($produits,$customQrCodeBuilder){
        $list= array();
        foreach ($produits as $produit){

            $text = sprintf(
                'Nom: %s |Prix: %s | Type: %s | Categorie: %s | Ticket: %s',
                $produit->getNom(),
                $produit->getPrix(),
                $produit->getDescription(),
                $produit->getCategorie() ,
                $produit->getId()
            );

            $result = $customQrCodeBuilder
                ->size(100)
                ->margin(20)
                ->data($text)
                ->build();

            $base64 = $result->getDataUri();
            array_push($list, $base64);
        }
        return $list;
    }
}
