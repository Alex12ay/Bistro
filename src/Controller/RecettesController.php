<?php

namespace App\Controller;

use App\Entity\Commentaire;
use App\Entity\Etapes;
use App\Entity\Favoris;
use App\Entity\Ingredient;
use App\Entity\Quantite;
use App\Entity\Recette;
use App\Entity\User;
use App\Entity\Ustensile;
use App\Form\AddEtapesType;
use App\Form\AddIngredientsType;
use App\Form\AddRecettesType;
use App\Form\AddUstensileType;
use App\Form\ChoiceUstensileType;
use App\Form\CommentaireType;
use App\Form\QuantiteType;
use App\Repository\CategorieRepository;
use App\Repository\CommentaireRepository;
use App\Repository\RecetteRepository;
use App\Repository\BudgetRepository;
use App\Repository\DifficulteRepository;
use App\Repository\FavorisRepository;
use App\Repository\IngredientRepository;
use App\Repository\QuantiteRepository;
use App\Repository\SaisonRepository;
use App\Repository\UserRepository;
use App\Repository\UstensileRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

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
        $averageNotes = [];

         //affichage de la note
         foreach ($targetRecette as $recette) {
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
        $ingredient = $ing->findAll();
        $categorie = $cr->findAll();
        $ingredientId = $ing->find($id);
        $ingredientName = $ingredientId-> getNom();
        $recette = $rr->findAll();

        $targetRecette = $rr->findByIngredient($ingredientName);

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
            'averageNotes' => $averageNotes,
         
        ]);
    }

    #[Route('/recettes/{id}', name: 'app_recettes_show')]
    public function show(UserRepository $user, RecetteRepository $rr,QuantiteRepository $qr, CommentaireRepository $cor, CategorieRepository $cr, IngredientRepository $ing, SaisonRepository $sr, BudgetRepository $br, DifficulteRepository $dr, UstensileRepository $ur, FavorisRepository $fav, Request $request, EntityManagerInterface $entityManager, $id): Response
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
        $ingredients = $oneRec->getQuantites();
        
        // Récupérer tous les ustensiles pour cette recette
        $ustensiles = $oneRec->getUstensile();

        //Récupérer les étapes de la recette
        $etapes = $oneRec->getEtapes();

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
            'ingredients' => $ingredients,
            'ustensile' => $ustensile,
            'commentaire' => $form->createView(),
            'etapes' => $etapes,
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
                
                return $this->redirectToRoute('app_recette_add.');
                
            }
            // Afficher le formulaire
            return $this->render('recettes/add.html.twig', [
                'form' => $form->createView(),
            ]);

        }
        #[Route('/error', name: 'app_error')]
        public function error(EntityManagerInterface $em): Response
        {   
            return $this->render('error.html.twig', [
                
            ]);
        }

        #[Route('/add/recettes/ingredients', name: 'app_recette_add.')]
        public function newQ(Request $request,IngredientRepository $ing, SluggerInterface $slugger, RecetteRepository $rr,EntityManagerInterface $em,#[Autowire('%kernel.project_dir%/public/uploads/')] string $uploadDirectory): Response
        {
            $user = $this->getUser();
            if ($user == null) {
                return $this->redirectToRoute('app_error');
            }
            $newQ = new Quantite();
            $ingTrier = $ing->findBy([],['nom'=> 'ASC']);
            $formulaire = $this->createForm(QuantiteType::class, $newQ,[
                'ingredientTrier' => $ingTrier,
            ]);
            
            $formulaire->handleRequest($request);
            
            $findLast = $rr -> foundByUser($user);
            $ingredient = $findLast[0]->getQuantites();dump($ingredient);

            $newI = new Ingredient();
            $form =$this->createForm(AddIngredientsType::class,$newI);
            $form->handleRequest($request);
            //Créer un ingrédient
            if ($form->isSubmitted() && $form->isValid()) {
                  /** @var UploadedFile */
                  $image = $form->get('thumbnail')->getData();
                  if($image){
                      $originalFileName = pathinfo($image->getClientOriginalName(),PATHINFO_FILENAME);
                      $safeFilename = $slugger->slug($originalFileName);
                      $newFileName = $safeFilename.'-'.uniqid().'.'.$image->guessExtension();
  
                      try{
                          $image->move($uploadDirectory, $newFileName);
                      }catch(FileException $e){
  
                      }
                      $newI->setThumbnail($newFileName);
                  }
                $newI = $form ->getData();
                $em->persist($newI);
                $em->flush();
                
                return $this->redirectToRoute('app_recette_add.');

                }

             //Ajouter un ingrédient à la recette
            if ($formulaire->isSubmitted() && $formulaire->isValid()) {
                $newQ = $formulaire ->getData();
                $newQ -> setRecette($findLast[0]);dump($findLast);
                $em->persist($newQ);
                $em->flush();
                
                return $this->redirectToRoute('app_recette_add.');

                }
               
                return $this->render('recettes/ingredientRecette.html.twig', [
                    'formulaire' => $formulaire->createView(),
                    'ingredient' => $ingredient,
                    'recette' => $findLast,
                    'form' => $form,

                ]);
        }
        #[Route('/add/recettes/ustensiles', name: 'app_recette_add..')]
        public function addU(Request $request,RecetteRepository $rr,EntityManagerInterface $em): Response
        {
            $recette = $rr->findOneBy([], ['id' => 'DESC']);
            $ustensile = $recette->getUstensile();
            $formulaire = $this->createForm(ChoiceUstensileType::class, $recette);
            $formulaire->handleRequest($request);
            
            $newUs = new Ustensile;
            $form = $this->createForm(AddUstensileType::class, $newUs);

            if ($form->isSubmitted() && $form->isValid()) {
                $em->persist($newUs);
                $em->flush();
            }
        
            if ($formulaire->isSubmitted() && $formulaire->isValid()) {
                
                $em->persist($recette);
                $em->flush();

               
                return $this->redirectToRoute('app_recette_add..');

                }
               
                return $this->render('recettes/ustensiles.html.twig', [
                    'formulaire' => $formulaire->createView(),
                    'ustensile' => $ustensile,
                    'form' => $form,

                ]);
        }

        #[Route('/add/recettes/etape', name: 'app_recette_etape')]
        public function etape(Request $request,RecetteRepository $rr,EntityManagerInterface $em): Response
        {
            $user = $this->getUser();
            if ($user == null) {
                return $this->redirectToRoute('app_error');
            }
            $newEtape = new Etapes();
            $formulaire = $this->createForm(AddEtapesType::class, $newEtape);
            $formulaire->handleRequest($request);
            $recette = $rr -> foundByUser($user);
            $derniereEtape = $recette[0]->getEtapes();
            $recette = $recette[0];
            $numeroEtape = count($derniereEtape) + 1;
           

            if ($formulaire->isSubmitted() && $formulaire->isValid()) {
                $newEtape = $formulaire->getData();
                $newEtape -> setRecette($recette[0]);
                $newEtape -> setEtapes($numeroEtape);               
                $em->persist($newEtape);
                $em->flush();

               
                return $this->redirectToRoute('app_recette_etape');

                }
               
                return $this->render('recettes/etape.html.twig', [
                    'form' => $formulaire->createView(),
                    'etape' => $derniereEtape,
                    'recette' => $recette,

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

        // Rediriger vers la page précédente ou vers la page d'accueil si le referer n'est pas disponible
        return $referer ? $this->redirect($referer) : $this->redirectToRoute('app_accueil');
    }
    
   

}