<?php

namespace App\Controller;

use App\Entity\Commentaire;
use App\Entity\Favoris;
use App\Entity\Recette;
use App\Entity\User;
use App\Form\AddRecettesType;
use App\Form\CommentaireType;
use App\Repository\CategorieRepository;
use App\Repository\CommentaireRepository;
use App\Repository\RecetteRepository;
use App\Repository\BudgetRepository;
use App\Repository\DifficulteRepository;
use App\Repository\FavorisRepository;
use App\Repository\IngredientRepository;
use App\Repository\SaisonRepository;
use App\Repository\UserRepository;
use App\Repository\UstensileRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Validator\Constraints\DateTime;

class RecettesController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
        

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

    public function price(CategorieRepository $cr,CommentaireRepository $cor, $id, IngredientRepository $ing, SaisonRepository $sr, BudgetRepository $br, RecetteRepository $rr): Response

    { 
        $saison = $sr->findAll();
        $budget = $br->findAll();
        $budgetId = $br->find($id);
        $ingredient = $ing->findAll();
        $categorie = $cr->findAll();
        $budgetName = $budgetId-> getNom();
        $recettes = $rr->findAll();
        $targetRecette = $budgetId->getRecettes();

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


        return $this->render('recettes/price.html.twig', [
            'categorie' => $categorie,
            'saison' => $saison,
            'budget' => $budget,
            'ingredient' => $ingredient,
            'recette' => $recettes,
            'budgetName' => $budgetName,
            'budgetId' => $budgetId,
            'targetRecette' => $targetRecette,
            'averageNotes' => $averageNotes,

        ]);
    }

    #[Route('/recettes/ingredient/{id}', name: 'app_recettes_ingredient')]


    public function ingredient(CategorieRepository $cr,$id,CommentaireRepository $cor, IngredientRepository $ing, SaisonRepository $sr, BudgetRepository $br, RecetteRepository $rr): Response

    { 
        $saison = $sr->findAll();
        $budget = $br->findAll();
        $ingredientId = $ing->find($id);
        $ingredient = $ing->findAll();
        $categorie = $cr->findAll();
        $ingredientName = $ingredientId-> getNom();
        $recette = $rr->findAll();
        $targetRecette = $ingredientId->getRecettes();


        foreach ($recette as $recette) {
            $commentaires = $cor->findBy(['recette' => $recette]);
            $totalNotes = 0;
            $count = count($commentaires);

            foreach ($commentaires as $commentaire) {
                $totalNotes += (float)$commentaire->getNote();
            }

            $averageNotes[$recette->getId()] = $count > 0 ? $totalNotes / $count : null;
        }

        return $this->render('recettes/ingredient.html.twig', [
            'categorie' => $categorie,
            'saison' => $saison,
            'budget' => $budget,
            'ingredient' => $ingredient,
            'recette' => $recette,
            'ingredientName' => $ingredientName,
            'ingredientId' => $ingredientId,
            'targetRecette' => $targetRecette,
            'averageNotes' => $averageNotes

        ]);
    }

    #[Route('/recettes/{id}', name: 'app_recettes_show')]
    public function show(UserRepository $user, RecetteRepository $rr, CommentaireRepository $cor, CategorieRepository $cr, IngredientRepository $ing, SaisonRepository $sr, BudgetRepository $br, DifficulteRepository $dr, UstensileRepository $ur, FavorisRepository $fav, Request $request, EntityManagerInterface $entityManager, $id): Response
    { 
        $oneRec = $rr->find($id);
            if (!$oneRec) {
                throw $this->createNotFoundException('Recette non trouvée');
            }
        $saison = $sr->findAll();
        $budget = $br->findAll();
        $ingredient = $ing->findAll();
        $categorie = $cr->findAll();
        $averageNotes = [];
        $difficulte = $dr->findAll();
        $ustensile = $ur->findAll();
        $user = $this->getUser();

        // Vérifier si la recette est dans les favoris de l'utilisateur
                
        //$isFavorite = $fav->find($oneRec);
        $isFavorite = false;
        if ($user instanceof User) {
            $favori = $fav->findOneBy([
                'idUser' => $user,
                'recette' => $oneRec
            ]);
            $isFavorite = $favori !== null;
        }
       

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
            'isFavorite' => $isFavorite,
            'idUser' => $user,
            'favoris' =>'$favoris',
            'categorie' => $categorie,
            'saison' => $saison,
            'budget' => $budget,
            'ingredient' => $ingredient,
            'averageNote' => $averageNote,
            'averageNotes' => $averageNotes,
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
        public function new(Request $request, EntityManagerInterface $em, SluggerInterface $slugger,#[Autowire('%kernel.project_dir%/public/uploads/')] string $uploadDirectory): Response
        {
           
            $recette = new Recette();
            $form = $this->createForm(AddRecettesType::class, $recette);
            $form->handleRequest($request);
            $user = $this->getUser();

    
            // Vérifier si le formulaire est soumis et valide
            if ($form->isSubmitted() && $form->isValid()) {
                 /** @var UploadedFile */
                $image = $form->get('image')->getData();
                if($image){
                    $originalFileName = pathinfo($image->getClientOriginalName(),PATHINFO_FILENAME);
                    $safeFilename = $slugger->slug($originalFileName);
                    $newFileName = $safeFilename.'-'.uniqid().'.'.$image->guessExtension();

                    try{
                        $image->move($uploadDirectory, $newFileName);
                    }catch(FileException $e){

                    }
                    $recette->setImage($newFileName);
                }


                $recette = $form ->getData();
                $recette ->setidUser($user);
                $recette ->setDate(new DateTimeImmutable('today'));
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

    #[Route('/', name: 'app_accueil')]
    public function accueil(CategorieRepository $cr,CommentaireRepository $cor, IngredientRepository $ing,RecetteRepository $rr, SaisonRepository $sr, BudgetRepository $br): Response
    {
        $categorie = $cr->findAll();
        $saison = $sr->findAll();
        $budget = $br->findAll();
        $ingredient = $ing->findAll();
        $notes = $rr ->foundByNote();
        $recettes = $rr->foundByOrder();
        $averageNotes = [];

        foreach ($recettes as $recette) {
            $commentaires = $cor->findBy(['recette' => $recette]);
            $totalNotes = 0;
            $count = count($commentaires);

            foreach ($commentaires as $commentaire) {
                $totalNotes += $commentaire->getNote();
            }

            $averageNotes[$recette->getId()] = $count > 0 ? $totalNotes / $count : null;
        }

        return $this->render('accueil.html.twig', [
            'controller_name' => 'CategorieController',
            'categorie' => $categorie,
            'saison' => $saison,
            'budget' => $budget,
            'ingredient' => $ingredient,
            'recettes' => $recettes,
            'averageNotes' => $averageNotes,
            'note' => $notes,
        
        ]);
    }
    
    #[Route('ingredient', name: 'app_recettes_allIngredient')]
    public function ingredientAll(CategorieRepository $cr,CommentaireRepository $cor, IngredientRepository $ing, SaisonRepository $sr, BudgetRepository $br, RecetteRepository $rr): Response
    { 
        $saison = $sr->findAll();
        $budget = $br->findAll();
        $ingredient = $ing->findAll();
        $categorie = $cr->findAll();
        $recettes = $rr->findAll();

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

        return $this->render('recettes/allIngredient.html.twig', [
            'categorie' => $categorie,
            'saison' => $saison,
            'budget' => $budget,
            'ingredient' => $ingredient,
            'recette' => $recette,
            
        ]);
    }
 
    #[Route('/recette/{id}/favori', name:"ajout_favori")]

    public function ajoutFavori(Recette $recette, Request $request): Response
    {
        $idUser = $this->getUser(); // S'assurer que l'utilisateur est connecté
        if (!$idUser) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour ajouter des favoris.');
        }

        $favori = $this->entityManager->getRepository(Favoris::class)->findOneBy([
            'idUser' => $idUser,
            'recette' => $recette
        ]);

        if ($favori) {
            // Si le favori existe, on le supprime
            $this->entityManager->remove($favori);
            $this->addFlash('error', 'La recette a été retirée de vos favoris.');
        } else {
            // Sinon, on en crée un nouveau
            $favori = new Favoris();
            $favori->setIdUser($idUser);
            $favori->setRecette($recette);
            $this->entityManager->persist($favori);
            $this->addFlash('success', 'La recette a été ajoutée à vos favoris.');
        }

        // Récupérer l'URL de la page précédente
        $referer = $request->headers->get('referer');
        $this->entityManager->flush();

        // Récupérer l'URL de la page précédente
        $referer = $request->headers->get('referer');
        $this->entityManager->flush();

        // Rediriger vers la page précédente ou vers la page d'accueil si le referer n'est pas disponible
        return $referer ? $this->redirect($referer) : $this->redirectToRoute('app_accueil');
    }
    
}