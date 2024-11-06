<?php

namespace App\Controller;

use App\Entity\Commentaire;
use App\Entity\Favoris;
use App\Entity\Recette;
use App\Entity\User;
use App\Form\ProfilModifType;
use App\Repository\FavorisRepository;
use App\Repository\RecetteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

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

    #[Route('/profil/modif/{id}', name: 'app_profil_modif')]
    public function modif(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, User $user, $id): Response
    {
        // Vérifier si l'utilisateur est connecté
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour modifier votre profil.');
        }
        $form = $this->createForm(ProfilModifType::class, $user);
        $form->handleRequest($request);
        $url = $this->generateUrl('app_profil_modif', ['id' => $user->getUserIdentifier()]);
    
        if ($form->isSubmitted() && $form->isValid()) {
            if ($password = $form->get('plainPassword')->getData()) {
                $hashedPassword = $passwordHasher->hashPassword($user, $password);
                $user->setPassword($hashedPassword);
            }

            $entityManager->flush();

            $this->addFlash('success', 'Votre profil a été mis à jour avec succès');
            return $this->redirectToRoute('app_profil');
        }

        return $this->render('profil/modif.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/profil/suppr/{id}', name: 'app_profil_suppr', methods: ['GET', 'POST'])]
    public function supprProfil(Request $request, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage, $id): Response
    {
        // Vérifier si l'utilisateur est connecté
        $user = $this->getUser();
        
        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour supprimer votre profil.');
        }
    
        if ($request->isMethod('POST') && $this->isCsrfTokenValid('profil-suppr', $request->request->get('_token'))) {
            // Les recettes créées par l'utilisateur sont supprimées
            $recettes = $entityManager->getRepository(Recette::class)->findBy(['idUser' => $user]);
            foreach ($recettes as $recette) {
                $entityManager->remove($recette);
            }

            // Les favoris sont supprimés
            $favoris = $entityManager->getRepository(Favoris::class)->findBy(['idUser' => $user]);
            foreach ($favoris as $favori) {
                $entityManager->remove($favori);
            }

            // Les commentaires sont supprimés
            $commentaires = $entityManager->getRepository(Commentaire::class)->findBy(['idUser' => $user]);
            foreach ($commentaires as $commentaire) {
                $entityManager->remove($commentaire);
            }

            // Déconnexion de l'utilisateur
            $tokenStorage->setToken(null);
            $request->getSession()->invalidate();

            // Suppression de l'utilisateur
            $entityManager->remove($user);
            $entityManager->flush();
    
            $this->addFlash('success', 'Votre profil a été supprimé avec succès.');
    
            return $this->redirectToRoute('app_accueil');

            // Si la requête n'est pas POST ou si le token CSRF n'est pas valide
            if ($request->isMethod('POST')) {
                $this->addFlash('error', 'Une erreur est survenue lors de la suppression du profil.');
            }

            // Pour les requêtes GET ou en cas d'erreur
            return $this->render('profil/modif.html.twig', ['user' => $user]);
        }

    }
    
}