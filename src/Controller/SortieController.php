<?php

namespace App\Controller;


use App\Data\SearchData;
use App\Form\SearchForm;
use App\Repository\CampusRepository;
use App\Repository\SortieRepository;
use App\Entity\Sortie;
use App\Form\SortieType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SortieController extends AbstractController
{
    #[Route('/', name: 'app_accueil')]
    public function index(Request $request, SortieRepository $sortieRepository, CampusRepository $campusRepository): Response
    {
        // Récupérer la date actuelle
        $currentDate = new \DateTime();

        // Récupérer l'utilisateur connecté
        $user = $this->getUser();

        // Récupérer les sorties
        $sorties = $sortieRepository->findSearch();

        // Récupérer les campus depuis la base de données
        $campusEntities = $campusRepository->findAll();

        // Créer un tableau associatif des campus (id => nom) pour les choix du formulaire
        $campusChoices = [];
        foreach ($campusEntities as $campus) {
            $campusChoices[$campus->getNom()] = $campus->getNom();
        }

        // Récupérer le form pour le filtre
        $data = new SearchData();
        $form = $this->createForm(SearchForm::class, $data, [
            'campus_choices' => $campusChoices,
        ]);

        // Gérer la soumission du formulaire
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Effectuer le traitement du formulaire ici
            // ...

            // Rediriger ou rendre une autre vue si nécessaire
        }

        // Rendre la vue en passant la date, l'utilisateur et le formulaire
        return $this->render('pages/accueil.html.twig', [
            'currentDate' => $currentDate,
            'user' => $user,
            'sorties' => $sorties,
            'form' => $form->createView()
        ]);
    }

    #[Route('/sortie/creer', name: 'sortie_creer')]
    public function creer(Request $request, EntityManagerInterface $entityManager): Response
    {
        $sortie = new Sortie();
        $sortieForm = $this->createForm(SortieType::class, $sortie);
        $sortieForm->handleRequest($request);

        if ($sortieForm->isSubmitted() && $sortieForm->isValid()) {

            $entityManager->persist($sortie);
            $entityManager->flush();

            return $this->redirectToRoute('app_accueil');
        }

        return $this->render('pages/creerSortie.html.twig', [
            'sortieForm' => $sortieForm->createView()
        ]);
    }
}
