<?php

namespace App\Controller;

use App\Entity\dto\Pie;
use App\Entity\Terrain;
use App\Form\TerrainType;
use App\Repository\TerrainRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Culture;
use App\Repository\CultureRepository;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

#[Route('/terrain')]
class TerrainController extends AbstractController
{
    #[Route('/', name: 'app_terrain_index', methods: ['GET'])]
    public function index(TerrainRepository $terrainRepository): Response
    {
        return $this->render('terrain/index.html.twig', [
            'terrains' => $terrainRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_terrain_new', methods: ['GET', 'POST'])]
    public function new(Request $request, TerrainRepository $terrainRepository): Response
    {
        $terrain = new Terrain();
        $form = $this->createForm(TerrainType::class, $terrain);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $terrainRepository->save($terrain, true);

            return $this->redirectToRoute('app_terrain_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('terrain/new.html.twig', [
            'terrain' => $terrain,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_terrain_show', methods: ['GET'])]
    public function show(Terrain $terrain): Response
    {
        return $this->render('terrain/show.html.twig', [
            'terrain' => $terrain,
        ]);
    }

    #[Route('/stats', name: 'app_chart', methods: ['GET'])]
    public function barChartAction( TerrainRepository $terrainRepository  ): Response
    {
        $results= $terrainRepository->charteRepository();
        $totalCount = array_reduce($results, function($carry, $result) {
            return $carry + $result['nbrterrain'];
        }, 0);

        foreach ($results as $result) {
            $percentage = round(($result['nbrterrain']  ));
            $obj = new Pie();
            $obj->value = $result['culture'];
            $obj->valeur = $percentage ;
            $resultArray[] = $obj;
        }


        return $this->render('terrain/stats.html.twig', array(
            'results'  =>  $resultArray,


        ));
    }

    #[Route('/{id}/edit', name: 'app_terrain_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Terrain $terrain, TerrainRepository $terrainRepository): Response
    {
        $form = $this->createForm(TerrainType::class, $terrain);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $terrainRepository->save($terrain, true);

            return $this->redirectToRoute('app_terrain_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('terrain/edit.html.twig', [
            'terrain' => $terrain,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_terrain_delete', methods: ['POST'])]
    public function delete(Request $request, Terrain $terrain, TerrainRepository $terrainRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$terrain->getId(), $request->request->get('_token'))) {
            $terrainRepository->remove($terrain, true);
        }

        return $this->redirectToRoute('app_terrain_index', [], Response::HTTP_SEE_OTHER);
    }

//pour mobile 

    #[Route('/mobile/all', name: 'app_terrain_index', methods: ['POST'])]
public function mobileSHOW(TerrainRepository $terrainRepository, SerializerInterface $serializer): JsonResponse
{
    
    $terrains = $terrainRepository->findAll();
    $terrainsData = array_map(function($terrain) {
    $culture = $terrain->getCulture();

        return [
            'id' => $terrain->getId(),
            'numero' => $terrain->getNumero(),
            'surface' => $terrain->getSurface(),
            'lieu'=> $terrain->getLieu(),
            'culture' => [
                'id' => $culture->getId(),
                'type' => $culture->getType(),
                'dateculture' => $culture->getDatePlanting()->format('Y-m-d'),
                'quantie' => $culture->getQuantite(),
            ],
        ];
    }, $terrains);

    return new JsonResponse($terrainsData);

}

#[Route('/mobile/add', name: 'app_terrain_add', methods: ['POST'])]
public function mobileAdd(Request $request, EntityManagerInterface $entityManager): JsonResponse
{
    $numero = $request->get('numero');
    $surface = $request->get('surface');
    $lieu = $request->get('lieu');
    $cultureId = $request->get('culture_id');

    $culture = $entityManager->getRepository(Culture::class)->find($cultureId);

    if (!$culture) {
        throw new NotFoundHttpException('Culture not found');
    }

    $terrain = new Terrain();
    $terrain->setNumero($numero);
    $terrain->setSurface($surface);
    $terrain->setLieu($lieu);
    $terrain->setCulture($culture);

    $entityManager->persist($terrain);
    $entityManager->flush();

    return new JsonResponse(['status' => 'Terrain created']);
}

#[Route('/mobile/edit/{id}', name: 'app_terrain_update', methods: ['POST'])]
public function mobileUpdate(Request $request, Terrain $terrain, EntityManagerInterface $entityManager, SerializerInterface $serializer): JsonResponse
{
    $numero = $request->get('numero');
    $surface = $request->get('surface');
    $lieu = $request->get('lieu');
    $cultureId = $request->get('culture_id');
    
    $culture = $entityManager->getRepository(Culture::class)->find($cultureId);
    if (!$culture) {
        return new JsonResponse(['message' => 'Culture not found'], Response::HTTP_NOT_FOUND);
    }

    $terrain->setNumero($numero);
    $terrain->setSurface($surface);
    $terrain->setLieu($lieu);
    $terrain->setCulture($culture);

    $entityManager->flush();

    return new JsonResponse(['status' => 'Terrain updated']);
}

#[Route('/mobile/delete/{id}', name: 'app_terrain_delete', methods: ['DELETE'])]
public function mobileDelete(Terrain $terrain, EntityManagerInterface $entityManager): JsonResponse
{
    $entityManager->remove($terrain);
    $entityManager->flush();

    return new JsonResponse(['message' => 'Terrain deleted successfully']);
}

#[Route('/mobile/allCulture', name: 'app_culture_index', methods: ['POST'])]
public function getCulture(): JsonResponse
{
    $cultures = $this->getDoctrine()->getRepository(Culture::class)->findAll();

    $culturesArray = array_map(function (Culture $culture) {
        return [
            'id' => $culture->getId(),
            'type' => $culture->getType(),
            'dateculture' => $culture->getDatePlanting()->format('Y-m-d'),
            'quantie' => $culture->getQuantite(),
        ];
    }, $cultures);

    return $this->json($culturesArray, Response::HTTP_OK);
}
#[Route('/mobile/email', name: 'app_email_mobile', methods: ['POST'])]
public function email(MailerInterface $mailer, TerrainRepository $terrainRepository): JsonResponse
{
    $terrain  = $terrainRepository->findOneBy([], ['id' => 'desc']); // get the last added terrain
    $culture = $terrain->getCulture();
    $email = (new Email())
    ->from(new Address("mohamed.aouadi@esprit.tn"))                
    ->to(new Address("mohamed.aouadi@esprit.tn"))
    ->subject("New terrain added")
    ->html('
        <center><h1>Hello Admin,</h1></center>
        <p>A new terrain has been added with the following details:</p>
        <ul>
            <li>ID: '.$terrain->getId().'</li>
            <li>Number: '.$terrain->getNumero().'</li>
            <li>Surface: '.$terrain->getSurface().'</li>
            <li>Location: '.$terrain->getLieu().'</li>
            <li>Culture Type: '.$culture->getType().'</li>
            <li>Culture Date: '.$culture->getDatePlanting()->format('Y-m-d').'</li>
            <li>Culture Quantity: '.$culture->getQuantite().'</li>
        </ul>
    ');


    $mailer->send($email);

    return new JsonResponse(['terrain' => $terrain]);
}
}