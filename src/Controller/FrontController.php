<?php

namespace App\Controller;

use App\Repository\ProduitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class FrontController extends AbstractController
{
    #[Route('/front', name: 'app_front')]
    public function index(ProduitRepository $produitRepository): Response
    {
        return $this->render('front/index.html.twig', [
            'controller_name' => 'FrontController',
            'produits' => $produitRepository->findAll(),
        ]);
    }

    #[Route('/produits', name: 'app_frontProduit')]
    public function showProducts(ProduitRepository $produitRepository)
    {
        return $this->render('front/produits.html.twig', [
            'produits' => $produitRepository->findAll(),
        ]);
    }
}
