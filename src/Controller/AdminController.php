<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\CreerParticipantType;
use App\Form\CreerParticipantsType;
use App\Repository\CampusRepository;
use App\Repository\ParticipantRepository;
use App\Service\ImageManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Reader;

#[Route('/admin')]
class AdminController extends AbstractController
{
    #[Route('/creer-un-participant', name: 'admin_creer_participant')]
    public function creerUnParticipant(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager, ImageManager $imageManager): Response
    {
        $participant = new Participant();
        $participantForm = $this->createForm(CreerParticipantType::class, $participant);
        $participantForm->handleRequest($request);

        if ($participantForm->isSubmitted() && $participantForm->isValid()) {

            $champPassword = $participantForm->get('motPasse')->getData();

            $hashedPassword = $passwordHasher->hashPassword(user: $participant, plainPassword: $champPassword);
            $participant->setMotPasse($hashedPassword);

            $participant->setAdministrateur('false');
            $participant->setActif('true');

            $entityManager->persist($participant);
            $entityManager->flush();

            $this->addFlash('success', 'Vos informations ont été mises à jour avec succès.');
            return $this->redirectToRoute('app_accueil');
        }

        return $this->render('pages/admin/creerParticipant.html.twig', [
            'participantForm' => $participantForm->createView()
        ]);
    }

    #[Route('/creer-des-participants', name: 'admin_creer_participants')]
    public function creerDesParticipants(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager, CampusRepository $campusRepository, ParticipantRepository $participantRepository): Response
    {
        $csvForm = $this->createForm(CreerParticipantsType::class);
        $csvForm->handleRequest($request);

        if ($csvForm->isSubmitted() && $csvForm->isValid()) {
            $csv = $csvForm->get('fichierCsv')->getData();

            // Lire le fichier CSV
            $csv = Reader::createFromPath($csv->getPathname(), 'r');
            $csv->setHeaderOffset(0); // si la première ligne contient des en-têtes

            $records = $csv->getRecords();

            foreach ($records as $record) {
                $testPseudo = $participantRepository->testerPseudo($record['Pseudo']);
                $testMail = $participantRepository->testerMail($record['Mail']);

                if ($testPseudo) {
                    $this->addFlash('erreurPseudo' . $record['Pseudo'], sprintf("Le pseudo '%s' est déjà utilisé, le participant n'est pas ajouté", $record['Pseudo']));
                }
                if ($testMail) {
                    $this->addFlash('erreurMail' . $record['Mail'], sprintf("Le mail '%s' est déjà utilisé, le participant n'est pas ajouté", $record['Mail']));
                }

                if (!$testMail && !$testPseudo) {
                    $participant = new Participant();
                    $error = false;

                    // Vérification du nom
                    if (strlen($record['Nom']) < 3 || strlen($record['Nom']) > 50) {
                        $error = true;
                        $this->addFlash('erreurNom' . $record['Nom'], sprintf("Le nom '%s' doit avoir entre 3 et 50 caractères, le participant n'est pas ajouté", $record['Nom']));
                    }
                    // Vérification du prénom
                    if (strlen($record['Prenom']) < 3 || strlen($record['Prenom']) > 50) {
                        $error = true;
                        $this->addFlash('erreurPrenom' . $record['Prenom'], sprintf("Le prénom '%s' doit avoir entre 3 et 50 caractères, le participant n'est pas ajouté", $record['Prenom']));
                    }
                    // Vérification du téléphone
                    if (strlen($record['Telephone']) > 20) {
                        $error = true;
                        $this->addFlash('erreurTelephone' . $record['Telephone'], sprintf("Le numéro de téléphone '%s' ne peut pas dépasser 20 caractères, le participant n'est pas ajouté", $record['Telephone']));
                    }

                    $campus = $campusRepository->rechercheParNom($record['Campus']);
                    if ($campus) {
                        $participant->setCampus($campus);
                    } else {
                        $error = true;
                        $this->addFlash('erreurCampus' . $record['Campus'], sprintf("Le campus '%s' n'existe pas, le participant n'est pas ajouté", $record['Campus']));
                    }

                    $hashedPassword = $passwordHasher->hashPassword(user: $participant, plainPassword: "12345678");
                    $participant->setMotPasse($hashedPassword);

                    $participant->setMail($record['Mail']);
                    $participant->setPseudo($record['Pseudo']);
                    $participant->setNom($record['Nom']);
                    $participant->setPrenom($record['Prenom']);
                    $participant->setTelephone($record['Telephone']);
                    $participant->setAdministrateur(false);
                    $participant->setActif(true);

                    if (!$error) {
                        $entityManager->persist($participant);
                        $this->addFlash('success', sprintf("Le participant '%s' a été ajouté avec succès.", $record['Pseudo']));
                    }
                }
            }

            $entityManager->flush();
        }


        return $this->render('pages/admin/creerParticipants.html.twig', [
            'csvForm' => $csvForm->createView()
        ]);
    }

    #[Route('/gerer-les-participants', name: 'admin_gerer_participants')]
    public function gererParticipant(ParticipantRepository $participantRepository): Response
    {
        $participants = $participantRepository->findAll();

        return $this->render('pages/admin/gererParticipants.html.twig', [
            'participants' => $participants,
        ]);
    }

    #[Route('/participants/{id}/supprimer', name: 'admin_supp_participant')]
    public function supprimerParticipant(int $id, ParticipantRepository $participantRepository, EntityManagerInterface $entityManager)
    {
        $participant = $participantRepository->find($id);
        $entityManager->remove($participant);
        $entityManager->flush();

        return $this->redirectToRoute('admin_gerer_participants');
    }

    #[Route('/participants/{id}/desactiver', name: 'admin_desa_participant')]
    public function desactiverParticipant(int $id, ParticipantRepository $participantRepository, EntityManagerInterface $entityManager)
    {
        $participant = $participantRepository->find($id);

        if ($participant->isActif()) {
            $participant->setActif(false);
        } else {
            $participant->setActif(true);
        }
        $entityManager->persist($participant);
        $entityManager->flush();

        return $this->redirectToRoute('admin_gerer_participants');
    }
}
