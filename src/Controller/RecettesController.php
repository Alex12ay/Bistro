<?php

namespace App\Controller;

use App\Entity\Commentaire;
use App\Entity\Recette;
use App\Form\AddRecettesType;
use App\Form\CommentaireType;
use App\Repository\CategorieRepository;
use App\Repository\CommentaireRepository;
use App\Repository\RecetteRepository;
use App\Repository\BudgetRepository;
use App\Repository\DifficulteRepository;
use App\Repository\IngredientRepository;
use App\Repository\SaisonRepository;
use App\Repository\UstensileRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class RecettesController extends AbstractController
{
    #[Route('/recettes', name: 'app_recettes')]

    public function index(RecetteRepository $rr, CommentaireRepository $cor, CategorieRepository $cr,IngredientRepository $ing, SaisonRepository $sr, BudgetRepository $br): Response
    { 
        $recettes = $rr->findAll();
        $averageNotes = [];
        $saison = $sr->findAll();
        $budget = $br->findAll();
        $ingredient = $ing->findAll();
        $categorie = $cr->findAll();

        //affichage de la note
        foreach ($recettes as $recette) {
            $commentaires = $cor->findBy(['recette' => $recette]);
            $totalNotes = 0;
            $count = count($commentaires);

            foreach ($commentaires as $commentaire) {
                $totalNotes += (float)$commentaire->getNote();
            }

            $averageNotes[$recette->getId()] = $count > 0 ? $totalNotes / $count : null;
        }

        return $this->render('recettes/index.html.twig', [
            'recettes' => $recettes,
            'averageNotes' => $averageNotes,
            'categorie' => $categorie,
            'saison' => $saison,
            'budget' => $budget,
            'ingredient' => $ingredient,
        ]);
    }
    #[Route('/recettes/price', name: 'app_recettes_priceAll')]
    public function priceAll(CategorieRepository $cr, IngredientRepository $ing, SaisonRepository $sr, BudgetRepository $br, RecetteRepository $rr): Response
    { 
        $saison = $sr->findAll();
        $budget = $br->findAll();
        $ingredient = $ing->findAll();
        $categorie = $cr->findAll();
        $recette = $rr->findAll();


        return $this->render('recettes/priceAll.html.twig', [
            'categorie' => $categorie,
            'saison' => $saison,
            'budget' => $budget,
            'ingredient' => $ingredient,
            'recette' => $recette,

        ]);
    }

    
    #[Route('/recettes/price/{id}', name: 'app_recettes_price')]
    public function price(CategorieRepository $cr,$id, IngredientRepository $ing, SaisonRepository $sr, BudgetRepository $br, RecetteRepository $rr): Response
    { 
        $saison = $sr->findAll();
        $budget = $br->findAll();
        $budgetId = $br->find($id);
        $ingredient = $ing->findAll();
        $categorie = $cr->findAll();
        $budgetName = $budgetId-> getNom();
        $recette = $rr->findAll();
        $targetRecette = $budgetId->getRecettes();

        return $this->render('recettes/price.html.twig', [
            'categorie' => $categorie,
            'saison' => $saison,
            'budget' => $budget,
            'ingredient' => $ingredient,
            'recette' => $recette,
            'budgetName' => $budgetName,
            'budgetId' => $budgetId,

            'targetRecette' => $targetRecette,
        ]);
    }

    #[Route('/recettes/ingredient/{id}', name: 'app_recettes_ingredient')]
    public function ingredient(CategorieRepository $cr,$id, IngredientRepository $ing, SaisonRepository $sr, BudgetRepository $br, RecetteRepository $rr): Response
    { 
        $saison = $sr->findAll();
        $budget = $br->findAll();
        $ingredientId = $ing->find($id);
        $ingredient = $ing->findAll();
        $categorie = $cr->findAll();
        $ingredientName = $ingredientId-> getNom();
        $recette = $rr->findAll();
        $targetRecette = $ingredientId->getRecettes();

        return $this->render('recettes/ingredient.html.twig', [
            'categorie' => $categorie,
            'saison' => $saison,
            'budget' => $budget,
            'ingredient' => $ingredient,
            'recette' => $recette,
            'ingredientName' => $ingredientName,
            'ingredientId' => $ingredientId,
            'recette' => $recette,
            'targetRecette' => $targetRecette,
        ]);
    }

    #[Route('/recettes/{id}', name: 'app_recettes_show')]
    public function show(RecetteRepository $rr, CommentaireRepository $cor, CategorieRepository $cr, IngredientRepository $ing, SaisonRepository $sr, BudgetRepository $br, DifficulteRepository $dr, UstensileRepository $ur, Request $request, EntityManagerInterface $entityManager, $id): Response
    { 
        $oneRec = $rr->find($id);
            if (!$oneRec) {
                throw $this->createNotFoundException('Recette non trouvée');
            }
        $recettes = $rr->findAll();
        $saison = $sr->findAll();
        $budget = $br->findAll();
        $ingredient = $ing->findAll();
        $categorie = $cr->findAll();
        $averageNotes = [];
        $difficulte = $dr->findAll();
        $ustensile = $ur->findAll();

        // Récupérer les catégories de la recette actuelle
        $categories = $oneRec->getCategorie();

        // Trouver les recettes qui partagent au moins une catégorie
        $troisRecettes = $rr->createQueryBuilder('r')
        ->join('r.categorie', 'c')
        ->where('c IN (:categories)')
        ->andWhere('r.id != :currentId')
        ->setParameter('categories', $categories)
        ->setParameter('currentId', $id)
        ->orderBy('r.date', 'DESC')
        ->setMaxResults(3)
        ->getQuery()
        ->getResult();

        // Récupérer tous les ingredients pour cette recette
        $ingredients = $oneRec->getIngredient();

        // Récupérer tous les ustensiles pour cette recette
        $ustensiles = $oneRec->getUstensile();

        // Récupérer tous les commentaires pour cette recette
        $commentaires = $cor->findBy(['recette' => $oneRec], ['date' => 'DESC']);

        // Insérer un nouveau commentaire pour cette recette
        $commentaire = new Commentaire();
        $commentaire->setRecette($oneRec);

        // Calcul de la note moyenne pour cette recette spécifique
        $commentaires = $cor->findBy(['recette' => $oneRec]);
        $totalNotes = 0;
        $count = count($commentaires);
        foreach ($commentaires as $commentaire) {
            $totalNotes += (float)$commentaire->getNote();
        }
        $averageNote = $count > 0 ? $totalNotes / $count : null;

        // Insérer un nouveau commentaire pour cette recette
        $newCommentaire = new Commentaire();
        $newCommentaire->setRecette($oneRec);
        $form = $this->createForm(CommentaireType::class, $newCommentaire);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($newCommentaire);
            $entityManager->flush();

            $this->addFlash('success', 'Votre commentaire a été ajouté avec succès!');

            return $this->redirectToRoute('app_recettes_show', ['id' => $oneRec->getId()]);
        }

        return $this->render('recettes/show.html.twig', [
            'recette' => $oneRec,
            'recettes' => $recettes,
            'categorie' => $categorie,
            'saison' => $saison,
            'budget' => $budget,
            'ingredient' => $ingredient,
            'averageNote' => $averageNote,
            'oneRec' => $oneRec,
            'categories' => $categories,
            'difficulte' => $difficulte,
            'troisRecettes' => $troisRecettes,
            'commentaires' => $commentaires,
            'ingredients' => $ingredients,
            'ustensiles' => $ustensiles,
            'ustensile' => $ustensile,
            'commentaire' => $form->createView(),
        ]);
    
    }
        #[Route('add/recettes', name: 'app_recettes_new')]
        public function new(Request $request, EntityManagerInterface $em): Response
        {
           
            $recette = new Recette();
    
            
            $form = $this->createForm(AddRecettesType::class, $recette);
            $form->handleRequest($request);
    
            // Vérifier si le formulaire est soumis et valide
            if ($form->isSubmitted() && $form->isValid()) {
                // Enregistrer la recette dans la base de données
                $em->persist($recette);
                $em->flush();
    
                // Rediriger vers la liste des recettes
                return $this->redirectToRoute('app_recettes');
            }
    
            // Afficher le formulaire
            return $this->render('recettes/add.html.twig', [
                'form' => $form->createView(),
            ]);
        }
}