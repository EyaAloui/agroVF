<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\UtilisateurType;
use App\Repository\UtilisateurRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\HttpFoundation\JsonResponse;

#[Route('/utilisateur')]
class UtilisateurController extends AbstractController
{
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    
    {
    
    $this->passwordEncoder = $passwordEncoder;
    
    }


    #[Route('/', name: 'app_utilisateur_index', methods: ['GET'])]
    public function index(UtilisateurRepository $utilisateurRepository): Response
    {
        return $this->render('utilisateur/index.html.twig', [
            'utilisateurs' => $utilisateurRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_utilisateur_new', methods: ['GET', 'POST'])]
    public function new(Request $request, UtilisateurRepository $utilisateurRepository): Response
    {
        $utilisateur = new Utilisateur();
        $form = $this->createForm(UtilisateurType::class, $utilisateur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $utilisateurRepository->save($utilisateur, true);
        

            return $this->redirectToRoute('app_utilisateur_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('utilisateur/new.html.twig', [
            'utilisateur' => $utilisateur,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_utilisateur_show', methods: ['GET'])]
    public function show(Utilisateur $utilisateur): Response
    {
        return $this->render('utilisateur/show.html.twig', [
            'utilisateur' => $utilisateur,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_utilisateur_edit')]
    public function edit(Request $request, Utilisateur $utilisateur, UtilisateurRepository $utilisateurRepository): Response
    {
        $form = $this->createForm(UtilisateurType::class, $utilisateur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $utilisateurRepository->save($utilisateur, true);
            return $this->redirectToRoute('app_utilisateur_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('utilisateur/edit.html.twig', [
            'utilisateur' => $utilisateur,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_utilisateur_delete', methods: ['POST'])]
    public function delete(Request $request, Utilisateur $utilisateur, UtilisateurRepository $utilisateurRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$utilisateur->getId(), $request->request->get('_token'))) {
            $utilisateurRepository->remove($utilisateur, true);
        }

        return $this->redirectToRoute('app_utilisateur_index', [], Response::HTTP_SEE_OTHER);
    }

/////pour mobile
    
  ///delete user
#[Route('/mobile/deletedisUser', name: 'app_deleteUser', methods: ['POST'])]
public function deleteUser(Request $request): Response
{
    $user = new Utilisateur();
    $id = $request->query->get("id");
    $rep = $this->getDoctrine()->getRepository(Utilisateur::class);
    $em = $this->getDoctrine()->getManager();
    $user = $rep->find($id);
    $em->remove($user);
    $em->flush();
    $serializer = new Serializer([new ObjectNormalizer()]);
$formatted = $serializer->normalize("user got deleted");
return new JsonResponse($formatted);
}
    /// ajout user pour mobile
    #[Route('/mobile/adduser', name: 'app_adduser', methods: ['POST','GET'])]
    public function adduser(Request $request, UserPasswordEncoderInterface $userPasswordEncoder)
    {
       $user = new Utilisateur();
        $nom = $request->query->get("nom");
        $email = $request->query->get("email");
        $password = $request->query->get("password");
        $prenom = $request->query->get("prenom");
        $telephone = $request->query->get("telephone");
        $cin = $request->query->get("cin");

        $em = $this->getDoctrine()->getManager();
        $Checkuser = $this->getDoctrine()->getRepository(Utilisateur::class)->findOneBy(['email' => $email]);

        // Check if the user exists to prevent Integrity constraint violation error in the insertion
        if ($Checkuser){
          $serializer = new Serializer([new ObjectNormalizer()]);
          $formatted = $serializer->normalize("email exists");
          return new JsonResponse($formatted);
        }else{
      $roles[] = 'ROLE_USER';
          $user->setRoles($roles);
        $user->setEmail($email);
        $user->setPassword(md5($password));
        $user->setNom($nom);
        $user->setPrenom($prenom);
        $user->setTelephone($telephone);
        $user->setCin($cin);
        $user->setIsVerified(true);
        $em->persist($user);
        $em->flush();
        $serializer = new Serializer([new ObjectNormalizer()]);
        $formatted = $serializer->normalize("User ajouter");
        return new JsonResponse($formatted);
        
      }
    }
    // login pour user
    #[Route('/mobile/loginAction', name: 'loginAction', methods: ['POST','GET'])]
    public function loginAction(Request $request, UserPasswordEncoderInterface $userPasswordEncoder){


   
        $user = new Utilisateur();
        $email = $request->query->get("email");
        $password = $request->query->get("password");
        $hash_pass = md5($password);
 
        
        $Checkuser = $this->getDoctrine()->getRepository(Utilisateur::class)->findOneBy(['email' => $email, 'password' => $hash_pass ,'isVerified' => true]);
        
     
        $normalizer = new ObjectNormalizer ();
        $circularReferenceHandler = function ($Checkuser) {
            return $Checkuser -> getId ();
        };
            $serializer = new Serializer([ $normalizer ]);
            $formatted = $serializer->normalize($Checkuser , null , [ ObjectNormalizer::ATTRIBUTES => 
            ['id','email','prenom','nom','roles','telephone','cin']]);
            return new JsonResponse(
                $formatted
         
            );    
}

// update user
#[Route('/mobile/updateUser', name: 'updateUser', methods: ['POST','GET'])]
public function updateUser(Request $request)
{
    $user = new Utilisateur();
    $id = $request->query->get("id");
    $nom = $request->query->get("nom");
    $email = $request->query->get("email");
    $password = $request->query->get("password");
    $prenom = $request->query->get("prenom");
    $telephone = $request->query->get("telephone");
    $cin = $request->query->get("cin");

    
    $rep = $this->getDoctrine()->getManager();
    $Checkuser = $this->getDoctrine()->getRepository(Utilisateur::class)->findOneBy(['email' => $email]);

    // Check if the user exists to prevent Integrity constraint violation error in the insertion

  
      $user = $rep->getRepository(Utilisateur::class)->find($id);

      $user->setEmail($email);
      $user->setPassword(md5($password));
      $user->setNom($nom);
      $user->setPrenom($prenom);
      $user->setTelephone($telephone);
      $user->setCin($cin);
      $user->setIsVerified(true);
    $rep->flush();
    $serializer = new Serializer([new ObjectNormalizer()]);
    $formatted = $serializer->normalize("User mofifier");
    return new JsonResponse($formatted);
    
  }

  //forget password
  #[Route('/mobile/updatepassword', name: 'updatepassword', methods: ['POST','GET'])]
  public function updatepassword(Request $request)
  {
    $user = new Utilisateur();
      $email = $request->query->get("email");
      $password = $request->query->get("password");


      
      $rep = $this->getDoctrine()->getManager();    
      $user = $this->getDoctrine()->getRepository(Utilisateur::class)->findOneBy(['email' => $email]);
      $user->setPassword(md5($password));
      $rep->flush();
      $serializer = new Serializer([new ObjectNormalizer()]);
      $formatted = $serializer->normalize("Mot de passe a ete changer");
      return new JsonResponse($formatted);
      
    }


    #[Route('/mobile/checkemail', name: 'checkemail', methods: ['POST','GET'])]
  public function checkemail(Request $request)
  {
    $user = new Utilisateur();
      $email = $request->query->get("email");
      
      $rep = $this->getDoctrine()->getManager();    
      $user = $this->getDoctrine()->getRepository(Utilisateur::class)->findOneBy(['email' => $email]);
if($user){
$serializer = new Serializer([new ObjectNormalizer()]);
$formatted = $serializer->normalize("email exist");
return new JsonResponse($formatted);

}
  }
}
