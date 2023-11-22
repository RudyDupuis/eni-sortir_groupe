<?php

namespace App\Controller;

use App\Data\SearchDataRechercher;
use App\Entity\Campus;
use App\Form\CampusType;
use App\Form\SearchFormRechercher;
use App\Repository\CampusRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Doctrine\Persistence\ManagerRegistry;

class CampusController extends AbstractController
{
    private ManagerRegistry $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    #[Route('/campus', name: 'app_campus', methods: ['GET'])]
    public function index(Request $request, CampusRepository $campusRepository): Response
    {
        // Récupérer le form pour le filtre
        $data = new SearchDataRechercher();
        $form = $this->createForm(SearchFormRechercher::class, $data);
        $form->handleRequest($request);

        $data = $form->getData();
        $campuses = $campusRepository->findSearch($data);

        return $this->render('pages/campus.html.twig', [
            'campuses' => $campuses,
            'form' => $form->createView()
        ]);
    }

    #[Route('/campus/modifier/{id}', name: 'campus_modifier', methods: ['GET', 'POST'])]
    public function modifierCampus(Request $request, Campus $campus, ValidatorInterface $validator): Response
    {
        if ($request->isMethod('POST')) {
            $newName = $request->request->get('newName');

            // Validation du nouveau nom
            $validationErrors = $validator->validate($newName, [
                new \Symfony\Component\Validator\Constraints\NotBlank(['message' => 'Le nom du campus ne doit pas être vide.']),
                new \Symfony\Component\Validator\Constraints\Length([
                    'max' => 30,
                    'maxMessage' => 'Le nom du campus ne doit pas dépasser {{ limit }} caractères.',
                ]),
            ]);

            if (count($validationErrors) === 0) {
                // Vérifiez si le nom du campus n'existe pas déjà
                $existingCampus = $this->getDoctrine()->getRepository(Campus::class)->findOneBy(['nom' => $newName]);

                if (!$existingCampus || $existingCampus === $campus) {
                    // Le nom du campus est valide et n'existe pas déjà (ou il s'agit du même campus), procédez à la modification
                    $entityManager = $this->getDoctrine()->getManager();

                    // Mettre à jour le nom du campus
                    $campus->setNom($newName);

                    // Enregistrer les modifications dans la base de données
                    $entityManager->flush();

                    $this->addFlash('success', 'Le nom du campus a été mis à jour avec succès.');
                } else {
                    $this->addFlash('error', 'Le nom du campus existe déjà.');
                }
            } else {
                $this->addFlash('error', 'Le nom du campus ne peut pas être vide.');
            }

            return $this->redirectToRoute('app_campus');
        }

        return $this->render('pages/modifier_campus.html.twig', [
            'campus' => $campus,
        ]);
    }



    #[Route('/campus/supprimer/{id}', name: 'campus_supprimer', methods: ['GET'])]
    public function supprimerCampus(Campus $campus): Response
    {
        $entityManager = $this->doctrine->getManager();
        $entityManager->remove($campus);
        $entityManager->flush();

        $this->addFlash('success', 'Campus supprimé avec succès.');

        return $this->redirectToRoute('app_campus');
    }

    #[Route('/campus/ajouter', name: 'campus_ajouter', methods: ['POST'])]
    public function ajouterCampus(Request $request, ValidatorInterface $validator): Response
    {
        $nouveauCampusNom = $request->request->get('nouveauCampus');

        // Vérifiez si le nom du campus est valide
        $validationErrors = $validator->validate($nouveauCampusNom, [
            new \Symfony\Component\Validator\Constraints\NotBlank(['message' => 'Le nom du campus ne doit pas être vide.']),
            new \Symfony\Component\Validator\Constraints\Length([
                'max' => 30,
                'maxMessage' => 'Le nom du campus ne doit pas dépasser {{ limit }} caractères.',
            ]),
        ]);

        if (count($validationErrors) === 0) {
            // Vérifiez si le nom du campus n'existe pas déjà
            $existingCampus = $this->doctrine->getRepository(Campus::class)->findOneBy(['nom' => $nouveauCampusNom]);

            if (!$existingCampus) {
                // Le nom du campus est valide et n'existe pas déjà, procédez à l'ajout
                $entityManager = $this->doctrine->getManager();

                // Créez une nouvelle instance de l'entité Campus
                $nouveauCampus = new Campus();
                $nouveauCampus->setNom($nouveauCampusNom);

                // Persistez l'entité dans la base de données
                $entityManager->persist($nouveauCampus);
                $entityManager->flush();

                $this->addFlash('success', 'Nouveau campus ajouté avec succès.');
            } else {
                $this->addFlash('error', 'Le nom du campus existe déjà.');
            }
        } else {
            $this->addFlash('error', 'Le nom du campus ne peut pas être vide.');
        }

        // Redirigez vers la page principale des campus
        return $this->redirectToRoute('app_campus');
    }
}