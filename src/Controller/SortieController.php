<?php

namespace App\Controller;


use App\Data\SearchData;
use App\Form\SearchForm;
use App\Repository\CampusRepository;
use App\Repository\SortieRepository;
use App\Entity\Sortie;
use App\Form\SortieType;
use App\Repository\EtatRepository;
use App\Repository\LieuRepository;
use App\Repository\VilleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SortieController extends AbstractController
{
    #[Route('/', name: 'app_accueil')]
    public function index(Request $request, SortieRepository $sortieRepository, CampusRepository $campusRepository): Response
    {
        $currentDate = new \DateTime();
        $user = $this->getUser();

        // Récupérer les campus depuis la base de données
        $campusEntities = $campusRepository->findAll();
        $campusChoices = [];
        foreach ($campusEntities as $campus) {
            $campusChoices[$campus->getNom()] = $campus->getNom();
        }

        // Récupérer le form pour le filtre
        $data = new SearchData();
        $form = $this->createForm(SearchForm::class, $data, [
            'campus_choices' => $campusChoices,
        ]);
        $form->handleRequest($request);

        $data = $form->getData();
        $sorties = $sortieRepository->findSearch($data, $user);

        return $this->render('pages/accueil.html.twig', [
            'currentDate' => $currentDate,
            'user' => $user,
            'sorties' => $sorties,
            'form' => $form->createView()
        ]);
    }

    #[Route('/sortie/creer', name: 'sortie_creer')]
    public function creer(Request $request, EntityManagerInterface $entityManager, VilleRepository $villeRepository, LieuRepository $lieuRepository, EtatRepository $etatRepository): Response
    {
        $villes = $villeRepository->findAll();
        $sortie = new Sortie();
        //Réutiliser la function creer pour la page modification ?
        //$sortie = $id ? $this->getDoctrine()->getRepository(Sortie::class)->find($id) : new Sortie();

        /** @var \App\Entity\Participant $auteur */
        $auteur = $this->getUser();
        $sortie->setOrganisateur($auteur);
        $sortie->setSiteOrganisateur($auteur->getCampus());

        $idLieu = $request->request->get('lieu', '');
        $submit = $request->request->get('submit', '');

        if ($idLieu && $submit) {
            $lieu = $lieuRepository->find($idLieu);
            $etat = ($submit == "enregistrer") ? $etatRepository->find(1) : $etatRepository->find(2);

            $sortie->setLieu($lieu);
            $sortie->setEtat($etat);
        }

        $sortieForm = $this->createForm(SortieType::class, $sortie);
        $sortieForm->handleRequest($request);

        if ($sortieForm->isSubmitted() && $sortieForm->isValid()) {

            $entityManager->persist($sortie);
            $entityManager->flush();

            return $this->redirectToRoute('app_accueil');
        }

        return $this->render('pages/creerSortie.html.twig', [
            'sortieForm' => $sortieForm->createView(),
            'villes' => $villes
        ]);
    }

    #[Route(path: '/sortie/{id}', name: 'sortie_informationSortie')]
    public function informationSortie(SortieRepository $sortieRepository, int $id): Response
    {
        $sortie = $sortieRepository->find($id);

        return $this->render('pages/informationsSortie.html.twig', [
            'sortie' => $sortie
        ]);
    }

    #[Route('/sortie/{id}/inscription', name: 'sortie_inscription')]
    public function inscriptionSortie(int $id, EntityManagerInterface $entityManager,  SortieRepository $sortieRepository)
    {
        // Récupérer la sortie depuis la base de données
        $sortie = $sortieRepository->find($id);
        $participant = $this->getUser();

        // Vérifier si le participant est déjà inscrit à cette sortie ou si la sortie est commencée
        if ($sortie->getParticipants()->contains($participant) && $sortie->getEtat()->getLibelle() !== 'Activité en cours') {
            // Si le participant est déjà inscrit, le désinscrire
            $sortie->removeParticipant($participant);
            $entityManager->persist($participant);
            $entityManager->flush();

            // Vérifier si la sortie est strictement 'ouverte' et le nombre d'inscrit n'est pas dépassée
        } else if ($sortie->getEtat()->getLibelle() === 'Ouverte' && $sortie->getParticipants()->count() < $sortie->getNbInscriptionsMax()) {

            // Inscrire le participant à la sortie
            $sortie->addParticipant($participant); // Ajouter le participant à la sortie
            $entityManager->persist($participant);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_accueil');
    }

    #[Route('/ville/{id}/lieux')]
    public function recupererLieuxDuneVille(LieuRepository $lieuRepository, VilleRepository $villeRepository, int $id)
    {
        $ville = $villeRepository->find($id);
        $lieux = $lieuRepository->rechercheParVille($ville);

        $lieuxTableau = [];
        foreach ($lieux as $lieu) {
            $lieuxTableau[] = [
                'id' => $lieu->getId(),
                'nom' => $lieu->getNom(),
                'rue' => $lieu->getRue(),
                'codePostal' => $ville->getCodePostal(),
                'latitude' => $lieu->getLatitude(),
                'longitude' => $lieu->getLongitude(),
            ];
        }

        return new JsonResponse($lieuxTableau);
    }

    #[Route('/admin/sortie/{id}/supprimer', name: 'admin_sortie_supprimer')]
    public function supprimerSortie(int $id, EntityManagerInterface $entityManager): Response
    {
        // Récupérer la sortie depuis la base de données
        $sortie = $entityManager->getRepository(Sortie::class)->find($id);

        // Supprimer la sortie
        $entityManager->remove($sortie);
        $entityManager->flush();

        // Rechargement de la page après la suppression
        return $this->render('pages/modifierSortie.html.twig');
    }

}
