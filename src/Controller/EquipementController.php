<?php

namespace App\Controller;

use App\Entity\dto\Pie;
use App\Entity\Equipement;
use App\Form\EquipementType;
use App\Repository\EquipementRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Serializer;
use CMEN\GoogleChartsBundle\GoogleCharts\Charts\PieChart;
use Dompdf\Dompdf;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Employe;
use App\Repository\RatingRespository;
use App\Entity\Rating;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

#[Route('/equipement')]
class EquipementController extends AbstractController
{
   
    #[Route('/', name: 'app_equipement_index')]
    public function pag(Request $request, EquipementRepository $equipementRepository, PaginatorInterface $paginator): Response
    {
        $query = $equipementRepository->createQueryBuilder('e') // Use query builder to create a query
            ->orderBy('e.employe', 'DESC') // Sort by ID in descending order
            ->getQuery(); // Get the query object
        
            $equipements = $paginator->paginate(
                $query,
                $request->query->getInt('page', 1),
                2
            );
    
            return $this->render('equipement/index.html.twig', [
                'equipement' => $equipements,
            ]);
    }
    

    #[Route('/generate-pdf', name: 'app_equipement_generate_pdf')]
    public function generatePdf(EquipementRepository $equipementRepository): Response
    {
        // Fetch the equipements from the repository
        $equipements = $equipementRepository->findAll();
    
        // Render the equipements into HTML using a template
        $html = $this->renderView('equipement/pdf.html.twig', [
            'equipements' => $equipements,
        ]);
    
        // Use Dompdf to generate a PDF from the HTML
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
    
        // Output the generated PDF to the browser
        $response = new Response($dompdf->output());
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', 'attachment;filename="equipements.pdf"');
    
        return $response;
    }


    #[Route('/new', name: 'app_equipement_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EquipementRepository $EquipementRepository): Response
    {
        $equipement = new Equipement();
        $form = $this->createForm(EquipementType::class, $equipement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $EquipementRepository->save($equipement, true);

            return $this->redirectToRoute('app_equipement_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('equipement/new.html.twig', [
            'equipement' => $equipement,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_equipement_show', methods: ['GET'])]
    public function show(Equipement $equipement): Response
    { if (!$equipement) {
        throw new NotFoundHttpException('Equipement not found');
    }
        return $this->render('equipement/show.html.twig', [
            'equipement' => $equipement,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_equipement_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Equipement $equipement, EquipementRepository $EquipementRepository): Response
    {
        $form = $this->createForm(EquipementType::class, $equipement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $EquipementRepository->save($equipement, true);

            return $this->redirectToRoute('app_equipement_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('equipement/edit.html.twig', [
            'equipement' => $equipement,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_equipement_delete', methods: ['POST'])]
    public function delete(Request $request, Equipement $equipement, EquipementRepository $EquipementRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$equipement->getId(), $request->request->get('_token'))) {
            $EquipementRepository->remove($equipement, true);
        }

        return $this->redirectToRoute('app_equipement_index', [], Response::HTTP_SEE_OTHER);
    }
  
 
    #[Route('/equipements/stat', name: 'app_equipement_stats', methods: ['GET'])]
public function stat(EquipementRepository $equipementRepository): Response
{
    $equipements = $equipementRepository->countByState();
    $equipementCounts = [];

    foreach ($equipements as $equipement) {
        if (isset($equipement['etat'])) { // Check if 'etat' property exists
            $equipementState = $equipement['etat'];
            if (!array_key_exists($equipementState, $equipementCounts)) {
                $equipementCounts[$equipementState] = 0;
            }
            $equipementCounts[$equipementState]++;
        }
    }

    return $this->render('equipement/stats.html.twig', [
        'equipementCounts' => $equipementCounts,
    ]);
}

// pour le mobile 

#[Route('/mobile/all', name: 'app_equipement_show', methods: ['POST'])]
public function index(EquipementRepository $equipementRepository): JsonResponse
{
    $equipements = $equipementRepository->findAll();
    $data = [];

    foreach ($equipements as $equipement) {
        $employeId = null;
        $employe = $equipement->getEmploye();
        if ($employe !== null) {
            $employeId = $employe->getId();
        }

        $data[] = [
            'id' => $equipement->getId(),
            'type' => $equipement->getType(),
            'marque' => $equipement->getMarque(),
            'disponnible' => $equipement->isDisponnible(),
            'etat' => $equipement->getEtat(),
            'matricule' => $equipement->getMatricule(),
            'employe_id' => $employeId,
        ];
    }

    return new JsonResponse($data);
}

#[Route('/mobile/add', name: 'app_equipement_create', methods: ['POST'])]
public function create(Request $request, EntityManagerInterface $entityManager): JsonResponse
{
    $type = $request->get('type');
    $marque = $request->get('marque');
    $disponnible = $request->get('disponnible');
    $etat = $request->get('etat');
    $matricule = $request->get('matricule');
    $employeId = $request->get('employe_id');

    if (!$type || !$marque || !$disponnible || !$etat || !$matricule) {
        return new JsonResponse(['error' => 'All fields are required'], 400);
    }

    $equipement = new Equipement();
    $equipement->setType($type)
        ->setMarque($marque)
        ->setDisponnible($disponnible)
        ->setEtat($etat)
        ->setMatricule($matricule);

    if ($employeId) {
        $employe = $entityManager->getRepository(Employe::class)->find($employeId);
        if (!$employe) {
            return new JsonResponse(['error' => 'Employee not found'], 404);
        }
        $equipement->setEmploye($employe);
    }

    $entityManager->persist($equipement);
    $entityManager->flush();

    return new JsonResponse(['id' => $equipement->getId()], 201);
}

#[Route('/mobile/edit/{id}', name: 'app_equipement_edit', methods: ['POST'])]
public function editmobile(Request $request, Equipement $equipement): Response
{
    if (!$equipement) {
        throw new NotFoundHttpException('Equipement not found');
    }

    $data = json_decode($request->getContent(), true);
    $entityManager = $this->getDoctrine()->getManager();

    if (isset($data['type'])) {
        $equipement->setType($data['type']);
    }

    if (isset($data['marque'])) {
        $equipement->setMarque($data['marque']);
    }

    if (isset($data['disponnible'])) {
        $equipement->setDisponnible($data['disponnible']);
    }

    if (isset($data['etat'])) {
        $equipement->setEtat($data['etat']);
    }

    if (isset($data['matricule'])) {
        $equipement->setMatricule($data['matricule']);
    }

    $entityManager->persist($equipement);
    $entityManager->flush();

    return new JsonResponse(['message' => 'Equipement updated successfully']);
}

#[Route('/mobile/delete/{id}', name: 'app_equipement_delete', methods: ['DELETE'])]
public function deletemobile(Request $request, Equipement $equipement): Response
{
    if (!$equipement) {
        throw new NotFoundHttpException('Equipement not found');
    }

    $entityManager = $this->getDoctrine()->getManager();
    $entityManager->remove($equipement);
    $entityManager->flush();

    return new JsonResponse(['message' => 'Equipement deleted successfully']);
}

#[Route('/mobile/employes-without-equipement', name: 'app_employes_without_equipement', methods: ['POST'])]
public function employesWithoutEquipement(EntityManagerInterface $entityManager): Response
{
    $entityManager = $this->getDoctrine()->getManager();

    $subquery = $entityManager->createQueryBuilder()
        ->select('IDENTITY(eq.employe)')
        ->from('App\Entity\Equipement', 'eq')
        ->getDQL();

    $query = $entityManager->createQueryBuilder()
        ->select('e.id')
        ->from('App\Entity\Employe', 'e')
        ->where("e.id NOT IN ($subquery)")
        ->getQuery();

    $employeIds = $query->getResult();

    return $this->json($employeIds);}

    #[Route('/check_rating', name: 'mobile_check_rating')]
    public function checkRating(Request $request, RatingRespository $a, EntityManagerInterface $entityManager): Response
    {
        $iduser = $request->query->get('iduser');
    
        $existingRatings = $a->findBy([
            'iduser' => $iduser,
        ]);
        
        if (!empty($existingRatings)) {
            $serializer = new Serializer([new ObjectNormalizer()]);
            $formatted = [];
        
            foreach ($existingRatings as $rating) {
                $formatted[] = $serializer->normalize([
                    'idequipement' => $rating->getIdequipement(),
                    'rating' => $rating->getRating(),
                    'iduser' => $iduser,
                ]);
            }
        
            return new JsonResponse($formatted);
        } else {
            return new JsonResponse([]);
        }
    }
    
    #[Route('/updaterating', name: 'mobile_updaterating')]
    public function updaterating(Request $request, RatingRespository $a,EntityManagerInterface $entityManager): Response
    {          
    $Idequipement = $request->query->get('idequipement');
    $iduser = $request->query->get('iduser');
    $ratingValue = $request->query->get('rating');
    
    $existingRating = $a->findOneBy([
        'idequipement' => $Idequipement,
        'iduser' => $iduser,
    ]);
    
    if ($existingRating !== null) {
        // Update existing rating
        $existingRating->setRating($ratingValue);
    
        $entityManager->persist($existingRating);
        $entityManager->flush();
    } else {
        // Create new rating
        $rating = new Rating();
    
        $rating->setIdequipement($Idequipement);
        $rating->setIduser($iduser);
        $rating->setRating($ratingValue);
    
        $entityManager->persist($rating);
        $entityManager->flush();
    }
    
    $serializer = new Serializer([new ObjectNormalizer()]);
    $formatted = $serializer->normalize("rating");
    
    return new JsonResponse($formatted);  
    } 
}

