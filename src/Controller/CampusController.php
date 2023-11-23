<?php

namespace App\Controller;

use App\Entity\Campus;
use App\Form\CampusType;
use App\Repository\CampusRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;

class CampusController extends AbstractController
{
    private ManagerRegistry $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    #[Route('/admin/campus/ajouter', name: 'app_campus')]
    public function ajouterCampus(Request $request, CampusRepository $campusRepository, EntityManagerInterface $entityManager): Response
    {
        $searchTerm = $request->query->get('searchTerm');

        $nouveauCampus = new Campus();

        $campus = $campusRepository->findFiltered($searchTerm);

        $campusForm = $this->createForm(CampusType::class, $nouveauCampus);
        $campusForm->handleRequest($request);

        if ($campusForm->isSubmitted() && $campusForm->isValid()) {
            // Vérifier si le campus existe déjà
            $existingCampus = $campusRepository->findOneBy([
                'nom' => $nouveauCampus->getNom()
            ]);

            if ($existingCampus) {
                // Le campus existe déjà, affichez un message d'erreur
                $this->addFlash('error', 'Ce campus n\'existe déjà.');
            } else {
                // La ville n'existe pas encore, persistez-la
                $entityManager->persist($nouveauCampus);
                $entityManager->flush();

                $this->addFlash('success', 'Nouveau campus ajouté avec succès.');

                return $this->redirectToRoute('app_campus');
            }
        }

        return $this->render('pages/campus.html.twig', [
            'campusForm' => $campusForm->createView(),
            'campus' => $campus,
            'searchTerm' => $searchTerm,
        ]);
    }

    #[Route('/admin/campus/{id}/modifier', name: 'campus_modifier', methods: ['POST'])]
    public function modifierCampus(Request $request, CampusRepository $campusRepository, Campus $campus, EntityManagerInterface $entityManager): Response
    {
        $newName = $request->request->get('new_name');

        if (empty($newName)) {
            // Affiche un message d'erreur
            $this->addFlash('error', 'Veuillez remplir tous les champs.');
            return $this->redirectToRoute('app_campus'); // Redirige vers la page souhaitée
        }

        $existingCampus = $campusRepository->findOneBy(['nom' => $newName]);

        if ($existingCampus && $existingCampus->getId() !== $campus->getId()) {
            // Affiche un message d'erreur si le campus existe déjà
            $this->addFlash('error', 'Le campus existe déjà dans la base de données.');
            return $this->redirectToRoute('app_campus'); // Redirige vers la page souhaitée
        }

        $campus->setNom($newName);

        $entityManager->flush();

        $this->addFlash('success', 'Le campus a été modifiée avec succès.');

        // Redirige vers la page souhaitée après la modification.
        return $this->redirectToRoute('app_campus');
    }



    #[Route('/admin/campus/{id}/supprimer/', name: 'campus_supprimer', methods: ['GET'])]
    public function supprimerCampus(Campus $campus): Response
    {
        // Vérifier si le campus est utilisée dans une sortie
        if ($this->isCampusUsed($campus)) {
            $this->addFlash('error', 'Le campus est utilisé et ne peut pas être supprimée.');
        } else {
            $entityManager = $this->doctrine->getManager();
            $entityManager->remove($campus);
            $entityManager->flush();

            $this->addFlash('success', 'Campus supprimée avec succès.');
        }

        return $this->redirectToRoute('app_campus');
    }

    /**
     * Vérifie si le campus est utilisé.
     *
     * @param Campus $campus
     * @return bool
     */
    private function isCampusUsed(Campus $campus): bool
    {
        // Récupérer les lieux associés à la ville
        $participants = $campus->getParticipants();
        $sorties = $campus->getSorties();

        if (count($participants) !== 0 || count($sorties) !== 0) {
            return true;
        }

        return false;
    }
}
