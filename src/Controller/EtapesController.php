<?php

namespace App\Controller;

use App\Form\AddEtapesType;
use App\Entity\Etapes;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class EtapesController extends AbstractController
{
    #[Route('/etapes', name: 'app_etapes')]
    public function index(): Response
    {
        return $this->render('etapes/index.html.twig', [
            'controller_name' => 'EtapesController',
        ]);
    }
    #[Route('/etapes/add', name: 'app_etapes_add')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
       
        $etapes = new Etapes();

        
        $form = $this->createForm(AddEtapesType::class, $etapes);
        $form->handleRequest($request);

        // Vérifier si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            // Enregistrer la recette dans la base de données
            $em->persist($etapes);
            $em->flush();

            // Rediriger vers la liste des recettes
            return $this->redirectToRoute('app_etapes');
        }

        // Afficher le formulaire
        return $this->render('etapes/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
