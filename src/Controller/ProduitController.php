<?php

namespace App\Controller;

use App\Entity\dto\Pie;
use App\Entity\Produit;
use App\Form\ProduitType;
use App\Repository\CategorieRepository;
use App\Repository\ProduitRepository;
use App\Repository\UtilisateurRepository;
use Knp\Component\Pager\PaginatorInterface;
use MercurySeries\FlashyBundle\FlashyNotifier;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Endroid\QrCode\Builder\BuilderInterface;

#[Route('/produit')]
class ProduitController extends AbstractController
{
    #[Route('/all', name: 'app_produit_index', methods: ['GET'])]
    public function index(Request $request, ProduitRepository $produitRepository, PaginatorInterface $paginator): Response
    {
        $produits = $produitRepository->findAll();
        $query = $produitRepository->findAll();
        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1), 5);
        return $this->render('produit/index.html.twig', [
            'produits' => $produits,
            'pagination' => $pagination
        ]);
    }

    #[Route("/mail", name: "app_testmail")]
    public function Mailtest(MailerInterface $mailer )
    {
        $email = (new Email())
            ->from('soucrafribogri@testMail.com')
            ->subject('new produit added')
            ->text('testMail');
        $email->to('Hamza.khalloufi@exemple.com');
        $mailer->send($email);
    }

    #[Route('stat/chart', name: 'app_chart', methods: ['GET'])]
    public function barChartAction(ProduitRepository $produitRepository): Response
    {
        $results = $produitRepository->chartRepository();
        $totalCount = array_reduce($results, function ($carry, $result) {
            return $carry + $result['nbrprod'];
        }, 0);

        foreach ($results as $result) {
            $percentage = round(($result['nbrprod']));
            $obj = new Pie();
            $obj->value = $result['categorie'];
            $obj->valeur = $percentage;
            $resultArray[] = $obj;
        }
        return $this->render('produit/chart.html.twig', array(
            'results' => $resultArray,
        ));
    }

    #[Route('/search', name: 'app_produit_search', methods: ['GET'])]
    public function search(Request $request, ProduitRepository $produitRepository, CategorieRepository $categorieRepository): Response
    {
        $categories = $categorieRepository->findAll();
        $search = $request->query->get('search');
        $produits = $produitRepository->findByNom($search);
        return $this->render('produit/index.html.twig', [
            'produits' => $produits,
            'categories' => $categories,
        ]);
    }

    #[Route('/new', name: 'app_produit_new', methods: ['GET', 'POST'])]
    public function new(MailerInterface $mailer, FlashyNotifier $flashy, Request $request, ProduitRepository $produitRepository, UtilisateurRepository $utilisateurRepository): Response
    {
        $produit = new Produit();
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();

            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $newFilename = $originalFilename . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('images_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // Handle file upload error
                }
                $produit->setImage($newFilename);
            }


            $flashy->success('Ajout avec succès !', 'http://your-awesome-link.com/%27');
            $produitRepository->save($produit, true);

            $users = $utilisateurRepository->findAll();
            $email = (new Email())
                ->from('soucrafribogri@testMail.com')
                ->subject('new produit added')
                ->html('<html><body><h1>We have a new product!</h1><p>Check it out now!</p></body></html>');
            foreach ($users as $user) {
                $email->addTo($user->getEmail());
            }
            $mailer->send($email);

            return $this->redirectToRoute('app_produit_index', [], Response::HTTP_SEE_OTHER);
        }
        return $this->renderForm('produit/new.html.twig', [
            'produit' => $produit,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_produit_show', methods: ['GET'])]
    public function show(Produit $produit, BuilderInterface $customQrCodeBuilder): Response
    {
        $text = sprintf(
            'Nom: %s |Prix: %s | Type: %s | Categorie: %s | Ticket: %s',
            $produit->getNom(),
            $produit->getPrix(),
            $produit->getDescription(),
            $produit->getCategorie(),
            $produit->getId()
        );

        $result = $customQrCodeBuilder
            ->size(100)
            ->margin(20)
            ->data($text)
            ->build();

        $base64 = $result->getDataUri();

        return $this->render('produit/show.html.twig', [
            'produit' => $produit,
            'qrcode' => $base64
        ]);
    }

    #[Route('/{id}/edit', name: 'app_produit_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Produit $produit, FlashyNotifier $flashy, ProduitRepository $produitRepository): Response
    {
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $produitRepository->save($produit, true);
            $flashy->success('Mofidication avec succès !', 'http://your-awesome-link.com/%27');
            return $this->redirectToRoute('app_produit_index', [], Response::HTTP_SEE_OTHER);
        }
        return $this->renderForm('produit/edit.html.twig', [
            'produit' => $produit,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_produit_delete', methods: ['POST'])]
    public function delete(Request $request, Produit $produit, FlashyNotifier $flashy, ProduitRepository $produitRepository): Response
    {
        if ($this->isCsrfTokenValid('delete' . $produit->getId(), $request->request->get('_token'))) {
            $produitRepository->remove($produit, true);
        }
        $flashy->success('Produit supprimé !', 'http://your-awesome-link.com/%27');
        return $this->redirectToRoute('app_produit_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route("/getall/produitsJson", name: "produit_alllist")]
    public function getProduct(ProduitRepository $produitRepository, NormalizerInterface $normalizer): Response
    {
        $prod = $produitRepository->findAll();
        $json = $normalizer->normalize($prod, 'json', ['groups' => "produits"]);
        $json = json_encode($json);
        return new Response($json);
    }


    #[Route("/produitsJson/{id}", name: "produit_show", methods: ['GET'])]
    public function getProduit($id, NormalizerInterface $normalizer, ProduitRepository $repo)
    {
        $produit = $repo->find($id);
        $produitNormalises = $normalizer->normalize($produit, 'json', ['groups' => "produits"]);
        return new Response(json_encode($produitNormalises));
    }

    #[Route("/produitJson/new", name: "produit_create")]
    public function JsonNew(Request $request, ProduitRepository $produitRepository, CategorieRepository $categorieRepository): Response
    {
        $nom = $request->query->get('nom');
        $description = $request->query->get('description');
        $prix = $request->query->get('prix');
        $status = $request->query->get('status');
        $image = $request->query->get('image');
        $categorie_id = $request->query->get('categorie_id');

        $entityManager = $this->getDoctrine()->getManager();

        $produit = new Produit();
        $produit->setNom($nom);
        $produit->setDescription($description);
        $produit->setPrix($prix);
        $produit->setStatus($status);
        $produit->setImage($image);

        $categorie = $categorieRepository->find($categorie_id);

        if (!$categorie) {
            throw $this->createNotFoundException('Catégorie introuvable pour id ' . $categorie_id);
        }

        $produit->setCategorie($categorie);

        $entityManager->persist($produit);
        $entityManager->flush();

        return new Response(
            'Produit est a jour avec succes');
    }


    // Update an existing produit
    #[Route("/produitJsonUpdate/{id}", name: "produit_update", methods: ['GET'])]
    public function updateProduit(Request $request, $id, NormalizerInterface $normalizer, CategorieRepository $categorieRepository)
    {
        $nom = $request->query->get('nom');
        $image = $request->query->get('image');
        $categorie_id = $request->query->get('categorie_id');
        $prix = $request->query->get('prix');
        $status = $request->query->get('status');
        $description = $request->query->get('description');

        $entityManager = $this->getDoctrine()->getManager();

        $produit = $entityManager->getRepository(Produit::class)->find($id);

        if (!$produit) {
            throw $this->createNotFoundException('Produit introuvable pour id ' . $id);
        }

        $produit->setNom($nom);
        $produit->setImage($image);
        $produit->setPrix($prix);
        $produit->setStatus($status);
        $produit->setDescription($description);

        $categorie = $categorieRepository->find($categorie_id);

        if (!$categorie) {
            throw $this->createNotFoundException('Catégorie introuvable pour id ' . $categorie_id);
        }

        $produit->setCategorie($categorie);

        $entityManager->flush();

        return new Response(
            'Produit est a jour avec succes');
    }

    #[Route("/produitJsonDelete/{id}", name: "produit_delete", methods: ["GET"])]
    public function deleteProduit(Request $req, $id, NormalizerInterface $normalizer, ProduitRepository $produitRepository)
    {
        $em = $this->getDoctrine()->getManager();
        $produit = $produitRepository->find($id);

        if (!$produit) {
            throw $this->createNotFoundException('Produit not found for id ' . $id);
        }

        $em->remove($produit);
        $em->flush();

        $jsonContent = $normalizer->normalize($produit, 'json', ['groups' => 'produits']);
        return new Response(json_encode($jsonContent));
    }
///////////////////////////////////////
}



























