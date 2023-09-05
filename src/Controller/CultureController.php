<?php

namespace App\Controller;


use Dompdf\Dompdf;
use App\Entity\Culture;
use App\Form\CultureType;
use App\Repository\CultureRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


#[Route('/cultures')]
class CultureController extends AbstractController
{

    #[Route("/culture/calendar", name: "culture_calendar")]
    public function calendar(): JsonResponse
    {
        // retrieve all the events related to the Culture entity
        $cultures = $this->getDoctrine()->getRepository(Culture::class)->findAll();

        // format the events data in the required format by FullCalendar, which is JSON
        $events = [];
        foreach ($cultures as $culture) {
            $events[] = [
                'title' => $culture->getType(),
                'start' => $culture->getDatePlanting()->format('Y-m-d'),
            ];
        }

        // send the events data in JSON format
        return $this->json($events);
    }
    #[Route('/generate-pdf', name: 'app_culture_generate_pdf')]
    public function generatePdf(CultureRepository $cultureRepository): Response
    {
        // Fetch the cultures from the repository
        $cultures = $cultureRepository->findAll();

        // Render the cultures into HTML using a template
        $html = $this->renderView('culture/pdf.html.twig', [
            'cultures' => $cultures,
        ]);

        // Use Dompdf to generate a PDF from the HTML
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Output the generated PDF to the browser
        $response = new Response($dompdf->output());
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', 'attachment;filename="cultures.pdf"');

        return $response;
    }

    #[Route('/all', name: 'app_culture_index')]

    public function pag(Request $request, CultureRepository $cultureRepository, PaginatorInterface $paginator): Response
    {
        $query = $cultureRepository->createQueryBuilder('c') // Use query builder to create a query
        ->orderBy('c.type', 'DESC') // Sort by ID in descending order
        ->getQuery(); // Get the query object

        $cultures = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            1
        );
        return $this->render('culture/pg.html.twig', [
            'culture' => $cultures,
        ]);
    }

    #[Route('/stats', name: 'app_culture_stats', methods: ['GET'])]
    public function stat(CultureRepository $cultureRepository): Response
    {
        $cultures = $cultureRepository->findAll();
        $cultureCounts = [];

        foreach ($cultures as $culture) {
            $cultureName = $culture->getType();
            if (!array_key_exists($cultureName, $cultureCounts)) {
                $cultureCounts[$cultureName] = 0;
            }
            $cultureCounts[$cultureName]++;
        }

        return $this->render('culture/stats.html.twig', [
            'cultureCounts' => $cultureCounts,
        ]);
    }

    #[Route('/new', name: 'app_culture_new', methods: ['GET', 'POST'])]
    public function new(Request $request, CultureRepository $cultureRepository): Response
    {
        $culture = new Culture();
        $form = $this->createForm(CultureType::class, $culture);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            $cultureRepository->save($culture, true);


            return $this->redirectToRoute('app_culture_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('culture/new.html.twig', [
            'culture' => $culture,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_culture_show', methods: ['GET'])]
    public function show(Culture $culture): Response
    {
        return $this->render('culture/show.html.twig', [
            'culture' => $culture,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_culture_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Culture $culture, CultureRepository $cultureRepository): Response
    {
        $form = $this->createForm(CultureType::class, $culture);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $cultureRepository->save($culture, true);



            return $this->redirectToRoute('app_culture_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('culture/edit.html.twig', [
            'culture' => $culture,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_culture_delete', methods: ['POST'])]
    public function delete(Request $request, Culture $culture,CultureRepository $cultureRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$culture->getId(), $request->request->get('_token'))) {
            $relatedEntities = $culture->getTerrain();
            if ($relatedEntities->count() > 0) {
                return new Response('Cette culture ne peut pas être supprimée car elle est liée à un ou plusieurs terrains');
            }
            $cultureRepository->remove($culture, true);

        }
        return $this->redirectToRoute('app_culture_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route("/liste", name: "liste")]
    public function getCulture(CultureRepository $cultureRepository,SerializerInterface $serializer)
    {

        $culture=$cultureRepository->findAll();
        $json =$serializer->serialize($culture, 'json', ['groups'=>"culture"]);
        return new Response($json);
    }
    #[Route("addculture/new", name: "addculture")]
    public function addCulture(Request $req,NormalizerInterface $Normalizer)
    {

        $em = $this->getDoctrine()->getManager();
        $culture = new culture();
        $culture->setDatePlanting($req->get('date'));
        $culture->setType($req->get('type'));
        $em->persist($culture);
        $em->flush();

        $jsonContent = $Normalizer->normalize($culture, 'json', ['groups' => '$culture']);
        return new Response(json_encode($jsonContent));
    }
    #[Route("/showculture/{id}", name: "showculture")]
    public function showculture($id, NormalizerInterface $normalizer, CultureRepository $cultureRepository)
    {
        $culture = $cultureRepository->find($id);

        $cultureNormalise = $normalizer->normalize($culture, 'json', ['groups' => "culture"]);
        return new Response(json_encode($cultureNormalise));
    }
    #[Route("/deleteculture/{id}", name: "deleteculture")]
    public function deleteculture(Request $req, $id, NormalizerInterface $Normalizer)
    {
        $em = $this->getDoctrine()->getManager();
        $culture = $em->getRepository(culture::class)->find($id);
        $em->remove($culture);
        $em->flush();
        $jsonContent = $Normalizer->normalize($culture, 'json', ['groups' => 'culture']);
        return new Response( json_encode($jsonContent));
    }
}
