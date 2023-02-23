<?php

namespace App\Controller;

use App\Repository\CategorieRepository;
use App\Repository\ProduitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(): Response
    {
        return $this->render('dashboard/index.html.twig', [
            'controller_name' => 'DashboardController',
        ]);
    }
    #[Route('/client', name: 'app_dashboardClient')]
    public function client(): Response
    {
        return $this->render('dashboard/page-clients.html.twig', [
            'controller_name' => 'DashboardController',
        ]);
    }
    #[Route('/user', name: 'app_dashboardUser')]
    public function user(): Response
    {
        return $this->render('dashboard/page-user.html.twig', [
            'controller_name' => 'DashboardController',
        ]);
    }

    #[Route('/comptabilite', name: 'app_dashboardComptabilite')]
    public function comptabilite(): Response
    {
        return $this->render('dashboard/page-comptabilite.html.twig', [
            'controller_name' => 'DashboardController',
        ]);
    }
    #[Route('/culture', name: 'app_dashboardCulture')]
    public function culture(): Response
    {
        return $this->render('dashboard/page-culture.html.twig', [
            'controller_name' => 'DashboardController',
        ]);
    }
    #[Route('/employe', name: 'app_dashboardEmploye')]
    public function employe(): Response
    {
        return $this->render('dashboard/page-employe.html.twig', [
            'controller_name' => 'DashboardController',
        ]);
    }
    #[Route('/evenement', name: 'app_dashboardEvenement')]
    public function evenement(): Response
    {
        return $this->render('dashboard/page-event.html.twig', [
            'controller_name' => 'DashboardController',
        ]);
    }
    #[Route('/facture', name: 'app_dashboardFacture')]
    public function facture(): Response
    {
        return $this->render('dashboard/page-facture.html.twig', [
            'controller_name' => 'DashboardController',
        ]);
    }
    #[Route('/participant', name: 'app_dashboardParticipant')]
    public function participant(): Response
    {
        return $this->render('dashboard/page-participant.html.twig', [
            'controller_name' => 'DashboardController',
        ]);
    }
    #[Route('/produit', name: 'app_dasshboardProduit')]
    public function produit(ProduitRepository $produitRepository): Response
    {
        return $this->render('produit/index.html.twig', [
            'produits' => $produitRepository->findAll(),
        ]);
    }
    #[Route('/categorie', name: 'app_dashboardCqtegorie')]
    public function categorie(CategorieRepository $categorieRepository): Response
    {
        return $this->render('categorie/index.html.twig', [
            'categories' => $categorieRepository->findAll(),
        ]);
    }
    #[Route('/terrain', name: 'app_dashboardTerrain')]
    public function terrain(): Response
    {
        return $this->render('dashboard/page-terrain.html.twig', [
            'controller_name' => 'DashboardController',
        ]);
    }
    #[Route('/equipement', name: 'app_dashboardEquipement')]
    public function vehicule(): Response
    {
        return $this->render('dashboard/page-equipement.html.twig', [
            'controller_name' => 'DashboardController',
        ]);
    }
}
