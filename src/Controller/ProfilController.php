<?php

namespace App\Controller;

use App\Repository\FavorisRepository;
use App\Repository\RecetteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProfilController extends AbstractController
{
    #[Route('/profil', name: 'app_profil')]
    public function index(RecetteRepository $rr, FavorisRepository $fr): Response
    {
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour visualiser votre profil.');
        }
        
        // Récupérer les recettes ajoutées par l'utilisateur
        $recettesAjoutees = $rr->findBy(['idUser' => $user]);
        
        // Récupérer les favoris de l'utilisateur
        $favoris = $fr->findBy(['idUser' => $user]);
        
        return $this->render('profil/index.html.twig', [
            'user' => $user,
            'recettesAjoutees' => $recettesAjoutees,
            'favoris' => $favoris,
        ]);
    }
}

