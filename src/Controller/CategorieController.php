<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Form\CategorieType;
use App\Repository\CategorieRepository;
use App\Repository\ProduitRepository;
use Knp\Component\Pager\PaginatorInterface;
use MercurySeries\FlashyBundle\FlashyNotifier;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Dompdf\Dompdf;

#[Route('/categorie')]
class CategorieController extends AbstractController
{
    #[Route('/all', name: 'app_categorie_index', methods: ['GET'])]
    public function index(CategorieRepository $categorieRepository, Request $request, PaginatorInterface $paginator): Response
    {
        $query = $categorieRepository->findAll();
        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1), 5);
        return $this->render('categorie/index.html.twig', [
            'categories' => $categorieRepository->findAll(),
            'pagination' => $pagination
        ]);
    }

    #[Route('/pdf/', name: 'app_categorie_pdf')]
    public function generatePdfAction(CategorieRepository $categorieRepository, ProduitRepository $produitRepository,FlashyNotifier $flashy)
    {
        $categories = $categorieRepository->findAll();
        $produits = $produitRepository->findAll([]);
        $html = $this->renderView('categorie/pdf.html.twig', [
            'categories' => $categories,
            'produits' => array_reduce($produits, function ($result, $produit) {
                $result[$produit->getCategorie()->getId()][] = $produit;
                return $result;
            }, []),
        ]);

        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $response = new Response($dompdf->output());
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', 'attachment;filename=categorie.pdf');
        $flashy->muted('Impression du pdf ... ', 'http://your-awesome-link.com/%27');
        return $response;
    }

    #[Route('/new', name: 'app_categorie_new', methods: ['GET', 'POST'])]
    public function new(Request $request, CategorieRepository $categorieRepository,FlashyNotifier $flashy): Response
    {
        $categorie = new Categorie();
        $form = $this->createForm(CategorieType::class, $categorie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $categorieRepository->save($categorie, true);

            $flashy->success('Ajout avec succes !!', 'http://your-awesome-link.com/%27');
            return $this->redirectToRoute('app_categorie_index', [], Response::HTTP_SEE_OTHER);
        }
        return $this->renderForm('categorie/new.html.twig', [
            'categorie' => $categorie,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_categorie_show', methods: ['GET'])]
    public function show(Categorie $categorie): Response
    {
        return $this->render('categorie/show.html.twig', [
            'categorie' => $categorie,
        ]);
    }


    #[Route('/{id}/edit', name: 'app_categorie_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Categorie $categorie, CategorieRepository $categorieRepository,FlashyNotifier $flashy): Response
    {
        $form = $this->createForm(CategorieType::class, $categorie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $categorieRepository->save($categorie, true);
            $flashy->success('Categorie est a jour !', 'http://your-awesome-link.com/%27');
            return $this->redirectToRoute('app_categorie_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('categorie/edit.html.twig', [
            'categorie' => $categorie,
            'form' => $form,
        ]);
    }

    #[Route('/categorie/search', name: 'app_categorie_search')]
    public function search(Request $request, CategorieRepository $categorieRepository): Response
    {
        $q = $request->query->get('q');

        $categories = $categorieRepository->findByLabel($q);

        return $this->render('categorie/index.html.twig', [
            'categories' => $categories,
        ]);
    }

    #[Route('/{id}', name: 'app_categorie_delete', methods: ['POST'])]
    public function delete(Request $request, Categorie $categorie, CategorieRepository $categorieRepository,FlashyNotifier $flashy): Response
    {
        if ($this->isCsrfTokenValid('delete' . $categorie->getId(), $request->request->get('_token'))) {

            $relatedEntities = $categorie->getProduits(); // Assuming "Produit" is the related entity
            if ($relatedEntities->count() > 0) {
                $flashy->error('Categorie est en relation avec des produits !', 'http://your-awesome-link.com/%27');
                return $this->redirectToRoute('app_categorie_index', [], Response::HTTP_SEE_OTHER);
            }
            $categorieRepository->remove($categorie, true);
        }
        $flashy->success('Categorie supprimée !', 'http://your-awesome-link.com/%27');
        return $this->redirectToRoute('app_categorie_index', [], Response::HTTP_SEE_OTHER);
    }

    //Json Services
    #[Route("/getall/categoriesJson", name: "categorie_list")]
    public function getCategories(CategorieRepository $categorieRepository, NormalizerInterface $normalizer): Response
    {
        $categories = $categorieRepository->findAll();
        $json = $normalizer->normalize($categories, 'json', ['groups' => "categories"]);
        $json = json_encode($json);
        return new Response($json);
    }

    #[Route("/Show/categorieShowGet/{id}", name: "categorie_show")]
    public function getCategorie($id, NormalizerInterface $normalizer, CategorieRepository $repo)
    {
        $categorie = $repo->find($id);
        $categorieNormalises = $normalizer->normalize($categorie, 'json', ['groups' => "categories"]);
        return new Response(json_encode($categorieNormalises));
    }

    #[Route("/add/Json", name: "categorie_create")]
    public function createCategorie(Request $req, NormalizerInterface $normalizer)
    {
        $em = $this->getDoctrine()->getManager();
        $categorie = new Categorie();
        $categorie->setLabel($req->get('label'));
        $em->persist($categorie);
        $em->flush();

        $jsonContent = $normalizer->normalize($categorie, 'json', ['groups' => 'categories']);
        return new Response('Ajout categorie avec succes');
    }

    #[Route("/updateJson/{id}", name: "categorie_update")]
    public function updateCategorie(Request $req, $id, NormalizerInterface $normalizer)
    {
        $em = $this->getDoctrine()->getManager();
        $categorie = $em->getRepository(Categorie::class)->find($id);
        if (!$categorie) {
            throw $this->createNotFoundException('Categorie not found for id ' . $id);
        }
        $categorie->setLabel($req->get('label'));
        $em->flush();

        $jsonContent = $normalizer->normalize($categorie, 'json', ['groups' => 'categories']);
        return new Response(json_encode($jsonContent));
    }

    #[Route("/deleteJson/{id}", name: "categorie_delete", methods: ["GET"])]
    public function deleteCategorie(Request $req, $id, NormalizerInterface $normalizer)
    {
        $em = $this->getDoctrine()->getManager();
        $categorie = $em->getRepository(Categorie::class)->find($id);
        if (!$categorie) {
            throw $this->createNotFoundException('Categorie not found for id ' . $id);
        }

        $em->remove($categorie);
        $em->flush();

        $jsonContent = $normalizer->normalize($categorie, 'json', ['groups' => 'categories']);
        return new Response(json_encode($jsonContent));
    }

    //////End
}
