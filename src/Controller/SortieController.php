<?php

namespace App\Controller;


use App\Data\SearchData;
use App\Form\AnnulationSortieType;
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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

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

        /** @var \App\Entity\Participant $auteur */
        $auteur = $this->getUser();
        $sortie->setOrganisateur($auteur);
        $sortie->setSiteOrganisateur($auteur->getCampus());

        $idLieu = $request->request->get('lieu', '');
        $submit = $request->request->get('submit', '');

        if ($idLieu && $submit) {
            $lieu = $lieuRepository->find($idLieu);
            $etat = ($submit == "enregistrer") ? $etatRepository->rechercheParLibelle("Créée") : $etatRepository->rechercheParLibelle("Ouverte");

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

    #[Route('/sortie/{id}/supprimer', name: 'sortie_supprimer')]
    public function supprimerSortie(int $id, EntityManagerInterface $entityManager, SortieRepository $sortieRepository): Response
    {
        // Récupérer la sortie depuis la base de données
        $sortie = $sortieRepository->find($id);

        if ($this->getUser() !== $sortie->getOrganisateur()) {
            throw new AccessDeniedException("Accès interdit. Vous n'êtes pas l'organisateur de cette sortie.");
        }

        $entityManager->remove($sortie);
        $entityManager->flush();
        return $this->redirectToRoute('app_accueil');
    }

    #[Route('/sortie/{id}/modifier', name: 'sortie_modifier')]
    public function modifier(Request $request, EntityManagerInterface $entityManager, LieuRepository $lieuRepository, EtatRepository $etatRepository, int $id): Response
    {

        $sortie = $entityManager->getRepository(Sortie::class)->find($id);

        if ($this->getUser() !== $sortie->getOrganisateur()) {
            throw new AccessDeniedException("Accès interdit. Vous n'êtes pas l'organisateur de cette sortie.");
        }

        $sortieForm = $this->createForm(SortieType::class, $sortie);
        $sortieForm->handleRequest($request);

        // Récupération de la liste des lieux
        $lieux = $lieuRepository->findAll();


        if ($sortieForm->isSubmitted() && $sortieForm->isValid()) {

            $idLieu = $request->request->get('lieu', '');
            $submit = $request->request->get('submit', '');

            if ($idLieu && $submit) {
                $lieu = $lieuRepository->find($idLieu);
                dump($lieu);
                $etat = ($submit == "enregistrer") ? $etatRepository->rechercheParLibelle("Créée") : $etatRepository->rechercheParLibelle("Ouverte");

                $sortie->setLieu($lieu);
                $sortie->setEtat($etat);
            }


            $entityManager->persist($sortie);
            $entityManager->flush();
            return $this->redirectToRoute('app_accueil');
        }

        return $this->render('pages/modifierSortie.html.twig', [
            'sortieForm' => $sortieForm->createView(),
            'lieux' => $lieux,
            'sortie' => $sortie,
        ]);
    }

    #[Route('/sortie/{id}/publier', name: 'sortie_publier')]
    public function publier(EntityManagerInterface $entityManager, SortieRepository $sortieRepository, EtatRepository $etatRepository, int $id): Response
    {
        $sortie = $sortieRepository->find($id);

        if ($this->getUser() !== $sortie->getOrganisateur()) {
            throw new AccessDeniedException("Accès interdit. Vous n'êtes pas l'organisateur de cette sortie.");
        }

        if ($sortie->getEtat()->getLibelle() === "Créée") {
            $sortie->setEtat($etatRepository->rechercheParLibelle("Ouverte"));

            $entityManager->persist($sortie);
            $entityManager->flush();
        }
        return $this->redirectToRoute('app_accueil');
    }

    #[Route('/sortie/{id}/annuler', name: 'sortie_annuler')]
    public function annuler(Request $request, EntityManagerInterface $entityManager, SortieRepository $sortieRepository, EtatRepository $etatRepository, int $id): Response
    {
        $sortie = $sortieRepository->find($id);

        /** @var \App\Entity\Participant $user */
        $user = $this->getUser();

        if ($user == $sortie->getOrganisateur() || $user->isAdministrateur()) {
            $formAnnulationSortie = $this->createForm(AnnulationSortieType::class, $sortie);
            $formAnnulationSortie->handleRequest($request);

            if ($formAnnulationSortie->isSubmitted() && $formAnnulationSortie->isValid()) {
                $sortie->setEtat($etatRepository->rechercheParLibelle("Annulée"));

                $entityManager->persist($sortie);
                $entityManager->flush();

                return $this->redirectToRoute('app_accueil');
            }

            return $this->render('pages/annulerSortie.html.twig', [
                'sortie' => $sortie,
                'formAnnulationSortie' => $formAnnulationSortie->createView(),
            ]);
        } else {
            throw new AccessDeniedException("Accès interdit. Vous n'êtes pas l'organisateur de cette sortie.");
        }
    }
}
