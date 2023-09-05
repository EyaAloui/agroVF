<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Repository\CategorieRepository;
use App\Repository\CultureRepository;
use App\Repository\EmployeRepository;
use App\Repository\EquipementRepository;
use App\Repository\EvenementRepository;
use App\Repository\FactureRepository;
use App\Repository\ParticipationRepository;
use App\Repository\ProduitRepository;
use App\Repository\TerrainRepository;
use App\Repository\UtilisateurRepository;
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
    public function utilisateur(UtilisateurRepository $utilisateurRepository): Response
    {
        return $this->render('utilisateur/index.html.twig', [
            'utilisateurs' => $utilisateurRepository->findAll(),
        ]);
    }
    #[Route('/user', name: 'app_dashboardUser')]
    public function user(): Response
    {
        return $this->render('dashboard/page-user.html.twig', [
            'controller_name' => 'DashboardController',
        ]);
    }
    #[Route('/facture', name: 'app_dashboardFacture')]
    public function facture(FactureRepository $factureRepository): Response
    {
        return $this->render('facture/index.html.twig', [
            'factures' => $factureRepository->findAll(),
        ]);
    }

    #[Route('/comptabilite', name: 'app_dashboardComptabilite')]
    public function comptabilite(CultureRepository $cultureRepository): Response
    {
        return $this->render('comptabilite/index.html.twig', [
            'comptabilites' => $cultureRepository->findAll(),
        ]);
    }

    #[Route('/culture', name: 'app_dashboardCulture')]
    public function culture(CultureRepository $cultureRepository): Response
    {
        return $this->render('culture/index.html.twig', [
            'cultures' => $cultureRepository->findAll(),
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }


    #[Route('/employe', name: 'app_dashboardEmploye')]
    public function employe(EmployeRepository $employeRepository): Response
    {
        return $this->render('employe/index.html.twig', [
            'employes' => $employeRepository->findAll(),
        ]);
    }

    #[Route('/evenement', name: 'app_dashboardEvenement')]
    public function evenement(EvenementRepository $repository): Response
    {
        return $this->render('evenement/index.html.twig', [
            'evenements' =>$repository->findAll()
        ]);
    }
    #[Route('/participant', name: 'app_dashboardParticipant')]
    public function participant(ParticipationRepository $repository): Response
    {
        return $this->render('participation/index.html.twig', [
            'participations' =>$repository->findAll()
        ]);
    }
    #[Route('/produit', name: 'app_dasshboardProduit')]
    public function produit(ProduitRepository $produitRepository): Response
    {
        return $this->render('produit/index.html.twig', [
            'produits' => $produitRepository->findAll(),
        ]);
    }

    #[Route('/categorie', name: 'app_dashboardCategorie')]
    public function categorie(CategorieRepository $categorieRepository): Response
    {
        return $this->render('categorie/index.html.twig', [
            'categories' => $categorieRepository->findAll(),
        ]);
    }

    #[Route('/equipement', name: 'app_dashboardEquipement')]
    public function equipement(EquipementRepository $repository ): Response
    {
        return $this->render('equipement/index.html.twig', [
            'equipements' => $repository->findAll(),
        ]);
    }
    #[Route('/terrain', name: 'app_dashboardTerrain')]
    public function terrain( TerrainRepository $terrainRepository): Response
    {
        return $this->render('terrain/index.html.twig', [
            'terrains' => $terrainRepository->findAll(),
        ]);
    }


}
